<x-layouts.app title="Gestionar Cuentas">
    <div class="flex-between" style="margin-bottom:20px;">
        <div>
            <h1 class="page-title">Gestionar Cuentas y Roles</h1>
            <p class="page-desc" style="margin-bottom:0;">Creación, edición y suspensión de usuarios del sistema CUP.</p>
        </div>
        <div class="flex">
            <a href="{{ route('admin.usuarios.importar') }}" class="button button-secondary button-sm">📥 Importar</a>
            <a href="{{ route('admin.usuarios.create') }}" class="button button-sm">+ Nueva Cuenta</a>
        </div>
    </div>

    @if (session('dev_credenciales'))
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:16px;margin-bottom:18px;font-size:13px;">
            <strong style="color:#1e40af;">🔑 Credenciales generadas:</strong>
            @if (is_array(session('dev_credenciales')) && isset(session('dev_credenciales')[0]))
                @foreach (session('dev_credenciales') as $cred)
                    <div style="margin-top:8px;display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:center;padding:6px 0;border-bottom:1px solid #e2e8f0;font-size:13px;">
                        <span>{{ $cred['nombre'] }}</span>
                        <span style="color:#475569;">{{ $cred['email'] }}</span>
                        <code style="background:#e2e8f0;padding:2px 8px;border-radius:4px;color:#1e40af;font-size:13px;">{{ $cred['password'] }}</code>
                    </div>
                @endforeach
            @else
                <div style="margin-top:8px;display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:center;font-size:13px;">
                    <span>{{ session('dev_credenciales')['nombre'] }}</span>
                    <span style="color:#475569;">{{ session('dev_credenciales')['email'] }}</span>
                    <code style="background:#e2e8f0;padding:2px 8px;border-radius:4px;color:#1e40af;font-size:13px;">{{ session('dev_credenciales')['password'] }}</code>
                </div>
            @endif
        </div>
    @endif

    <div class="card" style="padding:20px 24px;margin-bottom:20px;">
        <form method="GET" action="{{ route('admin.usuarios.index') }}" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;">
            <div style="flex:2;min-width:200px;">
                <label for="busqueda" style="margin-top:0;">Buscar usuario</label>
                <input type="text" name="busqueda" id="busqueda" placeholder="Nombre, email, CI o rol..." value="{{ request('busqueda') }}" style="margin-bottom:0;">
            </div>
            <div style="flex:1;min-width:150px;">
                <label for="id_rol" style="margin-top:0;">Rol</label>
                <select name="id_rol" id="id_rol" style="margin-bottom:0;">
                    <option value="">Todos</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol->id_rol }}" {{ request('id_rol') == $rol->id_rol ? 'selected' : '' }}>{{ $rol->nombre_rol }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:150px;">
                <label for="estado" style="margin-top:0;">Estado</label>
                <select name="estado" id="estado" style="margin-bottom:0;">
                    <option value="">Todos</option>
                    <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div>
                <button type="submit">Buscar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <table class="table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>CI</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usr)
                    <tr>
                        <td>
                            <strong style="font-size:14px;">{{ $usr->nombre_usuario }}</strong>
                            <div style="font-size:11px;color:#94a3b8;">Creado {{ $usr->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td>{{ $usr->ci ?? '—' }}</td>
                        <td>{{ $usr->email }}</td>
                        <td>
                            <span style="font-weight:600;color:#1e40af;font-size:13px;">{{ $usr->rol?->nombre_rol ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $usr->estado }}">{{ $usr->estado }}</span>
                        </td>
                        <td style="text-align:right;">
                            <div class="flex" style="justify-content:flex-end;">
                                <a href="{{ route('admin.usuarios.edit', $usr->id_usuario) }}" class="button button-ghost button-sm">✏️ Editar</a>
                                @if($usr->id_usuario !== auth()->id())
                                    <form method="POST" action="{{ route('admin.usuarios.toggle', $usr->id_usuario) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="button button-ghost button-sm" style="color:{{ $usr->estado === 'activo' ? '#dc2626' : '#059669' }};">
                                            {{ $usr->estado === 'activo' ? '🚫 Desactivar' : '✅ Activar' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $usr->id_usuario) }}" style="margin:0;" onsubmit="return confirm('¿Eliminar cuenta de {{ $usr->nombre_usuario }}? Esta acción no se puede deshacer.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="button button-ghost button-sm" style="color:#dc2626;">🗑️ Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;color:#64748b;padding:40px;">
                            No se encontraron usuarios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">
        {{ $usuarios->links() }}
    </div>
</x-layouts.app>
