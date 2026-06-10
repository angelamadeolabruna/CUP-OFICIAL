<x-layouts.app title="Asignar Grupos, Aulas, Horarios y Materias">
    <div>
        <h1 class="page-title">Asignar Grupos, Aulas, Horarios y Materias</h1>
        <p class="page-desc">Organizá los grupos del curso preuniversitario: cada grupo pertenece a una materia, con su aula y horarios.</p>

        @if ($errors->any())
            <div class="error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Resumen --}}
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
            <div style="flex:1;min-width:120px;padding:14px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Gestión</div>
                <div style="font-size:18px;font-weight:700;margin-top:2px;">{{ $gestion?->nombre_gestion ?? '—' }}</div>
            </div>
            <div style="flex:1;min-width:120px;padding:14px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Inscritos</div>
                <div style="font-size:18px;font-weight:700;margin-top:2px;">{{ $totalInscritos }}</div>
            </div>
            <div style="flex:1;min-width:120px;padding:14px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Capacidad/grupo</div>
                <div style="font-size:18px;font-weight:700;margin-top:2px;">{{ $capacidad?->max_estudiantes ?? '—' }}</div>
            </div>
            <div style="flex:1;min-width:120px;padding:14px 18px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Grupos por materia</div>
                <div style="font-size:18px;font-weight:700;margin-top:2px;">{{ $gruposCalculados ?? '—' }}</div>
            </div>
        </div>

        {{-- Step 1: Generar Grupos --}}
        <div class="card" style="padding:20px;margin-bottom:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <strong style="font-size:15px;">1. Generar Grupos por Materia</strong>
                    <div style="font-size:13px;color:#64748b;margin-top:2px;">
                        @if ($grupos->isEmpty())
                            @if ($gruposCalculados)
                                Se generarán <strong>{{ $gruposCalculados }} grupos por materia</strong>
                                ({{ $gruposCalculados * $materias->count() }} en total).
                            @else
                                Calculá la cantidad de grupos desde la pestaña "Calcular Grupos".
                            @endif
                        @else
                            {{ $grupos->count() }} grupos existentes
                            ({{ $gruposCalculados }} por materia).
                            Al regenerar se eliminarán los grupos actuales.
                        @endif
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    @if ($gruposCalculados)
                        <form method="POST" action="{{ route('logistica.asignar.generar') }}"
                              onsubmit="return confirm('{{ $grupos->isEmpty() ? '¿Crear ' . ($gruposCalculados * $materias->count()) . ' grupos (' . $gruposCalculados . ' por materia)?' : '¿Regenerar grupos? Se eliminarán los actuales con todas sus asignaciones.' }}')">
                            @csrf
                            <button type="submit" class="button">
                                {{ $grupos->isEmpty() ? '🚀 Generar Grupos' : '🔄 Regenerar Grupos' }}
                            </button>
                        </form>
                    @else
                        <button class="button" disabled style="opacity:.5;cursor:not-allowed;">
                            ⏳ Calcular grupos primero
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Step 2 & 3: Asignar Aulas y Horarios --}}
        @if ($grupos->isNotEmpty())
            <div class="card" style="padding:0;overflow:hidden;margin-bottom:20px;">
                <div style="padding:16px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    <strong style="font-size:15px;">2. Asignar Aulas &nbsp;·&nbsp; 3. Asignar Horarios</strong>
                    <span style="font-size:12px;color:#64748b;margin-left:8px;">Cada grupo tiene una materia fija.</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Materia</th>
                                <th>Aula</th>
                                <th style="min-width:260px;">Horarios</th>
                                <th>Postulantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grupos as $grupo)
                                <tr>
                                    <td style="font-weight:700;font-size:15px;">{{ $grupo->nombre_grupo }}</td>
                                    <td>
                                        <span style="display:inline-block;padding:3px 8px;background:#e0e7ff;color:#4338ca;border-radius:4px;font-size:12px;font-weight:500;">
                                            {{ $grupo->materia?->nombre_materia ?? '—' }}
                                        </span>
                                    </td>

                                    {{-- Aula --}}
                                    <td>
                                        <form method="POST" action="{{ route('logistica.asignar.aula', $grupo->id_grupo) }}"
                                              style="display:flex;gap:6px;align-items:center;">
                                            @csrf
                                            <select name="id_aula" style="margin:0;width:auto;min-width:120px;padding:6px 8px;font-size:12px;"
                                                    onchange="this.form.submit()">
                                                <option value="">— Sin aula —</option>
                                                @foreach ($aulas as $aula)
                                                    <option value="{{ $aula->id_aula }}"
                                                        {{ $grupo->id_aula == $aula->id_aula ? 'selected' : '' }}>
                                                        {{ $aula->codigo_aula }}
                                                        @if ($aula->ubicacion)
                                                            ({{ $aula->ubicacion }})
                                                        @endif
                                                        — Cap.{{ $aula->capacidad }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <noscript><button type="submit" class="button button-sm">Asignar</button></noscript>
                                        </form>
                                    </td>

                                    {{-- Horarios --}}
                                    <td>
                                        <div style="display:flex;flex-direction:column;gap:4px;">
                                            @forelse ($grupo->horarios as $gh)
                                                <div style="display:flex;align-items:center;gap:6px;font-size:12px;padding:3px 8px;background:#f1f5f9;border-radius:4px;justify-content:space-between;">
                                                    <span>
                                                        {{ $gh->horario?->dia_semana }}
                                                        {{ substr($gh->horario?->hora_inicio, 0, 5) }}-
                                                        {{ substr($gh->horario?->hora_fin, 0, 5) }}
                                                        @if ($gh->horario?->turno)
                                                            <span style="font-size:10px;color:#64748b;">({{ $gh->horario->turno }})</span>
                                                        @endif
                                                    </span>
                                                    <form method="POST" action="{{ route('logistica.asignar.horario.quitar', $gh->id_grupo_horario) }}"
                                                          style="display:inline;margin:0;">
                                                        @csrf
                                                        <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:2px 4px;font-size:14px;line-height:1;"
                                                                onclick="return confirm('¿Quitar este horario?')">✕</button>
                                                    </form>
                                                </div>
                                            @empty
                                                <span style="color:#94a3b8;font-size:12px;">Sin horarios asignados</span>
                                            @endforelse

                                            {{-- Formulario agregar horario (sin materia) --}}
                                            <form method="POST" action="{{ route('logistica.asignar.horario.agregar') }}"
                                                  style="margin-top:4px;">
                                                @csrf
                                                <input type="hidden" name="id_grupo" value="{{ $grupo->id_grupo }}">
                                                <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:flex-end;">
                                                    <div>
                                                        <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:4px;">
                                                            @forelse ($horarios as $h)
                                                                <label style="display:flex;align-items:center;gap:2px;font-size:11px;font-weight:400;cursor:pointer;padding:2px 6px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;">
                                                                    <input type="checkbox" name="id_horarios[]" value="{{ $h->id_horario }}"
                                                                           style="width:auto;margin:0;accent-color:#0a2a5e;">
                                                                    {{ $h->dia_semana }} {{ substr($h->hora_inicio, 0, 5) }}
                                                                </label>
                                                            @empty
                                                                <span style="font-size:11px;color:#94a3b8;">Sin horarios — creá uno arriba</span>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="button button-sm" style="padding:4px 10px;font-size:11px;">+ Asignar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>

                                    {{-- Postulantes --}}
                                    <td>
                                        <div style="text-align:center;">
                                            <div style="font-weight:700;font-size:16px;">
                                                {{ $grupo->postulantes->count() }}
                                                <span style="font-size:11px;color:#64748b;font-weight:400;">
                                                    / {{ $grupo->capacidad_maxima }}
                                                </span>
                                            </div>
                                            @if ($grupo->postulantes->isNotEmpty())
                                                <button class="button button-sm button-secondary"
                                                        style="margin-top:4px;font-size:10px;padding:3px 8px;"
                                                        data-postulantes='@json($grupo->postulantes->map(fn($pg) => ['nombre' => $pg->postulante?->usuario?->nombre_usuario ?? '—', 'ci' => $pg->postulante?->usuario?->ci ?? '—'])->values())'
                                                    onclick="verPostulantes(this, '{{ $grupo->nombre_grupo }}')">
                                                    👥 Ver
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Schedule cards: horario semanal por grupo --}}
            @foreach ($grupos as $grupo)
                @if ($grupo->horarios->isNotEmpty() || $grupo->aula)
                    <div class="card" style="padding:16px;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                            <div>
                                <strong style="font-size:16px;">{{ $grupo->nombre_grupo }}</strong>
                                <span style="font-size:12px;color:#4338ca;margin-left:6px;background:#e0e7ff;padding:2px 6px;border-radius:4px;">
                                    {{ $grupo->materia?->nombre_materia }}
                                </span>
                                @if ($grupo->aula)
                                    <span style="font-size:13px;color:#64748b;margin-left:8px;">
                                        — 🏛️ {{ $grupo->aula->codigo_aula }} (Cap.{{ $grupo->aula->capacidad }})
                                    </span>
                                @else
                                    <span style="font-size:13px;color:#f59e0b;margin-left:8px;">— Sin aula asignada</span>
                                @endif
                            </div>
                            <span style="font-size:12px;color:#64748b;">
                                {{ $grupo->postulantes->count() }}/{{ $grupo->capacidad_maxima }} postulantes
                            </span>
                        </div>
                        @if ($grupo->horarios->isNotEmpty())
                            <div style="margin-top:10px;display:flex;flex-direction:column;gap:4px;">
                                @php
                                    $ordenDias = ['Lunes'=>1,'Martes'=>2,'Miércoles'=>3,'Jueves'=>4,'Viernes'=>5,'Sábado'=>6];
                                    $diasConHorario = $grupo->horarios
                                        ->groupBy(fn($gh) => $gh->horario?->dia_semana)
                                        ->sortBy(fn($item, $key) => $ordenDias[$key] ?? 99);
                                @endphp
                                <div style="font-size:12px;color:#475569;font-weight:600;margin-bottom:4px;">📅 Horario semanal:</div>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                    @foreach ($diasConHorario as $dia => $ghs)
                                        @foreach ($ghs as $gh)
                                            <div style="padding:5px 10px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;font-size:12px;">
                                                <strong>{{ $dia }}</strong>
                                                {{ substr($gh->horario?->hora_inicio, 0, 5) }}-{{ substr($gh->horario?->hora_fin, 0, 5) }}
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div style="margin-top:6px;font-size:12px;color:#94a3b8;">Sin horarios asignados todavía</div>
                        @endif
                    </div>
                @endif
            @endforeach

            {{-- Step 4: Distribuir Postulantes --}}
            <div class="card" style="padding:20px;margin-bottom:20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <strong style="font-size:15px;">4. Distribuir Postulantes</strong>
                        <div style="font-size:13px;color:#64748b;margin-top:2px;">
                            Distribuye los {{ $totalInscritos }} postulantes en los grupos de cada materia.
                            Cada postulante será asignado a <strong>{{ $materias->count() }} grupos</strong> (uno por materia).
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logistica.asignar.distribuir') }}"
                          onsubmit="return confirm('¿Distribuir todos los postulantes en los grupos de cada materia? Se reemplazarán las asignaciones actuales.')">
                        @csrf
                        <button type="submit" class="button">📦 Distribuir Postulantes</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Quick create: Aulas --}}
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <strong style="font-size:15px;">🏛️ Registrar aula nueva</strong>
            <form method="POST" action="{{ route('logistica.aulas.store') }}" style="margin-top:10px;">
                @csrf
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                    <div style="flex:1;min-width:100px;">
                        <label for="asignar_codigo_aula">Código *</label>
                        <input type="text" name="codigo_aula" id="asignar_codigo_aula" placeholder="Ej: A-101" required style="margin:0;">
                    </div>
                    <div style="flex:1;min-width:80px;">
                        <label for="asignar_capacidad">Capacidad *</label>
                        <input type="number" name="capacidad" id="asignar_capacidad" min="1" max="500" required style="margin:0;">
                    </div>
                    <div style="flex:1;min-width:80px;">
                        <label for="asignar_ubicacion">Ubicación</label>
                        <input type="text" name="ubicacion" id="asignar_ubicacion" placeholder="Ej: Edif A, Piso 1" style="margin:0;">
                    </div>
                    <button type="submit" class="button button-sm">+ Registrar Aula</button>
                </div>
            </form>
        </div>

        {{-- Quick create: Horarios --}}
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <strong style="font-size:15px;">🕐 Registrar horario nuevo</strong>
            <form method="POST" action="{{ route('logistica.horarios.store') }}" style="margin-top:10px;">
                @csrf
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                    <div style="flex:2;min-width:200px;">
                        <label>Días *</label>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;">
                            @foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $dia)
                                <label style="display:flex;align-items:center;gap:3px;font-weight:400;font-size:13px;cursor:pointer;">
                                    <input type="checkbox" name="dias[]" value="{{ $dia }}"
                                           style="width:auto;margin:0;accent-color:#0a2a5e;">
                                    {{ $dia }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div style="flex:1;min-width:80px;">
                        <label for="asignar_hora_ini">Hora inicio *</label>
                        <input type="time" name="hora_inicio" id="asignar_hora_ini" value="08:00" required style="margin:0;">
                    </div>
                    <div style="flex:1;min-width:80px;">
                        <label for="asignar_hora_fin">Hora fin *</label>
                        <input type="time" name="hora_fin" id="asignar_hora_fin" value="10:00" required style="margin:0;">
                    </div>
                    <div style="flex:1;min-width:80px;">
                        <label for="asignar_turno">Turno</label>
                        <select name="turno" id="asignar_turno" style="margin:0;">
                            <option value="">—</option>
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                            <option value="Noche">Noche</option>
                        </select>
                    </div>
                    <button type="submit" class="button button-sm">+ Registrar Horario</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal ver postulantes del grupo --}}
    <dialog id="modalPostulantes" style="border:none;border-radius:16px;padding:0;max-width:520px;width:92%;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <div style="background:linear-gradient(135deg,#0f172a,#1e293b);border-radius:16px 16px 0 0;padding:20px 24px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-size:18px;font-weight:700;color:#fff;" id="modalPostulantesTitle">Postulantes</div>
                <div style="font-size:12px;color:#94a3b8;margin-top:2px;" id="modalPostulantesSub">0 asignados</div>
            </div>
            <button onclick="document.getElementById('modalPostulantes').close()"
                    style="background:rgba(255,255,255,.1);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;transition:.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,.2)'"
                    onmouseout="this.style.background='rgba(255,255,255,.1)'">✕</button>
        </div>
        <div style="padding:16px 24px 8px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;gap:16px;font-size:12px;color:#64748b;">
            <span>👥 <strong id="modalCount">0</strong> postulantes</span>
            <span>📋 Cupo <strong id="modalCap">0</strong></span>
        </div>
        <div style="max-height:380px;overflow-y:auto;padding:8px 0;background:#fff;" id="modalPostulantesBody">
            <div id="modalPostulantesList"></div>
        </div>
    </dialog>

    <style>
        dialog {
            margin: auto;
            position: fixed;
            top: 0; right: 0; bottom: 0; left: 0;
        }
        dialog::backdrop {
            background: rgba(0,0,0,.45);
        }
    </style>
    <script>
        function verPostulantes(btn, nombreGrupo) {
            const postulantes = JSON.parse(btn.getAttribute('data-postulantes'));
            document.getElementById('modalPostulantesTitle').textContent = '👥 Postulantes — ' + nombreGrupo;
            document.getElementById('modalPostulantesSub').textContent = postulantes.length + ' asignado' + (postulantes.length !== 1 ? 's' : '');
            document.getElementById('modalCount').textContent = postulantes.length;

            const capText = btn.closest('tr')?.querySelector('.table td:last-child')?.textContent?.trim() || '—';
            document.getElementById('modalCap').textContent = capText;

            const list = document.getElementById('modalPostulantesList');
            list.innerHTML = '';
            if (postulantes.length === 0) {
                list.innerHTML = '<div style="text-align:center;padding:40px 20px;color:#94a3b8;font-size:14px;">📭 Sin postulantes asignados a este grupo</div>';
            } else {
                postulantes.forEach(function(p, i) {
                    const card = document.createElement('div');
                    card.style.cssText = 'display:flex;align-items:center;gap:12px;padding:10px 24px;border-bottom:1px solid #f1f5f9;transition:.1s;';
                    card.onmouseover = function() { this.style.background = '#f8fafc'; };
                    card.onmouseout = function() { this.style.background = ''; };
                    const iniciales = (p.nombre || '?').charAt(0).toUpperCase();
                    card.innerHTML = '' +
                        '<div style="width:36px;height:36px;border-radius:50%;background:#0a2a5e;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">' + iniciales + '</div>' +
                        '<div style="flex:1;min-width:0;">' +
                            '<div style="font-weight:600;font-size:14px;color:#0f172a;">' + p.nombre + '</div>' +
                            '<div style="font-size:12px;color:#64748b;">CI: ' + p.ci + '</div>' +
                        '</div>' +
                        '<div style="font-size:11px;color:#94a3b8;background:#f1f5f9;padding:2px 8px;border-radius:10px;">#' + (i + 1) + '</div>';
                    list.appendChild(card);
                });
            }
            document.getElementById('modalPostulantes').showModal();
        }
    </script>
</x-layouts.app>

