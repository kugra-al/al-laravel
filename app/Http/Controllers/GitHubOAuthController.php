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
                $gitUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'github_id'=> $user->id,
                    'auth_type'=> 'github',
                    'password' => encrypt(env('GITHUB_OAUTH_DEFAULT_PASS'))
                ]);

                Auth::login($gitUser);

                return redirect('/home');
            }

        } catch (Exception $e) {
            Log::error($e->getMessage());
            dd("Something went wrong. Error logged");
//            dd($e->getMessage());
        }
    }
}
