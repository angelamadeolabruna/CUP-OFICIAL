<x-layouts.app title="Bitácora de Auditoría">
    <div style="max-width:1400px;">
        <div style="margin-bottom:16px;">
            <h1 class="page-title">Bitácora de Auditoría</h1>
            <p class="page-desc">Eventos registrados en el sistema: ingresos, cambios, registros y publicaciones.</p>
        </div>

        {{-- Resumen --}}
        <div class="grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));">
            <div class="metric">
                <div class="label">Total Eventos</div>
                <strong>{{ $conteo['total'] }}</strong>
            </div>
            <div class="metric" style="border-left:4px solid #2563eb;">
                <div class="label">Eventos Hoy</div>
                <strong style="color:#2563eb;">{{ $conteo['hoy'] }}</strong>
            </div>
            <div class="metric" style="border-left:4px solid #8b5cf6;">
                <div class="label">Acciones Distintas</div>
                <strong style="color:#8b5cf6;">{{ $conteo['acciones_distintas'] }}</strong>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card" style="margin-bottom:20px;padding:16px 20px;">
            <form method="GET" class="flex" style="flex-wrap:wrap;gap:12px;align-items:flex-end;">
                <div style="width:180px;">
                    <label style="margin-bottom:3px;">Usuario</label>
                    <select name="id_usuario" style="margin-bottom:0;">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $u)
                            <option value="{{ $u->id_usuario }}" {{ request('id_usuario') == $u->id_usuario ? 'selected' : '' }}>{{ $u->nombre_usuario }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width:150px;">
                    <label style="margin-bottom:3px;">Acción</label>
                    <select name="accion" style="margin-bottom:0;">
                        <option value="">Todas</option>
                        @foreach ($acciones as $a)
                            <option value="{{ $a }}" {{ request('accion') === $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width:180px;">
                    <label style="margin-bottom:3px;">Tabla</label>
                    <select name="tabla" style="margin-bottom:0;">
                        <option value="">Todas</option>
                        @foreach ($tablas as $t)
                            <option value="{{ $t }}" {{ request('tabla') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width:160px;">
                    <label style="margin-bottom:3px;">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}" style="margin-bottom:0;">
                </div>
                <div style="width:160px;">
                    <label style="margin-bottom:3px;">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta') }}" style="margin-bottom:0;">
                </div>
                <button type="submit" class="button" style="align-self:flex-end;">🔍 Filtrar</button>
                @if (request()->anyFilled(['id_usuario', 'accion', 'tabla', 'desde', 'hasta']))
                    <a href="{{ route('admin.bitacora.index') }}" class="button button-secondary button-sm" style="align-self:flex-end;">Limpiar</a>
                @endif
            </form>
        </div>

        {{-- Tabla --}}
        <div class="card" style="padding:0;overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="table" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="width:150px;">Fecha / Hora</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Tabla</th>
                            <th>ID Registro</th>
                            <th>Detalle</th>
                            <th style="width:130px;">IP Origen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($eventos as $e)
                            <tr>
                                <td>{{ $e->id_bitacora }}</td>
                                <td style="white-space:nowrap;">
                                    <div>{{ $e->created_at?->format('d/m/Y') }}</div>
                                    <div style="font-size:11px;color:#64748b;">{{ $e->created_at?->format('H:i:s') }}</div>
                                </td>
                                <td><strong>{{ $e->usuario?->nombre_usuario ?? '—' }}</strong></td>
                                <td>
                                    @php
                                        $badgeColor = match ($e->accion) {
                                            'INSERT' => '#059669',
                                            'UPDATE' => '#f59e0b',
                                            'DELETE' => '#dc2626',
                                            'LOGIN' => '#2563eb',
                                            'LOGOUT' => '#64748b',
                                            'EXPORT' => '#8b5cf6',
                                            default => '#475569',
                                        };
                                    @endphp
                                    <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-weight:600;font-size:11px;color:#fff;background:{{ $badgeColor }};">
                                        {{ $e->accion }}
                                    </span>
                                </td>
                                <td style="color:#64748b;">{{ $e->tabla_afectada ?? '—' }}</td>
                                <td style="text-align:center;">{{ $e->id_registro ?? '—' }}</td>
                                <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $e->detalle }}">
                                    {{ $e->detalle ?? '—' }}
                                </td>
                                <td style="font-family:monospace;font-size:11px;">{{ $e->ip_origen ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align:center;padding:32px;color:#64748b;">No hay eventos registrados para los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-top:16px;">{{ $eventos->links() }}</div>
    </div>
</x-layouts.app>
