<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    
    /**
     * Überschreibt die Standard-Login-Validierung mit eigenen Fehlertexten.
     */
    protected function validateResetPassword(Request $request)
    {
        dd('validateLogin wure aufgerufen');
        $messages = [
            'email.required' => 'Bitte gib deine E-Mail-Adresse ein.',
            'email.email' => 'Das ist keine gültige E-Mail-Adresse.',
            'password.required' => 'Bitte gib dein Passwort ein.',
        ];
        
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], $messages);
    }
}
