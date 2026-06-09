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

                <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:10px;">
                    <a href="{{ route('admin.usuarios.index') }}" class="button button-secondary">Cancelar</a>
                    <button type="submit">Crear Cuenta y Notificar</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
