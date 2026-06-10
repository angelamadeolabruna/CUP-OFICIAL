<x-layouts.app title="Dashboard">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h1 class="text-[22px] font-extrabold text-blue-institucional tracking-tight">Dashboard</h1>
            <p class="text-sm text-slate-500 mb-0">Bienvenido, <strong class="text-slate-800">{{ auth()->user()->nombre_usuario }}</strong> — {{ auth()->user()->rol?->nombre_rol }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white border border-slate-200 rounded-xl mb-5 px-5 py-3.5">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="filter-field">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-[0.6px] mb-1">Gestión Académica</label>
                <select name="id_gestion" onchange="this.form.submit()"
                        class="mb-0 w-full px-3.5 py-2 text-sm border border-slate-300 rounded-lg text-slate-900 bg-white outline-none transition-[border-color,shadow] duration-[0.18s] focus:border-blue-institucional focus:ring-3 focus:ring-blue-institucional/10">
                    @foreach ($gestiones as $g)
                        <option value="{{ $g->id_gestion }}" {{ $gestionId == $g->id_gestion ? 'selected' : '' }}>
                            {{ $g->nombre_gestion }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-[0.6px] mb-1">Carrera</label>
                <select name="id_carrera" onchange="this.form.submit()"
                        class="mb-0 w-full px-3.5 py-2 text-sm border border-slate-300 rounded-lg text-slate-900 bg-white outline-none transition-[border-color,shadow] duration-[0.18s] focus:border-blue-institucional focus:ring-3 focus:ring-blue-institucional/10">
                    <option value="">Todas las carreras</option>
                    @foreach ($carreras as $c)
                        <option value="{{ $c->id_carrera }}" {{ $carreraId == $c->id_carrera ? 'selected' : '' }}>
                            {{ $c->codigo_carrera }} — {{ $c->nombre_carrera }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if ($carreraId)
                <a href="{{ route('dashboard') }}?id_gestion={{ $gestionId }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded-lg no-underline transition-all duration-[0.18s] hover:bg-slate-200 hover:text-slate-800 self-end">✕ Limpiar carrera</a>
            @endif
        </form>
    </div>

    {{-- Gestión activa label --}}
    <div class="text-xs text-slate-500 mb-4">
        Mostrando datos de: <strong class="text-slate-800">{{ $gestionLabel }}</strong>
        @if ($carreraId)
            — Carrera: <strong class="text-slate-800">{{ $carreras->firstWhere('id_carrera', $carreraId)?->nombre_carrera ?? '—' }}</strong>
        @endif
    </div>

    {{-- Tarjetas de métricas --}}
    <div class="grid grid-cols-3 gap-4 mb-6 max-lg:grid-cols-2 max-sm:grid-cols-1">
        <div class="metric">
            <div class="label">Total Inscritos</div>
            <strong>{{ $totalPostulantes }}</strong>
        </div>
        <div class="metric">
            <div class="label">Aprobados</div>
            <strong>{{ $totalAprobados }}</strong>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $porcentajeAprobados }}% del total</div>
        </div>
        <div class="metric">
            <div class="label">Reprobados</div>
            <strong>{{ $totalReprobados }}</strong>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $porcentajeReprobados }}% del total</div>
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
    <div class="grid grid-cols-2 gap-5 mb-6 max-md:grid-cols-1">
        {{-- Dona --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6 flex flex-col items-center justify-center">
            <h3 class="text-sm font-bold text-slate-900 mb-4 self-start">Distribución de Resultados</h3>
            @if ($totalPostulantes > 0)
                @php
                    $a = $porcentajeAprobados;
                    $r = $porcentajeReprobados;
                    $s = 100 - $a - $r;
                    $gradient = '';
                    if ($s > 0) $gradient .= "#f59e0b 0% {$s}%,";
                    if ($a > 0) $gradient .= "#059669 {$s}% " . ($s + $a) . "%,";
                    if ($r > 0) $gradient .= "#dc2626 " . ($s + $a) . "% 100%,";
                    $gradient = rtrim($gradient, ',');
                @endphp
                <div class="relative w-[180px] h-[180px]">
                    <div class="w-[180px] h-[180px] rounded-full" style="background:conic-gradient({{ $gradient }});"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[90px] h-[90px] rounded-full bg-white flex flex-col items-center justify-center">
                        <span class="text-[22px] font-extrabold text-slate-900">{{ $totalPostulantes }}</span>
                        <span class="text-[10px] text-slate-400">Total</span>
                    </div>
                </div>
                <div class="flex gap-4 mt-4 text-xs">
                    @if ($a > 0)
                        <div><span class="inline-block w-[10px] h-[10px] rounded-sm bg-emerald-600"></span> Aprobados {{ $a }}%</div>
                    @endif
                    @if ($r > 0)
                        <div><span class="inline-block w-[10px] h-[10px] rounded-sm bg-red-600"></span> Reprobados {{ $r }}%</div>
                    @endif
                    @if ($s > 0)
                        <div><span class="inline-block w-[10px] h-[10px] rounded-sm bg-amber-500"></span> Sin resultado {{ $s }}%</div>
                    @endif
                </div>
            @else
                <p class="text-slate-400">Sin datos para mostrar.</p>
            @endif
        </div>

        {{-- Resumen rápido --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6">
            <h3 class="text-sm font-bold text-slate-900 mb-4">Resumen del Proceso</h3>
            <div class="grid grid-cols-2 gap-3 text-xs max-sm:grid-cols-1">
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.4px]">Inscritos</div>
                    <div class="text-[24px] font-extrabold text-blue-600">{{ $totalPostulantes }}</div>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.4px]">No evaluados</div>
                    <div class="text-[24px] font-extrabold text-amber-500">{{ $sinResultado }}</div>
                </div>
                <div class="p-3 bg-emerald-50 rounded-lg">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.4px]">Aprobados</div>
                    <div class="text-[24px] font-extrabold text-emerald-600">{{ $totalAprobados }}</div>
                    <div class="text-[11px] text-emerald-600 mt-0.5">{{ $porcentajeAprobados }}% del total</div>
                </div>
                <div class="p-3 bg-red-50 rounded-lg">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.4px]">Reprobados</div>
                    <div class="text-[24px] font-extrabold text-red-600">{{ $totalReprobados }}</div>
                    <div class="text-[11px] text-red-600 mt-0.5">{{ $porcentajeReprobados }}% del total</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla por carrera --}}
    @if ($statsPorCarrera->isNotEmpty())
        <div class="bg-white border border-slate-200 table-container">
            <h3 class="text-sm font-bold text-slate-900 px-5 pt-4 pb-2">Postulantes por Carrera</h3>
            <table class="table min-w-full text-xs">
                <thead>
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-slate-500 uppercase tracking-[0.4px] bg-slate-50 border-b border-slate-200">Carrera</th>
                        <th class="text-center px-5 py-3 font-semibold text-slate-500 uppercase tracking-[0.4px] bg-slate-50 border-b border-slate-200">Total</th>
                        <th class="text-center px-5 py-3 font-semibold text-slate-500 uppercase tracking-[0.4px] bg-slate-50 border-b border-slate-200">Aprobados</th>
                        <th class="text-center px-5 py-3 font-semibold text-slate-500 uppercase tracking-[0.4px] bg-slate-50 border-b border-slate-200">% Aprobación</th>
                        <th class="px-5 py-3 font-semibold text-slate-500 uppercase tracking-[0.4px] bg-slate-50 border-b border-slate-200">Barra</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($statsPorCarrera as $s)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-5 py-3"><strong class="text-slate-900">{{ $s['carrera']->codigo_carrera }}</strong> <span class="text-slate-500">— {{ $s['carrera']->nombre_carrera }}</span></td>
                            <td class="text-center px-5 py-3 text-slate-900">{{ $s['total'] }}</td>
                            <td class="text-center px-5 py-3 font-semibold text-emerald-600">{{ $s['aprobados'] }}</td>
                            <td class="text-center px-5 py-3 font-semibold text-slate-900">{{ $s['porcentaje'] }}%</td>
                            <td class="px-5 py-3">
                                <div class="h-2 bg-red-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-emerald-600" style="width:{{ $s['porcentaje'] }}%;"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($totalPostulantes === 0)
        <div class="bg-white border border-slate-200 rounded-xl text-center py-10 text-slate-400">
            No hay datos registrados para los filtros seleccionados.
        </div>
    @endif
</x-layouts.app>
