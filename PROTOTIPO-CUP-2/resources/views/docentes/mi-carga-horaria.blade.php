<x-layouts.app title="Mi Carga Horaria">
    <div>
        <h1 class="page-title">Mi Carga Horaria</h1>
        <p class="page-desc">Grupos, horarios y materias asignados.</p>

        @if (isset($sinRegistro))
            <div class="card" style="padding:40px;text-align:center;color:#94a3b8;">
                <div style="font-size:48px;margin-bottom:12px;">👨‍🏫</div>
                <div style="font-size:16px;font-weight:600;color:#475569;">No estás registrado como docente</div>
                <div style="font-size:13px;margin-top:4px;">No se encontró un registro docente vinculado a tu usuario.</div>
            </div>
        @else
            @php $cargas = $docente->cargasHorarias; @endphp

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;">
                <div style="padding:14px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Docente</div>
                    <div style="font-size:16px;font-weight:700;margin-top:2px;">{{ $docente->nombre_completo }}</div>
                </div>
                <div style="padding:14px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Grupos asignados</div>
                    <div style="font-size:22px;font-weight:700;margin-top:2px;">{{ $cargas->count() }}</div>
                </div>
                <div style="padding:14px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                    <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Estado</div>
                    <div style="font-size:14px;font-weight:600;margin-top:4px;">
                        <span class="badge badge-{{ $docente->estado_docente === 'aprobado' ? 'aprobado' : 'reprobado' }}">
                            {{ $docente->estado_docente }}
                        </span>
                    </div>
                </div>
            </div>

            @forelse ($cargas as $carga)
                @php $g = $carga->grupo; @endphp
                <div class="card" style="padding:16px;margin-bottom:12px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                        <div>
                            <strong style="font-size:18px;">{{ $g?->nombre_grupo }}</strong>
                            <span style="font-size:12px;color:#4338ca;margin-left:8px;background:#e0e7ff;padding:2px 8px;border-radius:4px;font-weight:500;">
                                {{ $g?->materia?->nombre_materia }}
                            </span>
                            @if ($g?->aula)
                                <span style="font-size:13px;color:#64748b;margin-left:8px;">
                                    — 🏛️ {{ $g->aula->codigo_aula }}
                                </span>
                            @endif
                        </div>
                        <span style="font-size:12px;color:#64748b;">
                            👥 {{ $g?->postulantes?->count() ?? 0 }} postulantes
                        </span>
                    </div>

                    @if ($g?->horarios->isNotEmpty())
                        <div style="margin-top:10px;">
                            <div style="font-size:12px;color:#475569;font-weight:600;margin-bottom:4px;">📅 Horario:</div>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                @php
                                    $ordenDias = ['Lunes'=>1,'Martes'=>2,'Miércoles'=>3,'Jueves'=>4,'Viernes'=>5,'Sábado'=>6];
                                    $horarios = $g->horarios->sortBy(fn($gh) => $ordenDias[$gh->horario?->dia_semana] ?? 99);
                                @endphp
                                @foreach ($horarios as $gh)
                                    <div style="padding:6px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;font-size:13px;">
                                        <strong>{{ $gh->horario?->dia_semana }}</strong>
                                        {{ substr($gh->horario?->hora_inicio, 0, 5) }}-{{ substr($gh->horario?->hora_fin, 0, 5) }}
                                        @if ($gh->horario?->turno)
                                            <span style="font-size:11px;color:#64748b;">({{ $gh->horario->turno }})</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div style="margin-top:6px;font-size:12px;color:#94a3b8;">Sin horario asignado todavía</div>
                    @endif
                </div>
            @empty
                <div class="card" style="padding:40px;text-align:center;color:#94a3b8;">
                    <div style="font-size:48px;margin-bottom:12px;">📭</div>
                    <div style="font-size:16px;font-weight:600;color:#475569;">Sin asignaciones</div>
                    <div style="font-size:13px;margin-top:4px;">Aún no te han asignado carga horaria.</div>
                </div>
            @endforelse
        @endif
    </div>
</x-layouts.app>
