<x-layouts.app title="Registrar Docente">
    <div>
        <h1 class="page-title">Registrar Docente</h1>
        <p class="page-desc">Ingresá los datos del docente para registrarlo en el sistema. Luego podrás adjuntar sus requisitos académicos.</p>

        <div class="card" style="padding:24px;max-width:640px;">
            <form method="POST" action="{{ route('docentes.store') }}">
                @csrf

                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:150px;">
                        <label for="nombres">Nombres *</label>
                        <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" required>
                    </div>
                    <div style="flex:1;min-width:150px;">
                        <label for="apellidos">Apellidos *</label>
                        <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" required>
                    </div>
                </div>

                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:120px;">
                        <label for="ci">CI *</label>
                        <input type="text" name="ci" id="ci" value="{{ old('ci') }}" required placeholder="1234567">
                    </div>
                    <div style="flex:2;min-width:200px;">
                        <label for="profesion">Profesión *</label>
                        <input type="text" name="profesion" id="profesion" value="{{ old('profesion') }}" required placeholder="Ej: Lic. en Informática">
                    </div>
                </div>

                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <div style="flex:2;min-width:200px;">
                        <label for="correo">Correo electrónico *</label>
                        <input type="email" name="correo" id="correo" value="{{ old('correo') }}" required>
                    </div>
                    <div style="flex:1;min-width:100px;">
                        <label for="telefono">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}">
                    </div>
                </div>

                <hr style="border:none;border-top:1px solid #e2e8f0;margin:18px 0;">

                {{-- Crear usuario --}}
                <label style="display:flex;align-items:center;gap:8px;font-weight:500;cursor:pointer;margin-bottom:14px;">
                    <input type="checkbox" name="crear_usuario" value="1"
                           style="width:auto;margin:0;accent-color:#2563eb;"
                           onchange="document.getElementById('credenciales').style.display=this.checked?'block':'none'"
                           {{ old('crear_usuario') ? 'checked' : '' }}>
                    Crear usuario para que pueda iniciar sesión
                </label>

                <div id="credenciales" style="{{ old('crear_usuario') ? '' : 'display:none;' }}padding:12px 16px;background:#f8fafc;border-radius:8px;margin-bottom:14px;">
                    <div style="display:flex;gap:12px;flex-wrap:wrap;">
                        <div style="flex:1;min-width:150px;">
                            <label for="nombre_usuario">Nombre de usuario *</label>
                            <input type="text" name="nombre_usuario" id="nombre_usuario" value="{{ old('nombre_usuario') }}"
                                   placeholder="Ej: jperez">
                        </div>
                        <div style="flex:1;min-width:150px;">
                            <label for="password">Contraseña *</label>
                            <input type="password" name="password" id="password" placeholder="Mín. 6 caracteres">
                        </div>
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px;">El docente podrá iniciar sesión con estas credenciales (rol: docente).</div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;">
                    <a href="{{ route('docentes.index') }}" class="button button-secondary">Cancelar</a>
                    <button type="submit" class="button">Guardar Docente</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
