<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->isMasyarakat()) {
                return redirect()->intended('/masyarakat/dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'no_hp' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'password' => Hash::make($request->password),
            'role' => 'masyarakat',
        ]);

        Auth::login($user);

        return redirect('/masyarakat/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'identifier.required' => 'Email atau Nomor HP/WhatsApp wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $identifier = trim($request->identifier);

        // Siapkan variasi nomor HP jika input berupa angka
        $cleanNumber = preg_replace('/[^0-9]/', '', $identifier);
        $variants = [$identifier];

        if (!empty($cleanNumber)) {
            $variants[] = $cleanNumber;
            if (str_starts_with($cleanNumber, '62')) {
                $variants[] = '0' . substr($cleanNumber, 2);
            } elseif (str_starts_with($cleanNumber, '0')) {
                $variants[] = '62' . substr($cleanNumber, 1);
            }
        }

        $variants = array_unique($variants);

        // Cari akun masyarakat berdasarkan Email ATAU Nomor HP
        $user = User::where(function ($query) use ($identifier, $variants) {
            $query->where('email', $identifier)
                  ->orWhereIn('no_hp', $variants);
        })->first();

        if (!$user) {
            return back()->withErrors([
                'identifier' => 'Akun dengan email atau nomor HP tersebut tidak ditemukan dalam sistem. Pastikan data sudah benar.',
            ])->withInput($request->only('identifier'));
        }

        // Keamanan: akun admin tidak boleh direset lewat form publik ini
        if ($user->isAdmin()) {
            return back()->withErrors([
                'identifier' => 'Akun Administrator tidak dapat direset melalui form publik ini. Silakan hubungi pengelola sistem.',
            ])->withInput($request->only('identifier'));
        }

        // Update kata sandi baru
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('success', 'Kata sandi akun atas nama ' . $user->name . ' berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
    }
}
