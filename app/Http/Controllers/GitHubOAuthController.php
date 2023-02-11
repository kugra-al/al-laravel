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
                if (!$user->name)
                    $user->name = explode("@",$user->email)[0];
                $gitUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'github_id'=> $user->id,
                    'auth_type'=> 'github',
                    'password' => \Hash::make(\Str::random(12));
                ]);

                Auth::login($gitUser);

                return redirect('/home');
            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return \Redirect('/login')->with('status',"Something went wrong. Error logged");
//            dd("Something went wrong. Error logged");
//            dd($e->getMessage());
        }
    }
}
