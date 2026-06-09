<x-layouts.app title="Registrar Notas">
    <div style="max-width:1100px;">
        <div class="flex-between" style="margin-bottom:8px;">
            <div>
                <h1 class="page-title">Registrar Notas</h1>
                <p class="page-desc">Ingresá las calificaciones de los postulantes en las evaluaciones de tu grupo.</p>
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

        @if (!empty($sinRegistro))
            <div class="card" style="padding:24px;text-align:center;">
                <p style="color:#64748b;">No estás registrado como docente en el sistema. Contactá al administrador.</p>
            </div>
        @else
            {{-- Selectores: Grupo y Evaluación --}}
            <div class="card" style="margin-bottom:20px;">
                <form method="GET" action="{{ route('academico.notas.index') }}" style="display:flex;gap:16px;align-items:end;flex-wrap:wrap;">
                    <div style="min-width:250px;flex:1;">
                        <label for="id_grupo">Seleccionar Grupo</label>
                        <select name="id_grupo" id="id_grupo" onchange="this.form.submit()" required>
                            <option value="">— Seleccioná un grupo —</option>
                            @foreach ($grupos as $g)
                                <option value="{{ $g->id_grupo }}" @selected($g->id_grupo == request('id_grupo'))>
                                    {{ $g->nombre_grupo }}
                                    @if ($g->materia) — {{ $g->materia->nombre_materia }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($grupoSeleccionado && $evaluaciones->isNotEmpty())
                        <div style="min-width:220px;flex:1;">
                            <label for="id_evaluacion">Evaluación</label>
                            <select name="id_evaluacion" id="id_evaluacion" onchange="this.form.submit()" required>
                                <option value="">— Seleccioná una evaluación —</option>
                                @foreach ($evaluaciones as $e)
                                    <option value="{{ $e->id_evaluacion }}" @selected($e->id_evaluacion == request('id_evaluacion'))>
                                        {{ $e->numero_evaluacion }}°
                                        @switch($e->numero_evaluacion)
                                            @case(1) Primer Parcial @break
                                            @case(2) Segundo Parcial @break
                                            @case(3) Examen Final @break
                                        @endswitch
                                        ({{ $e->porcentaje }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <noscript><button type="submit" class="button button-sm">Ir</button></noscript>
                </form>
            </div>

            {{-- Panel de información del grupo seleccionado --}}
            @if ($grupoSeleccionado)
                <div style="margin-bottom:20px;padding:12px 16px;background:#f8fafc;border-radius:6px;font-size:13px;display:flex;gap:24px;flex-wrap:wrap;">
                    <div><strong>Grupo:</strong> {{ $grupoSeleccionado->nombre_grupo }}</div>
                    <div><strong>Materia:</strong> {{ $grupoSeleccionado->materia?->nombre_materia ?? '—' }}</div>
                    <div><strong>Estudiantes:</strong> {{ $postulantes->count() }}</div>
                    @if ($evaluacionSeleccionada)
                        <div><strong>Evaluación:</strong>
                            {{ $evaluacionSeleccionada->numero_evaluacion }}°
                            @switch($evaluacionSeleccionada->numero_evaluacion)
                                @case(1) Parcial @break
                                @case(2) Parcial @break
                                @case(3) Final @break
                            @endswitch
                            ({{ $evaluacionSeleccionada->porcentaje }}%)
                        </div>
                    @endif
                </div>
            @endif

            {{-- Tabla de notas --}}
            @if ($grupoSeleccionado && $evaluacionSeleccionada)
                @if ($postulantes->isEmpty())
                    <div class="card" style="padding:24px;text-align:center;">
                        <p style="color:#64748b;">No hay postulantes asignados a este grupo.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('academico.notas.store') }}">
                        @csrf
                        <input type="hidden" name="id_grupo" value="{{ $grupoSeleccionado->id_grupo }}">
                        <input type="hidden" name="id_evaluacion" value="{{ $evaluacionSeleccionada->id_evaluacion }}">

                        <div class="card" style="padding:0;overflow:hidden;">
                            <div style="overflow-x:auto;">
                                <table class="table" style="margin-bottom:0;">
                                    <thead>
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>Postulante</th>
                                            <th>CI</th>
                                            <th style="width:180px;">Nota (0-100)</th>
                                            <th style="width:100px;">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($postulantes as $i => $postulante)
                                            @php
                                                $notaExistente = $notasExistentes->get($postulante->id_postulante);
                                            @endphp
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>
                                                    <strong>{{ $postulante->prepostulante?->nombres }} {{ $postulante->prepostulante?->apellidos }}</strong>
                                                </td>
                                                <td>{{ $postulante->prepostulante?->ci ?? '—' }}</td>
                                                <td>
                                                    <input type="number"
                                                           name="notas[{{ $postulante->id_postulante }}]"
                                                           value="{{ old('notas.' . $postulante->id_postulante, $notaExistente?->nota) }}"
                                                           min="0" max="100" step="0.01"
                                                           placeholder="0.00"
                                                           required
                                                           style="width:140px;margin-bottom:0;text-align:center;font-weight:600;font-size:16px;">
                                                </td>
                                                <td>
                                                    @if ($notaExistente)
                                                        <span class="badge badge-activo" style="font-size:10px;">REGISTRADA</span>
                                                    @else
                                                        <span style="color:#94a3b8;font-size:12px;">Pendiente</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div style="margin-top:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                            <div style="font-size:13px;color:#64748b;">
                                <strong>{{ $postulantes->count() }}</strong> postulantes — Notas válidas de <strong>0.00</strong> a <strong>100.00</strong>
                            </div>
                            <button type="submit" class="button">💾 Guardar Notas</button>
                        </div>
                    </form>
                @endif

            @elseif ($grupoSeleccionado && $evaluaciones->isEmpty())
                <div class="card" style="padding:24px;text-align:center;">
                    <p style="color:#64748b;">No hay evaluaciones configuradas para la materia de este grupo. El administrador debe configurarlas primero en <strong>Evaluación Académica → Configurar Evaluaciones</strong>.</p>
                </div>
            @else
                <div class="card" style="padding:24px;text-align:center;">
                    <p style="color:#64748b;">Seleccioná un grupo y una evaluación para registrar notas.</p>
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
