<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulación Confirmada - CUP FICCT</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #059669; padding: 30px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; letter-spacing: 1px; }
        .header p { color: #a7f3d0; margin: 6px 0 0; font-size: 13px; }
        .body { padding: 35px 40px; }
        .body p { color: #444; font-size: 15px; line-height: 1.6; }
        .info-box { background: #f0fdf4; border-left: 4px solid #059669; border-radius: 4px; padding: 20px 25px; margin: 25px 0; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 7px 0; font-size: 14px; color: #333; }
        .info-box td:first-child { font-weight: bold; width: 160px; color: #065f46; }
        .btn-container { text-align: center; margin: 30px 0; }
        .btn { background: #059669; color: #ffffff !important; text-decoration: none; padding: 13px 35px; border-radius: 5px; font-size: 15px; display: inline-block; }
        .footer { background: #f0f0f0; padding: 18px 40px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>✅ Postulación Confirmada</h1>
        <p>Sistema CUP FICCT — UAGRM</p>
    </div>
    <div class="body">
        <p>Hola <strong>{{ $usuario->nombre_usuario }}</strong>,</p>
        <p>Tu postulación al <strong>CUP FICCT</strong> ha sido <strong>confirmada exitosamente</strong>. A partir de ahora tenés acceso como <strong>Postulante Oficial</strong> con nuevos privilegios en el sistema.</p>

        <div class="info-box">
            <table>
                <tr><td>Estado:</td><td>Postulante Oficial ✅</td></tr>
                <tr><td>Correo:</td><td>{{ $usuario->email }}</td></tr>
                <tr><td>Rol:</td><td>postulante_oficial</td></tr>
            </table>
        </div>

        <p>Ahora podés:</p>
        <ul style="color:#444;font-size:14px;line-height:1.8;">
            <li>📖 Consultar tus notas y asistencias</li>
            <li>🎯 Ver tus resultados finales</li>
            <li>📋 Acceder a toda tu información de postulación</li>
        </ul>

        <div class="btn-container">
            <a href="{{ url('/login') }}" class="btn">Ingresar al sistema</a>
        </div>

        <p style="margin-top: 25px;">¡Mucho éxito en tu proceso de admisión!</p>
        <p>Saludos,<br><strong>Equipo CUP FICCT</strong></p>
    </div>
    <div class="footer">
        Este correo fue generado automáticamente. Por favor no respondas a este mensaje.
    </div>
</div>
</body>
</html>
