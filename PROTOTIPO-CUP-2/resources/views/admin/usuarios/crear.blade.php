<x-layouts.app title="Nueva Cuenta">
    <div style="max-width:640px;">
        <div style="margin-bottom:18px;">
            <a href="{{ route('admin.usuarios.index') }}" class="button button-ghost button-sm">← Volver al listado</a>
        </div>

        <div class="card">
            <h1 class="page-title">Registrar Nueva Cuenta</h1>
            <p class="page-desc">Crea una cuenta individual. Se enviará un correo automático de bienvenida con las credenciales.</p>

            <form method="POST" action="{{ route('admin.usuarios.store') }}">
                @csrf

                <div>
                    <label for="nombre_usuario">Nombre Completo *</label>
                    <input type="text" name="nombre_usuario" id="nombre_usuario" value="{{ old('nombre_usuario') }}" required placeholder="Ej: Dr. Carlos Roca">
                    @error('nombre_usuario') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="email">Correo Electrónico *</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="carlos@example.com">
                        @error('email') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="ci">Cédula de Identidad</label>
                        <input type="text" name="ci" id="ci" value="{{ old('ci') }}" placeholder="Ej: 5432100">
                        @error('ci') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="id_rol">Rol asignado *</label>
                        <select name="id_rol" id="id_rol" required>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                        @error('id_rol') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="estado">Estado Inicial *</label>
                        <select name="estado" id="estado" required>
                            <option value="activo" {{ old('estado', 'activo') === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('estado') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="password">Contraseña *</label>
                        <input type="password" name="password" id="password" required placeholder="Mínimo 8 caracteres">
                        @error('password') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation">Confirmar Contraseña *</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required>
                    </div>
                </div>

                <div id="campos-prepostulante" style="display:none;margin-top:20px;border-top:2px solid #e2e8f0;padding-top:16px;">
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:12px;color:#1f2937;">Datos del Prepostulante</h3>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div>
                            <label for="nombres">Nombres</label>
                            <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" placeholder="Nombres del prepostulante">
                            @error('nombres') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="apellidos">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" placeholder="Apellidos del prepostulante">
                            @error('apellidos') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:10px;">
                        <div>
                            <label for="carrera_primera_opcion">Primera Opción *</label>
                            <select name="carrera_primera_opcion" id="carrera_primera_opcion">
                                <option value="">— Seleccionar —</option>
                                @foreach ($carreras as $c)
                                    <option value="{{ $c->id_carrera }}" {{ old('carrera_primera_opcion') == $c->id_carrera ? 'selected' : '' }}>
                                        {{ $c->codigo_carrera }} — {{ $c->nombre_carrera }}
                                    </option>
                                @endforeach
                            </select>
                            @error('carrera_primera_opcion') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="carrera_segunda_opcion">Segunda Opción</label>
                            <select name="carrera_segunda_opcion" id="carrera_segunda_opcion">
                                <option value="">— Seleccionar —</option>
                                @foreach ($carreras as $c)
                                    <option value="{{ $c->id_carrera }}" {{ old('carrera_segunda_opcion') == $c->id_carrera ? 'selected' : '' }}>
                                        {{ $c->codigo_carrera }} — {{ $c->nombre_carrera }}
                                    </option>
                                @endforeach
                            </select>
                            @error('carrera_segunda_opcion') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:10px;">
                        <div>
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}">
                            @error('fecha_nacimiento') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="sexo">Sexo</label>
                            <select name="sexo" id="sexo">
                                <option value="">— Seleccionar —</option>
                                <option value="M" {{ old('sexo') === 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo') === 'F' ? 'selected' : '' }}>Femenino</option>
                                <option value="Otro" {{ old('sexo') === 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('sexo') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:10px;">
                        <div>
                            <label for="telefono">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" placeholder="Teléfono/Celular">
                            @error('telefono') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="direccion">Dirección</label>
                            <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" placeholder="Dirección domiciliaria">
                            @error('direccion') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:10px;">
                        <div>
                            <label for="colegio_procedencia">Colegio de Procedencia</label>
                            <input type="text" name="colegio_procedencia" id="colegio_procedencia" value="{{ old('colegio_procedencia') }}" placeholder="Colegio de egreso">
                            @error('colegio_procedencia') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="ciudad">Ciudad</label>
                            <input type="text" name="ciudad" id="ciudad" value="{{ old('ciudad') }}" placeholder="Ciudad de residencia">
                            @error('ciudad') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="margin-top:10px;">
                        <label for="titulo_bachiller" style="display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" name="titulo_bachiller" id="titulo_bachiller" value="1" {{ old('titulo_bachiller') ? 'checked' : '' }}>
                            Título de Bachiller
                        </label>
                        @error('titulo_bachiller') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:10px;">
                    <a href="{{ route('admin.usuarios.index') }}" class="button button-secondary">Cancelar</a>
                    <button type="submit">Crear Cuenta y Notificar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const rolSelect = document.getElementById('id_rol');
            const camposPre = document.getElementById('campos-prepostulante');

            function toggleCamposPre() {
                const selectedText = rolSelect.options[rolSelect.selectedIndex]?.text || '';
                if (selectedText.toLowerCase().includes('prepostulante')) {
                    camposPre.style.display = 'block';
                } else {
                    camposPre.style.display = 'none';
                }
            }

            rolSelect.addEventListener('change', toggleCamposPre);
            toggleCamposPre();
        })();
    </script>
</x-layouts.app>
