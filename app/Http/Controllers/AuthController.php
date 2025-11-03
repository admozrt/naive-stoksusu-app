<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();
        if (!Auth::attempt($request->only($request, 'password')))
        {
            Auth::login($user);
        return redirect()->route('dashboard');
        }
        // if ($user && $request->password === $user->password) {
        //     Auth::login($user);
        //     return redirect()->route('dashboard');
        // }

        return back()->with('error', 'Username atau password salah!');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'Berhasil logout!');
    }
}