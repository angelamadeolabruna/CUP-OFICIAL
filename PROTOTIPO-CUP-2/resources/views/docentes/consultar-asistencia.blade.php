<x-layouts.app title="Consultar Asistencia">
    <div>
        <h1 class="page-title">Consultar Asistencia</h1>
        <p class="page-desc">Historial de asistencia por grupo, fecha y postulante.</p>

        @if (isset($sinRegistro))
            <div class="card" style="padding:40px;text-align:center;color:#94a3b8;">
                <div style="font-size:48px;margin-bottom:12px;">🔍</div>
                <div style="font-size:16px;font-weight:600;color:#475569;">
                    @if (Auth::user()->rol?->nombre_rol === 'postulante_oficial')
                        No se encontró tu registro como postulante.
                    @else
                        No estás registrado como docente.
                    @endif
                </div>
            </div>
        @else
            {{-- Filtros --}}
            <div class="card" style="padding:20px;margin-bottom:20px;">
                <form method="GET" action="{{ route('asistencia.consulta.index') }}">
                    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                        @if (in_array($rol, ['administrador', 'coordinador_academico', 'docente']))
                            <div style="flex:1;min-width:150px;">
                                <label for="id_grupo">Grupo</label>
                                <select name="id_grupo" id="id_grupo" style="margin:0;">
                                    <option value="">Todos los grupos</option>
                                    @foreach ($grupos as $g)
                                        <option value="{{ $g->id_grupo }}" {{ request('id_grupo') == $g->id_grupo ? 'selected' : '' }}>
                                            {{ $g->nombre_grupo }} — {{ $g->materia?->nombre_materia }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div style="flex:1;min-width:130px;">
                            <label for="fecha_desde">Desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}" style="margin:0;">
                        </div>
                        <div style="flex:1;min-width:130px;">
                            <label for="fecha_hasta">Hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}" style="margin:0;">
                        </div>
                        <div style="flex:1;min-width:120px;">
                            <label for="estado_asistencia">Estado</label>
                            <select name="estado_asistencia" id="estado_asistencia" style="margin:0;">
                                <option value="">Todos</option>
                                <option value="presente" {{ request('estado_asistencia') === 'presente' ? 'selected' : '' }}>✅ Presente</option>
                                <option value="ausente" {{ request('estado_asistencia') === 'ausente' ? 'selected' : '' }}>❌ Ausente</option>
                                <option value="justificado" {{ request('estado_asistencia') === 'justificado' ? 'selected' : '' }}>⚠️ Justificado</option>
                            </select>
                        </div>
                        <button type="submit" class="button">🔍 Filtrar</button>
                        @if (request()->anyFilled(['id_grupo', 'fecha_desde', 'fecha_hasta', 'estado_asistencia']))
                            <a href="{{ route('asistencia.consulta.index') }}" class="button button-secondary">Limpiar</a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Resultados --}}
            @if ($asistencias->isEmpty())
                <div class="card" style="padding:40px;text-align:center;color:#94a3b8;">
                    <div style="font-size:40px;margin-bottom:8px;">📭</div>
                    <div style="font-size:15px;font-weight:600;color:#475569;">Sin registros de asistencia</div>
                    <div style="font-size:13px;">No se encontraron registros con los filtros seleccionados.</div>
                </div>
            @else
                <div class="card" style="padding:0;overflow:hidden;">
                    <div style="padding:12px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-size:13px;color:#64748b;">
                        {{ $asistencias->total() }} registro(s) encontrado(s)
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Grupo</th>
                                    <th>Materia</th>
                                    <th>Postulante</th>
                                    @if ($rol !== 'postulante_oficial')<th>Docente</th>@endif
                                    <th>Estado</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($asistencias as $a)
                                    <tr>
                                        <td style="white-space:nowrap;">
                                            {{ \Carbon\Carbon::parse($a->fecha_clase)->locale('es')->isoFormat('DD/MM/YYYY') }}
                                        </td>
                                        <td style="font-weight:600;">{{ $a->grupo?->nombre_grupo }}</td>
                                        <td>
                                            <span style="font-size:12px;color:#4338ca;background:#e0e7ff;padding:2px 6px;border-radius:3px;">
                                                {{ $a->grupo?->materia?->nombre_materia ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $a->postulante?->usuario?->nombre_usuario ?? '—' }}</td>
                                        @if ($rol !== 'postulante_oficial')
                                            <td style="font-size:13px;color:#64748b;">{{ $a->docente?->nombre_completo ?? '—' }}</td>
                                        @endif
                                        <td>
                                            @switch($a->estado_asistencia)
                                                @case('presente')
                                                    <span class="badge badge-aprobado">Presente</span>
                                                    @break
                                                @case('ausente')
                                                    <span class="badge badge-reprobado">Ausente</span>
                                                    @break
                                                @case('justificado')
                                                    <span class="badge" style="background:#fef3c7;color:#92400e;">Justificado</span>
                                                    @break
                                                @default
                                                    <span>{{ $a->estado_asistencia }}</span>
                                            @endswitch
                                        </td>
                                        <td style="font-size:13px;color:#64748b;max-width:200px;">{{ $a->observacion ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="margin-top:16px;">
                    {{ $asistencias->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
