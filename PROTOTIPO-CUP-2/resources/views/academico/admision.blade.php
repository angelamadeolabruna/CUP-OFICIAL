<x-layouts.app title="Admisión por Cupos">
    <div style="max-width:1200px;">
        <div class="flex-between" style="margin-bottom:8px;">
            <div>
                <h1 class="page-title">Admisión por Cupos</h1>
                <p class="page-desc">Configurá los cupos por carrera y ejecutá el proceso de admisión automática.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="error" style="margin-bottom:16px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (!$gestionActiva)
            <div class="card" style="padding:24px;text-align:center;">
                <p style="color:#64748b;">No hay una gestión académica activa. Creá una antes de gestionar la admisión.</p>
            </div>
        @else
            {{-- Gestión info --}}
            <div style="margin-bottom:20px;padding:12px 16px;background:#f8fafc;border-radius:6px;font-size:13px;">
                <strong>Gestión activa:</strong> {{ $gestionActiva->nombre_gestion }}
                <span style="color:#64748b;margin-left:8px;">({{ $gestionActiva->fecha_inicio->format('d/m/Y') }} — {{ $gestionActiva->fecha_fin?->format('d/m/Y') ?? '—' }})</span>
            </div>

            {{-- Sección 1: Configurar Cupos --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="flex-between" style="margin-bottom:16px;">
                    <h2 style="font-size:16px;font-weight:700;">Configurar Cupos por Carrera</h2>
                </div>

                <form method="POST" action="{{ route('academico.admision.cupos.guardar') }}">
                    @csrf
                    <table class="table" style="margin-bottom:16px;">
                        <thead>
                            <tr>
                                <th>Carrera</th>
                                <th style="width:120px;">Cupos Totales</th>
                                <th style="width:120px;">Ocupados</th>
                                <th style="width:120px;">Disponibles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($carreras as $c)
                                @php $cupo = $cupos->get($c->id_carrera); @endphp
                                <tr>
                                    <td><strong>{{ $c->nombre_carrera }}</strong> ({{ $c->codigo_carrera }})</td>
                                    <td>
                                        <input type="hidden" name="cupos[{{ $c->id_carrera }}][id_carrera]" value="{{ $c->id_carrera }}">
                                        @if ($cupo)
                                            <input type="hidden" name="cupos[{{ $c->id_carrera }}][id_cupo]" value="{{ $cupo->id_cupo }}">
                                        @endif
                                        <input type="number" name="cupos[{{ $c->id_carrera }}][cupos_totales]"
                                               value="{{ old('cupos.' . $c->id_carrera . '.cupos_totales', $cupo?->cupos_totales ?? 100) }}"
                                               min="1" max="9999" required
                                               style="width:100px;text-align:center;margin-bottom:0;">
                                    </td>
                                    <td style="text-align:center;">{{ $cupo?->cupos_ocupados ?? 0 }}</td>
                                    <td style="text-align:center;font-weight:600;color:{{ $cupo && $cupo->cupos_disponibles > 0 ? '#059669' : '#dc2626' }}">
                                        {{ $cupo?->cupos_disponibles ?? 0 }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit" class="button">💾 Guardar Cupos</button>
                    </div>
                </form>
            </div>

            {{-- Sección 2: Resumen de Admisión --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="flex-between" style="margin-bottom:16px;">
                    <h2 style="font-size:16px;font-weight:700;">Resumen de Admisión</h2>
                    @if ($porAdmitir->isNotEmpty())
                        <form method="POST" action="{{ route('academico.admision.ejecutar') }}"
                              onsubmit="return confirm('¿Ejecutar admisión? Se resetearán los resultados anteriores y se asignarán cupos según el orden de mérito.')">
                            @csrf
                            <button type="submit" class="button">🚀 Ejecutar Admisión</button>
                        </form>
                    @endif
                </div>

                <div class="grid" style="margin-bottom:16px;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));">
                    <div class="metric">
                        <div class="label">Con Resultado</div>
                        <strong>{{ $conResultado->count() }}</strong>
                    </div>
                    <div class="metric" style="border-left:4px solid #059669;">
                        <div class="label">Aprobados</div>
                        <strong style="color:#059669;">{{ $aprobados->count() }}</strong>
                    </div>
                    <div class="metric" style="border-left:4px solid #dc2626;">
                        <div class="label">Reprobados</div>
                        <strong style="color:#dc2626;">{{ $reprobados->count() }}</strong>
                    </div>
                    <div class="metric" style="border-left:4px solid #2563eb;">
                        <div class="label">Admitidos</div>
                        <strong style="color:#2563eb;">{{ $admitidos->count() }}</strong>
                    </div>
                    <div class="metric" style="border-left:4px solid #f59e0b;">
                        <div class="label">Por Admitir</div>
                        <strong style="color:#f59e0b;">{{ $porAdmitir->count() }}</strong>
                    </div>
                </div>

                {{-- Resultados por carrera --}}
                <div style="margin-bottom:16px;display:grid;grid-template-columns:repeat(auto-fit, minmax(200px,1fr));gap:12px;">
                    @foreach ($carreras as $c)
                        @php $cupo = $cupos->get($c->id_carrera); @endphp
                        <div style="padding:12px;background:#f8fafc;border-radius:6px;border:1px solid #e2e8f0;">
                            <div style="font-size:12px;color:#64748b;font-weight:600;">{{ $c->codigo_carrera }}</div>
                            <div style="font-size:14px;font-weight:700;margin:2px 0;">{{ $c->nombre_carrera }}</div>
                            <div style="font-size:13px;">
                                Cupos: <strong>{{ $cupo?->cupos_ocupados ?? 0 }}/{{ $cupo?->cupos_totales ?? 0 }}</strong>
                                @if ($cupo && $cupo->cupos_disponibles > 0)
                                    <span style="color:#059669;">({{ $cupo->cupos_disponibles }} libres)</span>
                                @elseif ($cupo && $cupo->cupos_disponibles <= 0)
                                    <span style="color:#dc2626;">(lleno)</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Tabla de todos los postulantes con resultado --}}
                @if ($conResultado->isNotEmpty())
                    <h3 style="font-size:14px;font-weight:600;margin-bottom:8px;">
                        Postulantes con Resultado
                        <span style="font-weight:400;color:#64748b;">({{ $conResultado->count() }} total — {{ $aprobados->count() }} aprobados, {{ $reprobados->count() }} reprobados)</span>
                    </h3>
                    <div style="overflow-x:auto;">
                        <table class="table" style="font-size:13px;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Postulante</th>
                                    <th>Promedio</th>
                                    <th>1ra Opción</th>
                                    <th>2da Opción</th>
                                    <th>Carrera Asignada</th>
                                    <th>Opción</th>
                                    <th>Estado Académico</th>
                                    <th>Admisión</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($conResultado as $i => $p)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><strong>{{ $p->prepostulante?->nombres }} {{ $p->prepostulante?->apellidos }}</strong></td>
                                        <td style="font-weight:600;">{{ $p->resultado?->promedio_final ?? '—' }}</td>
                                        <td>{{ $p->primeraOpcion?->codigo_carrera ?? '—' }}</td>
                                        <td>{{ $p->segundaOpcion?->codigo_carrera ?? '—' }}</td>
                                        <td>
                                            @if ($p->admision && $p->admision->id_carrera_asignada)
                                                <strong>{{ $p->admision->carreraAsignada?->codigo_carrera ?? '—' }}</strong>
                                            @else
                                                <span style="color:#94a3b8;">—</span>
                                            @endif
                                        </td>
                                        <td style="text-align:center;">
                                            @if ($p->admision && $p->admision->opcion_asignada)
                                                {{ $p->admision->opcion_asignada }}°
                                            @else
                                                <span style="color:#94a3b8;">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($p->resultado->estado_academico === 'aprobado')
                                                <span class="badge badge-aprobado">APROBADO</span>
                                            @else
                                                <span class="badge badge-reprobado">REPROBADO</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($p->admision?->estado_admision === 'admitido')
                                                <span class="badge badge-aprobado">ADMITIDO</span>
                                            @elseif ($p->admision?->estado_admision === 'no_admitido')
                                                <span class="badge badge-reprobado">NO ADMITIDO</span>
                                            @elseif ($p->resultado->estado_academico === 'reprobado')
                                                <span style="color:#94a3b8;">—</span>
                                            @else
                                                <span style="color:#f59e0b;font-weight:600;">PENDIENTE</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="padding:20px;text-align:center;background:#f8fafc;border-radius:6px;color:#64748b;">
                        No hay postulantes con resultados. Calculá los promedios primero en <strong>Promedios y Resultados</strong>.
                    </div>
                @endif
            </div>

            {{-- Leyenda del algoritmo --}}
            <div style="padding:12px 16px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;font-size:12px;color:#0369a1;">
                <strong>🧠 Algoritmo de Admisión:</strong> Los postulantes aprobados se ordenan de <strong>mayor a menor promedio</strong>.
                Por cada postulante se intenta asignar su <strong>1ra opción</strong>; si no hay cupo, se evalúa su <strong>2da opción</strong>.
                Si tampoco hay cupo disponible, queda como <strong>No admitido</strong> por falta de cupo.
            </div>
        @endif
    </div>
</x-layouts.app>
