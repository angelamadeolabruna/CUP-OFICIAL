<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estadísticas por Materia</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1f2937; padding: 20px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #64748b; font-size: 12px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { background: #f8fafc; text-align: left; padding: 6px 8px; border-bottom: 2px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        td:first-child { text-align: left; font-weight: 700; }
        .footer { margin-top: 16px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>Estadísticas por Materia</h1>
    <p class="subtitle">Gestión: {{ $gestionActiva?->nombre_gestion ?? '—' }} — Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Materia</th>
                <th>Total</th>
                <th>Con Notas</th>
                <th>Prom. General</th>
                <th>Aprobados</th>
                <th>Reprobados</th>
                <th>% Aprobación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($estadisticas as $e)
                <tr>
                    <td>{{ $e['materia']->nombre_materia }}</td>
                    <td>{{ $e['total_postulantes'] }}</td>
                    <td>{{ $e['con_notas'] }}</td>
                    <td>{{ $e['promedio_general'] ?? '—' }}</td>
                    <td style="color:#059669;">{{ $e['aprobados'] }}</td>
                    <td style="color:#dc2626;">{{ $e['reprobados'] }}</td>
                    <td>{{ $e['porcentaje_aprobacion'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Sistema CUP FICCT - UAGRM</div>
</body>
</html>
