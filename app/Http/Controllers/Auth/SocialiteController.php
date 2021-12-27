<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SocialAccount;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    //
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProvideCallback($provider)
    {
        try {
            # code...
            $user = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            # code...
            return redirect()->back();
        }
        //cek apakah user sudah ada/engga
        $authUser = $this->findOrCreateUser($user, $provider);

        auth()->login($authUser, true);
        // dd("aaaaa");

        return redirect()->route('home');
    }

    public function findOrCreateUser($socialUser, $provider)
    {
        $socialAccount = SocialAccount::where('provider_id', $socialUser->getId())
        ->where('provider_name', $provider)
        ->first();

        if($socialAccount)
        {
            return $socialAccount->user;
        }
        else{
            $user = User::where('email', $socialUser->getEmail())->first();

            if(!$user)
            {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail()
                ]);
            }

            $user->social_accounts()->create([
                'provider_id' => $socialUser->getId(),
                'provider_name' => $provider
            ]);

            return $user;
        }
    }
}

// FACEBOOK_CLIENT_ID="1079450042819580"
// FACEBOOK_CLIENT_SECRET="c93d97164900850dec8f1703e01b736d"
// FACEBOOK_CLIENT_REDIRECT=http://localhost:8000/auth/facebook/callback

// GITHUB_CLIENT_ID="f54079ef7d493d38a256"
// GITHUB_CLIENT_SECRET="e829beaa3ff9ba2b703e9a0fa3737992dd750226"
// GITHUB_CLIENT_REDIRECT=http://localhost:8000/auth/github/callback
