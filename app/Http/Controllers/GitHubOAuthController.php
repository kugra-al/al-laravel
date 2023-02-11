<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Exception;
use Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\GithubAL;

class GitHubOAuthController extends Controller
{
    public function gitRedirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function gitCallback()
    {
        try {

            $user = Socialite::driver('github')->user();

            $searchUser = User::where('github_id', $user->id)->first();

            if ($user && !GithubAL::checkMemberIDInTeam('Accursed-Lands-MUD','Coders',$user->id)) {
                return back()->with('error',"You need to be a member of the Accursed-Lands-MUD Coders team to login");
            }
            if($searchUser){

                Auth::login($searchUser);

                return redirect('/home');

            }else{
                $status = "Created new user account";
                // Check for existing user and convert to gituser
                $gitUser = User::where('email',$user->email)->first();
                if ($gitUser) {
                    $gitUser->auth_type = 'github';
                    $gitUser->github_id = $user->id;
                    $gitUser->save();
                    $status = "Converted user login to github login";
                } else {
                    if (!$user->name)
                        $user->name = explode("@",$user->email)[0];
                    $gitUser = User::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'github_id'=> $user->id,
                        'auth_type'=> 'github',
                        'password' => \Hash::make(\Str::random(12))
                    ]);
                }
                if (!$gitUser->hasRole('creators')) {
                    $gitUser->assignRole('creators');
                    $status .= ".  Assigned role `creators`";
                }
                if (!$gitUser->hasRole('admin') && GithubAL::checkMemberIDInTeam('Accursed-Lands-MUD','webmasters',$gitUser->github_id)) {
                    $gitUser->assignRole('admin');
                    $status .= ".  Assigned role `admin`";
                }
                Auth::login($gitUser);

                return redirect('/home')->with('status',$status);
            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return \Redirect('/login')->with('error',"Something went wrong. Error logged");
        }
    }
}
