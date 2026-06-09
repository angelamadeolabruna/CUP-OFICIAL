<x-layouts.app title="Registrar Horarios">
    <div>
        <h1 class="page-title">Registrar Horarios</h1>
        <p class="page-desc">Gestioná los horarios disponibles para asignar a los grupos.</p>

        <div style="display:flex;gap:20px;flex-wrap:wrap-reverse;">
            {{-- Formulario --}}
            <div style="flex:2;min-width:320px;">
                <div class="card" style="padding:20px;">
                    <strong style="font-size:15px;">Nuevo Horario</strong>
                    <form method="POST" action="{{ route('logistica.horarios.store') }}" style="margin-top:12px;">
                        @csrf
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <div style="flex:2;min-width:200px;">
                                <label>Días *</label>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
                                    @foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $dia)
                                        <label style="display:flex;align-items:center;gap:3px;font-weight:400;font-size:13px;cursor:pointer;">
                                            <input type="checkbox" name="dias[]" value="{{ $dia }}"
                                                   style="width:auto;margin:0;accent-color:#2563eb;">
                                            {{ $dia }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div style="flex:1;min-width:100px;">
                                <label for="hora_inicio">Hora inicio *</label>
                                <input type="time" name="hora_inicio" id="hora_inicio"
                                       value="{{ old('hora_inicio', '08:00') }}" required>
                            </div>
                            <div style="flex:1;min-width:100px;">
                                <label for="hora_fin">Hora fin *</label>
                                <input type="time" name="hora_fin" id="hora_fin"
                                       value="{{ old('hora_fin', '10:00') }}" required>
                            </div>
                            <div style="flex:1;min-width:100px;">
                                <label for="turno">Turno</label>
                                <select name="turno" id="turno">
                                    <option value="">—</option>
                                    <option value="Mañana" {{ old('turno') == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                                    <option value="Tarde" {{ old('turno') == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                                    <option value="Noche" {{ old('turno') == 'Noche' ? 'selected' : '' }}>Noche</option>
                                </select>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="button">+ Registrar Horario</button>
                        </div>
                    </form>
                </div>

                {{-- Tabla de horarios --}}
                <div class="card" style="padding:0;overflow:hidden;margin-top:16px;">
                    @if ($horarios->isEmpty())
                        <div style="padding:24px;text-align:center;color:#94a3b8;">No hay horarios registrados.</div>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Día</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Turno</th>
                                    <th style="width:120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($horarios as $horario)
                                    <tr>
                                        <td style="font-weight:600;">{{ $horario->dia_semana }}</td>
                                        <td>{{ substr($horario->hora_inicio, 0, 5) }}</td>
                                        <td>{{ substr($horario->hora_fin, 0, 5) }}</td>
                                        <td>{{ $horario->turno ?? '—' }}</td>
                                        <td>
                                            <div style="display:flex;gap:4px;">
                                                <button class="button button-sm button-secondary"
                                                        onclick="editarHorario({{ $horario->id_horario }}, '{{ $horario->dia_semana }}', '{{ substr($horario->hora_inicio, 0, 5) }}', '{{ substr($horario->hora_fin, 0, 5) }}', '{{ $horario->turno ?? '' }}')">
                                                    ✏️
                                                </button>
                                                <form method="POST" action="{{ route('logistica.horarios.destroy', $horario->id_horario) }}"
                                                      onsubmit="return confirm('¿Eliminar este horario?')" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="button button-sm button-danger">🗑️</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Editar Horario --}}
    <dialog id="modalEditar" style="border:1px solid #e2e8f0;border-radius:12px;padding:24px;max-width:420px;width:90%;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <strong style="font-size:16px;">Editar Horario</strong>
            <button onclick="document.getElementById('modalEditar').close()"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b;">✕</button>
        </div>
        <form method="POST" action="" id="formEditarHorario">
            @csrf
            <label for="edit_dia_semana">Día *</label>
            <select name="dia_semana" id="edit_dia_semana" required>
                @foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $dia)
                    <option value="{{ $dia }}">{{ $dia }}</option>
                @endforeach
            </select>

            <div style="display:flex;gap:10px;">
                <div style="flex:1;">
                    <label for="edit_hora_inicio">Hora inicio *</label>
                    <input type="time" name="hora_inicio" id="edit_hora_inicio" required>
                </div>
                <div style="flex:1;">
                    <label for="edit_hora_fin">Hora fin *</label>
                    <input type="time" name="hora_fin" id="edit_hora_fin" required>
                </div>
            </div>

            <label for="edit_turno">Turno</label>
            <select name="turno" id="edit_turno">
                <option value="">—</option>
                <option value="Mañana">Mañana</option>
                <option value="Tarde">Tarde</option>
                <option value="Noche">Noche</option>
            </select>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
                <button type="button" class="button button-secondary"
                        onclick="document.getElementById('modalEditar').close()">Cancelar</button>
                <button type="submit" class="button">Guardar</button>
            </div>
        </form>
    </dialog>

    <script>
        function editarHorario(id, dia, inicio, fin, turno) {
            const form = document.getElementById('formEditarHorario');
            form.action = '{{ route('logistica.horarios.update', '__ID__') }}'.replace('__ID__', id);
            document.getElementById('edit_dia_semana').value = dia;
            document.getElementById('edit_hora_inicio').value = inicio;
            document.getElementById('edit_hora_fin').value = fin;
            document.getElementById('edit_turno').value = turno;
            document.getElementById('modalEditar').showModal();
        }
    </script>
</x-layouts.app>
