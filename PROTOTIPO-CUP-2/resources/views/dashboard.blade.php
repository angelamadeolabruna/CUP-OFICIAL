<x-layouts.app title="Dashboard">
    <div class="flex-between" style="margin-bottom:12px;">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-desc" style="margin-bottom:0;">Bienvenido, <strong>{{ auth()->user()->nombre_usuario }}</strong> — {{ auth()->user()->rol?->nombre_rol }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card" style="margin-bottom:20px;padding:14px 20px;">
        <form method="GET" class="flex" style="flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div class="filter-field">
                <label style="margin-bottom:3px;">Gestión Académica</label>
                <select name="id_gestion" style="margin-bottom:0;" onchange="this.form.submit()">
                    @foreach ($gestiones as $g)
                        <option value="{{ $g->id_gestion }}" {{ $gestionId == $g->id_gestion ? 'selected' : '' }}>
                            {{ $g->nombre_gestion }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label style="margin-bottom:3px;">Carrera</label>
                <select name="id_carrera" style="margin-bottom:0;" onchange="this.form.submit()">
                    <option value="">Todas las carreras</option>
                    @foreach ($carreras as $c)
                        <option value="{{ $c->id_carrera }}" {{ $carreraId == $c->id_carrera ? 'selected' : '' }}>
                            {{ $c->codigo_carrera }} — {{ $c->nombre_carrera }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if ($carreraId)
                <a href="{{ route('dashboard') }}?id_gestion={{ $gestionId }}" class="button button-secondary button-sm" style="align-self:flex-end;">Limpiar carrera</a>
            @endif
        </form>
    </div>

    {{-- Gestión activa label --}}
    <div style="margin-bottom:16px;font-size:13px;color:#475569;">
        Mostrando datos de: <strong>{{ $gestionLabel }}</strong>
        @if ($carreraId)
            — Carrera: <strong>{{ $carreras->firstWhere('id_carrera', $carreraId)?->nombre_carrera ?? '—' }}</strong>
        @endif
    </div>

    {{-- Tarjetas de métricas --}}
    <div class="grid">
        <div class="metric">
            <div class="label">Total Inscritos</div>
            <strong>{{ $totalPostulantes }}</strong>
        </div>
        <div class="metric">
            <div class="label">Aprobados</div>
            <strong>{{ $totalAprobados }}</strong>
            <div style="font-size:11px;color:#64748b;">{{ $porcentajeAprobados }}% del total</div>
        </div>
        <div class="metric">
            <div class="label">Reprobados</div>
            <strong>{{ $totalReprobados }}</strong>
            <div style="font-size:11px;color:#64748b;">{{ $porcentajeReprobados }}% del total</div>
        </div>
        <div class="metric">
            <div class="label">Sin Resultado</div>
            <strong>{{ $sinResultado }}</strong>
        </div>
        <div class="metric">
            <div class="label">Admitidos</div>
            <strong>{{ $totalAdmitidos }}</strong>
        </div>
        <div class="metric">
            <div class="label">Grupos Habilitados</div>
            <strong>{{ $totalGrupos }}</strong>
        </div>
    </div>

    {{-- Gráfico de torta (CSS) y detalle --}}
    <div class="grid-2" style="margin-bottom:24px;">
        {{-- Dona --}}
        <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;align-self:flex-start;">Distribución de Resultados</h3>
            @if ($totalPostulantes > 0)
                @php
                    $a = $porcentajeAprobados;
                    $r = $porcentajeReprobados;
                    $s = 100 - $a - $r;
                    $conicFrom = $s > 0 ? ($s / 100) * 360 : 0;
                    $conicAprobados = ($a / 100) * 360;
                    $conicReprobados = ($r / 100) * 360;
                    $startA = $s > 0 ? ($s / 100) * 360 : 0;
                    $endA = $startA + $conicAprobados;
                    $startR = $endA;
                    $endR = $startR + $conicReprobados;
                    $gradient = '';
                    if ($s > 0) $gradient .= "#f59e0b 0% {$s}%,";
                    if ($a > 0) $gradient .= "#059669 {$s}% " . ($s + $a) . "%,";
                    if ($r > 0) $gradient .= "#dc2626 " . ($s + $a) . "% 100%,";
                    $gradient = rtrim($gradient, ',');
                @endphp
                <div style="position:relative;width:180px;height:180px;">
                    <div style="width:180px;height:180px;border-radius:50%;background:conic-gradient({{ $gradient }});"></div>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:90px;height:90px;border-radius:50%;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <span style="font-size:22px;font-weight:800;">{{ $totalPostulantes }}</span>
                        <span style="font-size:10px;color:#64748b;">Total</span>
                    </div>
                </div>
                <div style="display:flex;gap:16px;margin-top:16px;font-size:12px;">
                    @if ($a > 0)
                        <div><span style="display:inline-block;width:10px;height:10px;background:#059669;border-radius:2px;"></span> Aprobados {{ $a }}%</div>
                    @endif
                    @if ($r > 0)
                        <div><span style="display:inline-block;width:10px;height:10px;background:#dc2626;border-radius:2px;"></span> Reprobados {{ $r }}%</div>
                    @endif
                    @if ($s > 0)
                        <div><span style="display:inline-block;width:10px;height:10px;background:#f59e0b;border-radius:2px;"></span> Sin resultado {{ $s }}%</div>
                    @endif
                </div>
            @else
                <p style="color:#94a3b8;">Sin datos para mostrar.</p>
            @endif
        </div>

        {{-- Resumen rápido --}}
        <div class="card">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:12px;">Resumen del Proceso</h3>
            <div class="grid-2-4" style="font-size:13px;">
                <div style="padding:12px;background:#f8fafc;border-radius:6px;">
                    <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Inscritos</div>
                    <div style="font-size:24px;font-weight:700;color:#2563eb;">{{ $totalPostulantes }}</div>
                </div>
                <div style="padding:12px;background:#f8fafc;border-radius:6px;">
                    <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">No evaluados</div>
                    <div style="font-size:24px;font-weight:700;color:#f59e0b;">{{ $sinResultado }}</div>
                </div>
                <div style="padding:12px;background:#f0fdf4;border-radius:6px;">
                    <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Aprobados</div>
                    <div style="font-size:24px;font-weight:700;color:#059669;">{{ $totalAprobados }}</div>
                    <div style="font-size:11px;color:#059669;">{{ $porcentajeAprobados }}% del total</div>
                </div>
                <div style="padding:12px;background:#fef2f2;border-radius:6px;">
                    <div style="color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;">Reprobados</div>
                    <div style="font-size:24px;font-weight:700;color:#dc2626;">{{ $totalReprobados }}</div>
                    <div style="font-size:11px;color:#dc2626;">{{ $porcentajeReprobados }}% del total</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla por carrera --}}
    @if ($statsPorCarrera->isNotEmpty())
        <div class="card table-container">
            <h3 style="font-size:15px;font-weight:700;padding:16px 20px 8px;">Postulantes por Carrera</h3>
            <table class="table" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>Carrera</th>
                            <th style="text-align:center;">Total</th>
                            <th style="text-align:center;">Aprobados</th>
                            <th style="text-align:center;">% Aprobación</th>
                            <th style="min-width:120px;">Barra</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($statsPorCarrera as $s)
                            <tr>
                                <td><strong>{{ $s['carrera']->codigo_carrera }}</strong> — {{ $s['carrera']->nombre_carrera }}</td>
                                <td style="text-align:center;">{{ $s['total'] }}</td>
                                <td style="text-align:center;font-weight:600;color:#059669;">{{ $s['aprobados'] }}</td>
                                <td style="text-align:center;font-weight:600;">{{ $s['porcentaje'] }}%</td>
                                <td>
                                    <div style="height:8px;background:#fee2e2;border-radius:4px;overflow:hidden;">
                                        <div style="height:100%;width:{{ $s['porcentaje'] }}%;background:#059669;border-radius:4px;"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
    @endif

    @if ($totalPostulantes === 0)
        <div class="card" style="text-align:center;padding:32px;color:#64748b;">
            No hay datos registrados para los filtros seleccionados.
        </div>
    @endif
</x-layouts.app>
