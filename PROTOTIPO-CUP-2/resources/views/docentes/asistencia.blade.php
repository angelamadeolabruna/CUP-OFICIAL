<x-layouts.app title="Registrar Asistencia">
    <div>
        <h1 class="page-title">Registrar Asistencia</h1>
        <p class="page-desc">Seleccioná un grupo y una fecha para registrar la asistencia de los postulantes.</p>

        @if ($errors->any())
            <div class="error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (isset($sinRegistro))
            <div class="card" style="padding:40px;text-align:center;color:#94a3b8;">
                <div style="font-size:48px;margin-bottom:12px;">👨‍🏫</div>
                <div style="font-size:16px;font-weight:600;color:#475569;">No estás registrado como docente</div>
                <div style="font-size:13px;margin-top:4px;">No se encontró un registro docente vinculado a tu usuario.</div>
            </div>
        @else
            {{-- Selector de grupo y fecha --}}
            <div class="card" style="padding:20px;margin-bottom:20px;">
                <form method="GET" action="{{ route('docentes.asistencia.index') }}">
                    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                        <div style="flex:1;min-width:200px;">
                            <label for="id_grupo">Grupo *</label>
                            <select name="id_grupo" id="id_grupo" required style="margin:0;">
                                <option value="">— Seleccionar grupo —</option>
                                @foreach ($grupos as $g)
                                    <option value="{{ $g->id_grupo }}" {{ ($grupoSeleccionado?->id_grupo ?? request('id_grupo')) == $g->id_grupo ? 'selected' : '' }}>
                                        {{ $g->nombre_grupo }} — {{ $g->materia?->nombre_materia }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1;min-width:140px;">
                            <label for="fecha">Fecha *</label>
                            <input type="date" name="fecha" id="fecha" value="{{ $fecha }}" required style="margin:0;">
                        </div>
                        <button type="submit" class="button">Cargar postulantes</button>
                    </div>
                </form>
            </div>

            {{-- Lista de postulantes --}}
            @if ($postulantes->isNotEmpty())
                <form method="POST" action="{{ route('docentes.asistencia.store') }}">
                    @csrf
                    <input type="hidden" name="id_grupo" value="{{ $grupoSeleccionado?->id_grupo }}">
                    <input type="hidden" name="fecha_clase" value="{{ $fecha }}">

                    <div class="card" style="padding:0;overflow:hidden;">
                        <div style="padding:14px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                            <strong style="font-size:15px;">
                                {{ $grupoSeleccionado?->nombre_grupo }}
                            </strong>
                            <span style="font-size:13px;color:#64748b;margin-left:8px;">
                                — {{ $grupoSeleccionado?->materia?->nombre_materia }}
                                — {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd D \de MMMM \de YYYY') }}
                            </span>
                        </div>
                        <div style="overflow-x:auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th>Postulante</th>
                                        <th>CI</th>
                                        <th style="width:160px;">Asistencia</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($postulantes as $i => $p)
                                        @php
                                            $existing = $asistenciasExistentes->get($p->id_postulante);
                                        @endphp
                                        <tr>
                                            <td style="text-align:center;font-weight:600;color:#64748b;">{{ $i + 1 }}</td>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:8px;">
                                                    <div style="width:30px;height:30px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0;">
                                                        {{ strtoupper(substr($p->usuario?->nombre_usuario ?? '?', 0, 1)) }}
                                                    </div>
                                                    <span style="font-weight:500;">{{ $p->usuario?->nombre_usuario ?? '—' }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $p->usuario?->ci ?? '—' }}</td>
                                            <td>
                                                <select name="asistencia[{{ $p->id_postulante }}][estado]"
                                                        style="margin:0;width:auto;min-width:130px;padding:5px 8px;font-size:13px;
                                                            {{ $existing?->estado_asistencia === 'presente' ? 'border-color:#16a34a;background:#f0fdf4;' : '' }}
                                                            {{ $existing?->estado_asistencia === 'ausente' ? 'border-color:#dc2626;background:#fef2f2;' : '' }}
                                                            {{ $existing?->estado_asistencia === 'justificado' ? 'border-color:#f59e0b;background:#fffbeb;' : '' }}">
                                                    <option value="presente" {{ ($existing?->estado_asistencia ?? 'presente') === 'presente' ? 'selected' : '' }}>✅ Presente</option>
                                                    <option value="ausente" {{ ($existing?->estado_asistencia ?? '') === 'ausente' ? 'selected' : '' }}>❌ Ausente</option>
                                                    <option value="justificado" {{ ($existing?->estado_asistencia ?? '') === 'justificado' ? 'selected' : '' }}>⚠️ Justificado</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="asistencia[{{ $p->id_postulante }}][observacion]"
                                                       value="{{ $existing?->observacion }}"
                                                       placeholder="Opcional" style="margin:0;font-size:13px;padding:5px 8px;">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div style="margin-top:16px;text-align:right;">
                        <button type="submit" class="button">💾 Guardar Asistencia</button>
                    </div>
                </form>
            @elseif (request('id_grupo'))
                <div class="card" style="padding:32px;text-align:center;color:#94a3b8;">
                    <div style="font-size:40px;margin-bottom:8px;">📭</div>
                    <div style="font-size:15px;font-weight:600;color:#475569;">Sin postulantes en este grupo</div>
                    <div style="font-size:13px;">No hay postulantes asignados al grupo {{ $grupoSeleccionado?->nombre_grupo }}.</div>
                </div>
            @else
                <div class="card" style="padding:32px;text-align:center;color:#94a3b8;">
                    <div style="font-size:40px;margin-bottom:8px;">👆</div>
                    <div style="font-size:15px;font-weight:600;color:#475569;">Seleccioná un grupo y fecha</div>
                    <div style="font-size:13px;">Elegí arriba para cargar los postulantes.</div>
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
