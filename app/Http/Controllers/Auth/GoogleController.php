<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $email = $googleUser->getEmail();

            if (!$email || !$googleUser->getId()) {
                return redirect()->route('login')
                    ->withErrors([
                        'email' => 'Akun Google tidak memberikan informasi yang diperlukan.',
                    ]);
            }

            // Cari akun berdasarkan email.
            $user = User::where('email', $email)->first();

            if ($user) {
                // Jangan otomatis menghubungkan akun lama yang
                // emailnya belum terverifikasi di database.
                if (!$user->email_verified_at) {
                    return redirect()->route('login')
                        ->withErrors([
                            'email' => 'Akun belum terverifikasi. Silakan hubungi admin.',
                        ]);
                }
            } else {
                // Akun Google baru selalu dibuat sebagai user biasa.
                $user = new User([
                    'name' => $googleUser->getName()
                        ?: 'Pengguna Greenova',
                    'email' => $email,
                    'password' => Str::random(40),
                ]);
                $user->email_verified_at = now();
                $user->role = 'user';
                $user->save();
            }

            Auth::login($user);
            request()->session()->regenerate();

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('home');

        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Login Google gagal. Silakan coba lagi.',
                ]);
        }
    }
}
