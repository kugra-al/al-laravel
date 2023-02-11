<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Exception;
use Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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

            if($searchUser){

                Auth::login($searchUser);

                return redirect('/home');

            }else{
                $status = "Created new user account";
                // Check for existing user and convert to gituser
                $gitUser = User::where('email',$user->email)->first();
                if ($gitUser) {
                    $gituser->auth_type = 'github';
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
                        'password' => \Hash::make(\Str::random(12));
                    ]);
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
