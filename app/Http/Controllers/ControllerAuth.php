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
    public function redirectGoogle(Request $request)
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret') || ! filter_var(config('services.google.redirect'), FILTER_VALIDATE_URL)) {
            return redirect()->route('login')->withErrors([
                'google' => 'Falta configurar el cliente de Google y su dirección de retorno en el servidor.',
            ]);
        }

        // Google debe regresar al mismo host en el que se creó la sesión OAuth.
        $callback = parse_url(config('services.google.redirect'));
        $origen = $callback['scheme'].'://'.$callback['host'];
        if (isset($callback['port'])) {
            $origen .= ':'.$callback['port'];
        }

        if ($request->getSchemeAndHttpHost() !== $origen) {
            return redirect()->away($origen.$request->getRequestUri());
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
                'google' => 'No se pudo validar la sesión con Google. Vuelve a iniciar el acceso desde esta página.',
            ]);
        }

        $correoVerificado = $googleUser->user['email_verified'] ?? $googleUser->user['verified_email'] ?? false;

        if (! $googleUser->getId() || ! $googleUser->getEmail() || ! filter_var($correoVerificado, FILTER_VALIDATE_BOOLEAN)) {
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
