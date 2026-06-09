<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grupos con Más Aprobados</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1f2937; padding: 20px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #64748b; font-size: 12px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { background: #f8fafc; text-align: left; padding: 6px 8px; border-bottom: 2px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        td:nth-child(2) { text-align: left; font-weight: 700; }
        .footer { margin-top: 16px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>Grupos con Más Aprobados</h1>
    <p class="subtitle">Gestión: {{ $gestionActiva?->nombre_gestion ?? '—' }} — Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Grupo</th>
                <th>Materia</th>
                <th>Aula</th>
                <th>Total</th>
                <th>Aprobados</th>
                <th>% Aprobación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ranking as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r['grupo']->nombre_grupo }}</td>
                    <td>{{ $r['grupo']->materia?->nombre_materia ?? '—' }}</td>
                    <td>{{ $r['grupo']->aula?->codigo_aula ?? $r['grupo']->aula?->nombre ?? '—' }}</td>
                    <td>{{ $r['total'] }}</td>
                    <td>{{ $r['aprobados'] }}</td>
                    <td>{{ $r['porcentaje'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Sistema CUP FICCT - UAGRM</div>
</body>
</html>
