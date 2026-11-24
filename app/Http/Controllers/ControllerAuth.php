<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use GuzzleHttp\Exception\GuzzleException;

class ControllerAuth extends Controller
{
    public function redirectGoogle()
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->withErrors([
                'google' => 'Falta configurar las credenciales de Google en el servidor.',
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('login')->withErrors([
                'google' => 'No se autorizó el acceso con Google. Puedes intentarlo nuevamente.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException | GuzzleException $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'google' => 'No se pudo validar el acceso con Google. Inténtalo nuevamente.',
            ]);
        }

        if (! $googleUser->getId() || ! $googleUser->getEmail() || ! filter_var($googleUser->user['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('login')->withErrors([
                'google' => 'Se necesita una cuenta de Google con correo verificado.',
            ]);
        }

        $usuario = Usuario::where('google_id', $googleUser->getId())->first();

        if (! $usuario) {
            $usuario = Usuario::where('email', $googleUser->getEmail())->first();

            if ($usuario && $usuario->google_id) {
                return redirect()->route('login')->withErrors([
                    'google' => 'Este correo ya está asociado a otra cuenta de Google.',
                ]);
            }

            // Vincula también los usuarios de prueba que aún no tienen google_id.
            if (! $usuario) {
                $usuario = new Usuario();
            }

            $usuario->nombre = $googleUser->getName() ?: $googleUser->getEmail();
            $usuario->email = $googleUser->getEmail();
            $usuario->google_id = $googleUser->getId();
            $usuario->save();
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('listas.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
