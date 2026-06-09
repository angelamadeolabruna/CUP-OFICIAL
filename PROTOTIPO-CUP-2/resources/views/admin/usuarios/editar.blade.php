<x-layouts.app title="Editar Cuenta">
    <div style="max-width:640px;">
        <div style="margin-bottom:18px;">
            <a href="{{ route('admin.usuarios.index') }}" class="button button-ghost button-sm">← Volver al listado</a>
        </div>

        <div class="card">
            <h1 class="page-title">Editar Cuenta</h1>
            <p class="page-desc">Modificá los datos de <strong>{{ $usuario->nombre_usuario }}</strong>. Dejá vacía la contraseña si no querés cambiarla.</p>

            <form method="POST" action="{{ route('admin.usuarios.update', $usuario->id_usuario) }}">
                @csrf

                <div>
                    <label for="nombre_usuario">Nombre Completo *</label>
                    <input type="text" name="nombre_usuario" id="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" required>
                    @error('nombre_usuario') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="email">Correo Electrónico *</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" required>
                        @error('email') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="ci">Cédula de Identidad</label>
                        <input type="text" name="ci" id="ci" value="{{ old('ci', $usuario->ci) }}">
                        @error('ci') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="id_rol">Rol asignado *</label>
                        <select name="id_rol" id="id_rol" required>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id_rol }}" {{ old('id_rol', $usuario->id_rol) == $rol->id_rol ? 'selected' : '' }}>{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                        @error('id_rol') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="estado">Estado *</label>
                        <select name="estado" id="estado" required>
                            <option value="activo" {{ old('estado', $usuario->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('estado', $usuario->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('estado') <span class="error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <label for="password">Nueva Contraseña <span style="font-weight:400;color:#94a3b8;">(opcional)</span></label>
                        <input type="password" name="password" id="password" placeholder="Dejá vacío para no cambiar">
                        @error('password') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation">
                    </div>
                </div>

                <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:10px;">
                    <a href="{{ route('admin.usuarios.index') }}" class="button button-secondary">Cancelar</a>
                    <button type="submit">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
