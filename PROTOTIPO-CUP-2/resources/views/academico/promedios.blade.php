<x-layouts.app title="Promedios y Resultados">
    <div style="max-width:1200px;">
        <div class="flex-between" style="margin-bottom:8px;">
            <div>
                <h1 class="page-title">Promedios y Resultados Académicos</h1>
                <p class="page-desc">Calculá los promedios ponderados y verificá el estado (aprobado/reprobado) de los postulantes.</p>
            </div>
            @if(in_array(auth()->user()->rol?->nombre_rol, ['coordinador_academico', 'administrador']))
                <div style="display:flex;gap:8px;">
                    <form method="POST" action="{{ route('academico.promedios.calcular') }}"
                          onsubmit="return confirm('¿Calcular promedios de todos los postulantes? Esta acción actualizará los resultados existentes.');">
                        @csrf
                        <button type="submit" class="button">📊 Calcular Promedios</button>
                    </form>
                </div>
            @endif
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
                <p style="color:#64748b;">No hay una gestión académica activa. Configurá una antes de calcular promedios.</p>
            </div>
        @else
            {{-- Gestión info --}}
            <div style="margin-bottom:20px;padding:12px 16px;background:#f8fafc;border-radius:6px;font-size:13px;">
                <strong>Gestión activa:</strong> {{ $gestionActiva->nombre_gestion }}
                <span style="color:#64748b;margin-left:8px;">({{ $gestionActiva->fecha_inicio->format('d/m/Y') }} — {{ $gestionActiva->fecha_fin?->format('d/m/Y') ?? '—' }})</span>
            </div>

            {{-- Summary cards --}}
            <div class="grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));">
                <div class="metric">
                    <div class="label">Total con Notas</div>
                    <strong>{{ $totalPostulantes }}</strong>
                </div>
                <div class="metric">
                    <div class="label">Notas Completas</div>
                    <strong>{{ $completos->count() }}</strong>
                </div>
                <div class="metric">
                    <div class="label">Incompletos</div>
                    <strong style="color:#f59e0b;">{{ $incompletos }}</strong>
                </div>
                <div class="metric" style="border-left:4px solid #059669;">
                    <div class="label">Aprobados</div>
                    <strong style="color:#059669;">{{ $aprobados }}</strong>
                </div>
                <div class="metric" style="border-left:4px solid #dc2626;">
                    <div class="label">Reprobados</div>
                    <strong style="color:#dc2626;">{{ $reprobados }}</strong>
                </div>
                <div class="metric">
                    <div class="label">Ya Calculados</div>
                    <strong>{{ $yaCalculados }}</strong>
                </div>
            </div>

            {{-- Tabla de postulantes --}}
            @if ($postulantes->isEmpty())
                <div class="card" style="padding:24px;text-align:center;">
                    <p style="color:#64748b;">No hay postulantes con notas registradas. Primero registrá las notas en <strong>Registrar Notas</strong>.</p>
                </div>
            @else
                <div class="card" style="padding:0;overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table class="table" style="margin-bottom:0;font-size:13px;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Postulante</th>
                                    <th>CI</th>
                                    @foreach ($materias as $m)
                                        <th style="text-align:center;min-width:100px;">{{ $m->nombre_materia }}</th>
                                    @endforeach
                                    <th style="text-align:center;min-width:90px;">Prom. Final</th>
                                    <th style="text-align:center;">Estado</th>
                                    <th style="text-align:center;">Publicado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($postulantes as $i => $p)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><strong>{{ $p['nombre'] }}</strong></td>
                                        <td>{{ $p['ci'] ?? '—' }}</td>
                                        @foreach ($materias as $m)
                                            @php $pm = $p['promedios_materia'][$m->id_materia] ?? null; @endphp
                                            <td style="text-align:center;font-weight:600;">
                                                @if ($pm && $pm['promedio'] !== null)
                                                    <span style="color:{{ $pm['aprobada'] ? '#059669' : '#dc2626' }}">
                                                        {{ number_format($pm['promedio'], 2) }}
                                                    </span>
                                                @else
                                                    <span style="color:#94a3b8;">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td style="text-align:center;font-weight:700;">
                                            @if ($p['promedio_final'] !== null)
                                                {{ number_format($p['promedio_final'], 2) }}
                                            @else
                                                <span style="color:#94a3b8;">—</span>
                                            @endif
                                        </td>
                                        <td style="text-align:center;">
                                            @if ($p['estado_academico'] === 'aprobado')
                                                <span class="badge badge-aprobado">APROBADO</span>
                                            @elseif ($p['estado_academico'] === 'reprobado')
                                                <span class="badge badge-reprobado">REPROBADO</span>
                                            @else
                                                <span style="color:#f59e0b;font-size:11px;font-weight:600;">INCOMPLETO</span>
                                            @endif
                                        </td>
                                        <td style="text-align:center;">
                                            @if ($p['resultado_db'])
                                                @if ($p['resultado_db']->publicado)
                                                    <span class="badge badge-activo" style="font-size:10px;">SÍ</span>
                                                @else
                                                    <span style="color:#f59e0b;font-size:11px;">No</span>
                                                @endif
                                            @else
                                                <span style="color:#94a3b8;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Leyenda --}}
                <div style="margin-top:16px;padding:12px 16px;background:#f8fafc;border-radius:6px;font-size:12px;color:#64748b;display:flex;gap:20px;flex-wrap:wrap;">
                    <span>✅ <strong>Aprobado:</strong> promedio ≥ 60 en <strong>todas</strong> las materias</span>
                    <span>❌ <strong>Reprobado:</strong> promedio &lt; 60 en al menos una materia</span>
                    <span>⚠️ <strong>Incompleto:</strong> faltan notas en una o más evaluaciones</span>
                </div>

                @if ($yaCalculados > 0)
                    <div style="margin-top:16px;padding:12px 16px;background:#fef3c7;border-radius:6px;font-size:13px;color:#92400e;">
                        ⚠️ Ya existen {{ $yaCalculados }} resultados calculados. Al hacer clic en "Calcular Promedios" se actualizarán.
                    </div>
                @endif
            @endif
        @endif
    </div>
</x-layouts.app>
