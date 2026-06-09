<x-layouts.app title="Verificar Pagos y Postulantes">
    <div style="max-width:1100px;">
        <div style="margin-bottom:20px;">
            <h1 class="page-title">Verificar y Confirmar Pagos</h1>
            <p class="page-desc" style="margin-bottom:0;">Estado de todos los prepostulantes: pagos, datos de registro y conversión a postulante.</p>
        </div>

        @if (session('status'))
            <div class="success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filtros --}}
        <div class="card" style="padding:16px 20px;margin-bottom:20px;">
            <form method="GET" action="{{ route('admin.pagos.index') }}" style="display:flex;gap:14px;align-items:flex-end;flex-wrap:wrap;">
                <div style="flex:2;min-width:200px;">
                    <label for="busqueda" style="margin-top:0;">Buscar</label>
                    <input type="text" name="busqueda" id="busqueda" placeholder="Nombre, CI o correo..." value="{{ request('busqueda') }}" style="margin-bottom:0;">
                </div>
                <div style="min-width:180px;">
                    <label for="estado_pago" style="margin-top:0;">Estado del pago</label>
                    <select name="estado_pago" id="estado_pago" style="margin-bottom:0;">
                        <option value="">Todos</option>
                        <option value="sin_pago" {{ request('estado_pago') === 'sin_pago' ? 'selected' : '' }}>Sin pago</option>
                        <option value="pendiente" {{ request('estado_pago') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="confirmado" {{ request('estado_pago') === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                        <option value="rechazado" {{ request('estado_pago') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                </div>
                <div>
                    <button type="submit">Filtrar</button>
                </div>
            </form>
        </div>

        {{-- Tabla única --}}
        <div class="card" style="padding:0;overflow:hidden;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Prepostulante</th>
                        <th>CI</th>
                        <th>Datos Registro</th>
                        <th>Pago</th>
                        <th>Método</th>
                        <th>Comprobante</th>
                        <th>Estado Proceso</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prepostulantes as $pre)
                        @php
                            $ultimoPago = $pre->pagos->first();
                            $tieneRegistro = $pre->datosRegistroTemporal ? true : false;
                            $pagoConfirmado = $ultimoPago && $ultimoPago->estado_pago === 'confirmado';
                            $pagoPendiente = $ultimoPago && $ultimoPago->estado_pago === 'pendiente';
                            $pagoRechazado = $ultimoPago && $ultimoPago->estado_pago === 'rechazado';
                            $sinPago = !$ultimoPago;
                        @endphp
                        <tr>
                            <td>
                                <strong style="font-size:13px;">{{ $pre->nombres }} {{ $pre->apellidos }}</strong>
                                <div style="font-size:11px;color:#94a3b8;">{{ $pre->correo }}</div>
                            </td>
                            <td>{{ $pre->ci }}</td>
                            <td>
                                @if ($tieneRegistro)
                                    <span style="color:#059669;font-weight:600;">✅ Completo</span>
                                @else
                                    <span style="color:#f59e0b;font-weight:600;">⏳ Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if ($sinPago)
                                    <span style="color:#94a3b8;font-weight:500;">Sin pago</span>
                                @elseif ($pagoPendiente)
                                    <span style="color:#f59e0b;font-weight:600;">Pendiente</span>
                                    <div style="font-size:11px;color:#94a3b8;">{{ $ultimoPago->codigo_pago }}</div>
                                @elseif ($pagoConfirmado)
                                    <span style="color:#059669;font-weight:600;">✅ Confirmado</span>
                                    <div style="font-size:11px;color:#94a3b8;">{{ $ultimoPago->codigo_pago }}</div>
                                @elseif ($pagoRechazado)
                                    <span style="color:#dc2626;font-weight:600;">❌ Rechazado</span>
                                    <div style="font-size:11px;color:#94a3b8;">{{ $ultimoPago->codigo_pago }}</div>
                                @endif
                            </td>
                            <td style="font-size:12px;">{{ $ultimoPago?->metodo_pago ?? '—' }}</td>
                            <td>
                                @if ($ultimoPago?->comprobante_url)
                                    <a href="{{ asset('storage/' . $ultimoPago->comprobante_url) }}" target="_blank" class="button button-ghost button-sm">Ver</a>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $estados = [
                                        'prepostulado' => ['color' => '#f59e0b', 'label' => 'Prepostulado'],
                                        'pago_confirmado' => ['color' => '#059669', 'label' => 'Pago conf.'],
                                        'postulante_completo' => ['color' => '#1e40af', 'label' => 'Postulante'],
                                    ];
                                    $est = $estados[$pre->estado_proceso] ?? ['color' => '#64748b', 'label' => $pre->estado_proceso];
                                @endphp
                                <span style="color:{{ $est['color'] }};font-weight:600;font-size:12px;">{{ $est['label'] }}</span>
                            </td>
                            <td style="text-align:right;">
                                @if ($sinPago || $pagoRechazado)
                                    <span style="color:#94a3b8;font-size:11px;">Esperando pago</span>

                                @elseif ($pagoPendiente)
                                    <div class="flex" style="justify-content:flex-end;gap:6px;">
                                        <form method="POST" action="{{ route('admin.pagos.confirmar', $ultimoPago->id_pago) }}" style="margin:0;">
                                            @csrf
                                            <button type="submit" class="button button-sm" style="background:#059669;">✅ Confirmar</button>
                                        </form>
                                        <button type="button" class="button button-sm button-secondary" style="color:#dc2626;" onclick="mostrarMotivo({{ $ultimoPago->id_pago }})">❌ Rechazar</button>
                                        <form id="rechazar-{{ $ultimoPago->id_pago }}" method="POST" action="{{ route('admin.pagos.rechazar', $ultimoPago->id_pago) }}" style="margin:0;display:none;">
                                            @csrf
                                            <input type="text" name="motivo_rechazo" placeholder="Motivo (opcional)" style="width:180px;font-size:12px;padding:6px 10px;">
                                            <button type="submit" class="button button-sm" style="background:#dc2626;">Rechazar</button>
                                        </form>
                                    </div>

                                @elseif ($pagoConfirmado && $tieneRegistro && $pre->estado_proceso !== 'postulante_completo')
                                    <form method="POST" action="{{ route('admin.pagos.confirmar-postulante', $pre->id_prepostulante) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="button button-sm" style="background:#1e40af;">✅ Confirmar Postulante</button>
                                    </form>

                                @elseif ($pagoConfirmado && !$tieneRegistro)
                                    <span style="color:#94a3b8;font-size:11px;">Sin datos de registro</span>

                                @elseif ($pre->estado_proceso === 'postulante_completo')
                                    <span style="color:#1e40af;font-weight:600;font-size:12px;">✅ Postulante</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;color:#64748b;padding:40px;">
                                No se encontraron prepostulantes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">{{ $prepostulantes->links('pagination::bootstrap-5') }}</div>
    </div>

    <script>
        function mostrarMotivo(id) {
            const form = document.getElementById('rechazar-' + id);
            form.style.display = form.style.display === 'none' ? 'flex' : 'none';
            form.style.gap = '6px';
            form.style.alignItems = 'center';
        }
    </script>
</x-layouts.app>
