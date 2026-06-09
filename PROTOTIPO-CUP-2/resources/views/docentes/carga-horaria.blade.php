<x-layouts.app title="Carga Horaria de Docentes">
    <div>
        <h1 class="page-title">Carga Horaria de Docentes</h1>
        <p class="page-desc">Asigná grupos a docentes aprobados. Cada grupo ya tiene su materia, aula y horarios definidos (máx. 4 grupos por docente).</p>

        @if ($errors->any())
            <div class="error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
            <div style="padding:12px 16px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Docentes aprobados</div>
                <div style="font-size:22px;font-weight:700;margin-top:2px;">{{ $docentes->count() }}</div>
            </div>
            <div style="padding:12px 16px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Grupos activos</div>
                <div style="font-size:22px;font-weight:700;margin-top:2px;">{{ $grupos->count() }}</div>
            </div>
        </div>

        @forelse ($docentes as $docente)
            @php
                $cargas = $docente->cargasHorarias;
                $gruposAsignados = $cargas->pluck('grupo');
            @endphp
            <div class="card" style="padding:16px;margin-bottom:12px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#059669;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">
                            {{ strtoupper(substr($docente->nombres, 0, 1)) }}{{ strtoupper(substr($docente->apellidos, 0, 1)) }}
                        </div>
                        <div>
                            <strong style="font-size:15px;">{{ $docente->nombre_completo }}</strong>
                            <span style="font-size:12px;color:#64748b;margin-left:6px;">{{ $docente->profesion }}</span>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
                        <span style="color:#64748b;">Grupos:</span>
                        <strong>{{ $gruposAsignados->count() }}/4</strong>
                    </div>
                </div>

                {{-- Cargas actuales --}}
                @if ($cargas->isNotEmpty())
                    <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach ($cargas as $c)
                            @php $g = $c->grupo; @endphp
                            <div style="padding:5px 10px;background:#f1f5f9;border-radius:6px;font-size:12px;display:flex;align-items:center;gap:6px;">
                                <strong>{{ $g?->nombre_grupo }}</strong>
                                <span style="color:#64748b;">|</span>
                                <span style="color:#2563eb;">{{ $g?->materia?->nombre_materia }}</span>
                                <span style="color:#64748b;">|</span>
                                🏛️ {{ $g?->aula?->codigo_aula ?? '—' }}
                                @if ($g?->horarios->isNotEmpty())
                                    @foreach ($g->horarios as $gh)
                                        <span style="background:#e0e7ff;padding:1px 5px;border-radius:3px;">{{ $gh->horario->dia_semana }} {{ substr($gh->horario->hora_inicio, 0, 5) }}</span>
                                    @endforeach
                                @endif
                                <form method="POST" action="{{ route('docentes.carga-horaria.quitar', $c->id_carga_horaria) }}"
                                      style="display:inline;margin:0;"
                                      onsubmit="return confirm('¿Quitar esta carga horaria?')">
                                    @csrf
                                    <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px;padding:0 2px;">✕</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="margin-top:10px;font-size:13px;color:#94a3b8;">Sin cargas horarias asignadas.</div>
                @endif

                {{-- Formulario agregar carga --}}
                <details style="margin-top:10px;font-size:13px;">
                    <summary style="cursor:pointer;color:#2563eb;font-weight:500;user-select:none;">+ Asignar nuevo grupo</summary>
                    <form method="POST" action="{{ route('docentes.carga-horaria.store') }}" style="margin-top:10px;">
                        @csrf
                        <input type="hidden" name="id_docente" value="{{ $docente->id_docente }}">
                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
                            <div style="flex:1;min-width:200px;">
                                <label style="font-size:11px;">Grupo *</label>
                                <select name="id_grupo" required style="margin:0;padding:5px 8px;font-size:12px;">
                                    <option value="">— Seleccionar grupo —</option>
                                    @foreach ($grupos as $g)
                                        @php
                                            $yaAsignado = $gruposAsignados->firstWhere('id_grupo', $g->id_grupo);
                                        @endphp
                                        <option value="{{ $g->id_grupo }}" {{ $yaAsignado ? 'disabled' : '' }}>
                                            {{ $g->nombre_grupo }}
                                            — {{ $g->materia?->nombre_materia }}
                                            — 🏛️ {{ $g->aula?->codigo_aula ?? 'sin aula' }}
                                            @if ($g->horarios->isNotEmpty())
                                                — 📅 {{ $g->horarios->first()->horario->dia_semana }} {{ substr($g->horarios->first()->horario->hora_inicio, 0, 5) }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="button button-sm">Asignar</button>
                        </div>
                    </form>
                </details>
            </div>
        @empty
            <div class="card" style="padding:32px;text-align:center;color:#94a3b8;">
                No hay docentes aprobados. Primero registrá y aprobá docentes.
            </div>
        @endforelse
    </div>
</x-layouts.app>
