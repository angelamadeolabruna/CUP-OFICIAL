<x-layouts.app title="Registrar Aulas">
    <div>
        <h1 class="page-title">Registrar Aulas</h1>
        <p class="page-desc">Gestioná las aulas disponibles para el curso preuniversitario.</p>

        <div style="display:flex;gap:20px;flex-wrap:wrap-reverse;">
            {{-- Formulario --}}
            <div style="flex:2;min-width:320px;">
                <div class="card" style="padding:20px;">
                    <strong style="font-size:15px;">Nueva Aula</strong>
                    <form method="POST" action="{{ route('logistica.aulas.store') }}" style="margin-top:12px;">
                        @csrf
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <div style="flex:1;min-width:100px;">
                                <label for="codigo_aula">Código / Número *</label>
                                <input type="text" name="codigo_aula" id="codigo_aula"
                                       value="{{ old('codigo_aula') }}" required
                                       placeholder="Ej: A-101">
                            </div>
                            <div style="flex:1;min-width:100px;">
                                <label for="capacidad">Capacidad *</label>
                                <input type="number" name="capacidad" id="capacidad"
                                       value="{{ old('capacidad') }}" min="1" max="500" required>
                            </div>
                        </div>
                        <label for="ubicacion">Ubicación</label>
                        <input type="text" name="ubicacion" id="ubicacion"
                               value="{{ old('ubicacion') }}" placeholder="Ej: Edificio A, Piso 1">
                        <div style="display:flex;justify-content:flex-end;">
                            <button type="submit" class="button">+ Registrar Aula</button>
                        </div>
                    </form>
                </div>

                {{-- Tabla de aulas --}}
                <div class="card" style="padding:0;overflow:hidden;margin-top:16px;">
                    @if ($aulas->isEmpty())
                        <div style="padding:24px;text-align:center;color:#94a3b8;">No hay aulas registradas.</div>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Ubicación</th>
                                    <th>Capacidad</th>
                                    <th>Estado</th>
                                    <th style="width:140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($aulas as $aula)
                                    <tr>
                                        <td style="font-weight:600;">{{ $aula->codigo_aula }}</td>
                                        <td>{{ $aula->ubicacion ?? '—' }}</td>
                                        <td>{{ $aula->capacidad }}</td>
                                        <td>
                                            <span class="badge {{ $aula->estado_activo ? 'badge-activo' : 'badge-inactivo' }}">
                                                {{ $aula->estado_activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display:flex;gap:4px;">
                                                <button class="button button-sm button-secondary"
                                                        onclick="editarAula({{ $aula->id_aula }}, '{{ $aula->codigo_aula }}', '{{ $aula->ubicacion ?? '' }}', {{ $aula->capacidad }}, {{ $aula->estado_activo ? 'true' : 'false' }})">
                                                    ✏️
                                                </button>
                                                <form method="POST" action="{{ route('logistica.aulas.destroy', $aula->id_aula) }}"
                                                      onsubmit="return confirm('¿Eliminar aula {{ $aula->codigo_aula }}?')" style="display:inline;">
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

    {{-- Modal Editar Aula --}}
    <dialog id="modalEditar" style="border:1px solid #e2e8f0;border-radius:12px;padding:24px;max-width:420px;width:90%;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <strong style="font-size:16px;">Editar Aula</strong>
            <button onclick="document.getElementById('modalEditar').close()"
                    style="background:none;border:none;font-size:22px;cursor:pointer;color:#64748b;">✕</button>
        </div>
        <form method="POST" action="" id="formEditarAula">
            @csrf
                            <label for="edit_codigo_aula">Código / Número *</label>
            <input type="text" name="codigo_aula" id="edit_codigo_aula" required>

            <label for="edit_capacidad">Capacidad *</label>
            <input type="number" name="capacidad" id="edit_capacidad" min="1" max="500" required>

            <label for="edit_ubicacion">Ubicación</label>
            <input type="text" name="ubicacion" id="edit_ubicacion">

            <label style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                <input type="checkbox" name="estado_activo" id="edit_estado_activo" value="1" style="width:auto;margin:0;">
                Aula activa
            </label>

            <div style="display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" class="button button-secondary"
                        onclick="document.getElementById('modalEditar').close()">Cancelar</button>
                <button type="submit" class="button">Guardar</button>
            </div>
        </form>
    </dialog>

    <script>
        function editarAula(id, codigo, ubicacion, capacidad, activo) {
            const form = document.getElementById('formEditarAula');
            form.action = '{{ route('logistica.aulas.update', '__ID__') }}'.replace('__ID__', id);
            document.getElementById('edit_codigo_aula').value = codigo;
            document.getElementById('edit_capacidad').value = capacidad;
            document.getElementById('edit_ubicacion').value = ubicacion;
            document.getElementById('edit_estado_activo').checked = activo;
            document.getElementById('modalEditar').showModal();
        }
    </script>
</x-layouts.app>
