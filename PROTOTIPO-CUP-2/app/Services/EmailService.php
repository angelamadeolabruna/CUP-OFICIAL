<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public static function enviar(string $to, string $subject, string $html): bool
    {
        $apiKey = env('RESEND_API_KEY');
        if (!$apiKey) {
            Log::warning("RESEND_API_KEY no configurado — correo no enviado a {$to}");
            return false;
        }

        try {
            $response = Http::withToken($apiKey)
                ->post('https://api.resend.com/emails', [
                    'from' => 'onboarding@resend.dev',
                    'to' => [$to],
                    'subject' => $subject,
                    'html' => $html,
                ]);

            if ($response->successful()) {
                Log::info("Correo enviado a {$to} vía Resend");
                return true;
            } else {
                Log::error("Resend error a {$to}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error enviar correo a {$to}: " . $e->getMessage());
            return false;
        }
    }

    public static function enviarCredenciales(Usuario $usuario, string $passwordPlano): bool
    {
        $usuario->load('rol');
        $html = view('emails.bienvenida-usuario', [
            'usuario' => $usuario,
            'passwordPlano' => $passwordPlano,
        ])->render();

        return self::enviar(
            $usuario->email,
            'Bienvenido al Sistema CUP FICCT — Credenciales de acceso',
            $html
        );
    }
}
