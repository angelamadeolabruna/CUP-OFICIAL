<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Postulantes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1f2937; padding: 20px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #64748b; font-size: 12px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { background: #f8fafc; text-align: left; padding: 6px 8px; border-bottom: 2px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 9px; text-transform: uppercase; letter-spacing: .4px; }
        td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
        .aprobado { color: #059669; font-weight: 700; }
        .reprobado { color: #dc2626; font-weight: 700; }
        .footer { margin-top: 16px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>Reporte de Postulantes</h1>
    <p class="subtitle">Gestión: {{ $gestionActiva?->nombre_gestion ?? '—' }} — Generado: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>CI</th>
                <th>Apellidos y Nombres</th>
                <th>1ra Opción</th>
                <th>2da Opción</th>
                <th>Promedio</th>
                <th>Estado</th>
                <th>Admisión</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($postulantes as $p)
                <tr>
                    <td>{{ $p->id_postulante }}</td>
                    <td>{{ $p->prepostulante?->ci ?? '—' }}</td>
                    <td>{{ $p->prepostulante?->apellidos ?? '' }} {{ $p->prepostulante?->nombres ?? '' }}</td>
                    <td>{{ $p->primeraOpcion?->codigo_carrera ?? '—' }}</td>
                    <td>{{ $p->segundaOpcion?->codigo_carrera ?? '—' }}</td>
                    <td>{{ $p->resultado?->promedio_final ?? '—' }}</td>
                    <td class="{{ $p->resultado?->estado_academico }}">{{ $p->resultado?->estado_academico ?? '—' }}</td>
                    <td>{{ $p->admision?->estado_admision ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Total: {{ $postulantes->count() }} postulantes — Sistema CUP FICCT - UAGRM</div>
</body>
</html>
