<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'github', 'linkedin-openid'];

    /**
     * Redirect the user to the OAuth provider's authentication page.
     */
    public function redirect(string $provider)
    {
        if (! in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the OAuth provider.
     */
    public function callback(Request $request, string $provider)
    {
        if (! in_array($provider, self::ALLOWED_PROVIDERS, true)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            Log::error("Social login [{$provider}] failed: ".$e->getMessage());

            return redirect('/login1.html?social_error=auth_failed');
        }

        $email = $socialUser->getEmail();
        $socialId = (string) $socialUser->getId();
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? '';

        // GitHub may not expose a public email — require it
        if (empty($email)) {
            return redirect('/login1.html?social_error=no_email');
        }

        // 1. Find by provider + provider_id (returning user, same social account)
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialId)
            ->first();

        // 2. Find by email (existing password account — link it)
        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Link this social provider to the existing local account
                $user->update(['provider' => $provider, 'provider_id' => $socialId]);
            }
        }

        // 3. Register a brand-new user
        if (! $user) {
            [$firstName, $lastName] = $this->parseName($name, $email);
            $username = $this->generateUsername($firstName, $lastName);

            $user = DB::transaction(function () use ($firstName, $lastName, $email, $username, $provider, $socialId) {
                $newUser = User::create([
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'email' => $email,
                    'username' => $username,
                    'password' => Hash::make(Str::random(40)),
                    'provider' => $provider,
                    'provider_id' => $socialId,
                    'joinDate' => now(),
                ]);

                // Assign default student role (role_id = 1)
                $newUser->roles()->attach(1);

                return $newUser;
            });
        }

        // Block suspended accounts
        if ($user->is_suspended) {
            return redirect('/login1.html?social_error=suspended');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect('/profile.html');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Split a full name string into firstName / lastName.
     * Falls back to the local part of the email if name is empty.
     */
    private function parseName(string $fullName, string $email): array
    {
        $fullName = trim($fullName);

        if ($fullName === '') {
            // Derive from email local-part: "john.doe@example.com" → firstName=John, lastName=Doe
            $local = explode('@', $email)[0];
            $parts = preg_split('/[\s._-]+/', $local);
            $firstName = Str::title($parts[0] ?? 'User');
            $lastName = Str::title($parts[1] ?? '');

            return [$firstName, $lastName];
        }

        $parts = explode(' ', $fullName, 2);
        $firstName = Str::title($parts[0]);
        $lastName = isset($parts[1]) ? Str::title($parts[1]) : '';

        return [$firstName, $lastName];
    }

    /**
     * Generate a unique username from first + last name.
     * Attempts: john_doe → john_doe2 → john_doe3 … → random suffix
     */
    private function generateUsername(string $firstName, string $lastName): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]/i', '', $firstName));
        if ($lastName !== '') {
            $base .= '_'.strtolower(preg_replace('/[^a-z0-9]/i', '', $lastName));
        }
        $base = $base ?: 'user';

        $candidate = $base;
        $counter = 2;

        while (User::where('username', $candidate)->exists()) {
            if ($counter > 99) {
                // Fallback to a random suffix to guarantee uniqueness
                $candidate = $base.'_'.Str::random(5);
                break;
            }
            $candidate = $base.$counter;
            $counter++;
        }

        return $candidate;
    }
}
