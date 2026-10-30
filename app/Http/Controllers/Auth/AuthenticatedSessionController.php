<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt([...$request->validated(), 'is_active' => true])) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah, atau akun Anda nonaktif.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return to_route($request->user()->dashboardRoute());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('success', 'Anda berhasil keluar.');
    }
}
