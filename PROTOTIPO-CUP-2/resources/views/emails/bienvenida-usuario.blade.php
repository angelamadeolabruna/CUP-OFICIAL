<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido al Sistema CUP FICCT</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #1a3a5c; padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; letter-spacing: 1px; }
        .header p { color: #a8c4e0; margin: 6px 0 0; font-size: 13px; }
        .body { padding: 35px 40px; }
        .body p { color: #444; font-size: 15px; line-height: 1.6; }
        .credentials { background: #f0f5fb; border-left: 4px solid #1a3a5c; border-radius: 4px; padding: 20px 25px; margin: 25px 0; }
        .credentials table { width: 100%; border-collapse: collapse; }
        .credentials td { padding: 7px 0; font-size: 15px; color: #333; }
        .credentials td:first-child { font-weight: bold; width: 140px; color: #1a3a5c; }
        .credentials td:last-child { font-family: monospace; font-size: 15px; }
        .btn-container { text-align: center; margin: 30px 0; }
        .btn { background: #1a3a5c; color: #ffffff !important; text-decoration: none; padding: 13px 35px; border-radius: 5px; font-size: 15px; display: inline-block; }
        .warning { background: #fff8e1; border: 1px solid #ffe082; border-radius: 4px; padding: 12px 18px; font-size: 13px; color: #795548; margin-top: 20px; }
        .footer { background: #f0f0f0; padding: 18px 40px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Sistema CUP FICCT</h1>
        <p>Centro Universitario de Postulaciones — UAGRM</p>
    </div>
    <div class="body">
        <p>Hola <strong>{{ $usuario->nombre_usuario }}</strong>,</p>
        <p>Tu cuenta en el <strong>Sistema CUP FICCT</strong> ha sido creada exitosamente. A continuación encontrás tus credenciales de acceso:</p>

        <div class="credentials">
            <table>
                <tr>
                    <td>Correo:</td>
                    <td>{{ $usuario->email }}</td>
                </tr>
                <tr>
                    <td>Contraseña:</td>
                    <td>{{ $passwordPlano }}</td>
                </tr>
                <tr>
                    <td>Rol asignado:</td>
                    <td>{{ $usuario->rol->nombre_rol ?? 'Sin rol' }}</td>
                </tr>
            </table>
        </div>

        <div class="btn-container">
            <a href="{{ url('/login') }}" class="btn">Iniciar sesión</a>
        </div>

        <div class="warning">
            ⚠️ Por seguridad, te recomendamos cambiar tu contraseña después de iniciar sesión por primera vez. Ingresá a <strong>Mi perfil → Cambiar contraseña</strong>.
        </div>

        <p style="margin-top: 25px;">Si tenés algún problema para acceder, comunicate con el administrador del sistema.</p>
        <p>Saludos,<br><strong>Equipo CUP FICCT</strong></p>
    </div>
    <div class="footer">
        Este correo fue generado automáticamente. Por favor no respondas a este mensaje.
    </div>
</div>
</body>
</html>
