<x-layouts.app title="Pago de Inscripción">
    <div style="max-width:800px;">
        <h1 class="page-title">Pago de Inscripción</h1>
        <p class="page-desc">Registrá el pago de tu inscripción al CUP FICCT - UAGRM.</p>

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

        {{-- Resumen --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div class="card" style="padding:20px;">
                <div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Monto de inscripción</div>
                <div style="font-size:28px;font-weight:700;color:#0f172a;margin-top:4px;">Bs. 120.00</div>
            </div>
            <div class="card" style="padding:20px;">
                <div style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Total pagado</div>
                <div style="font-size:28px;font-weight:700;color:{{ $totalPagado > 0 ? '#059669' : '#64748b' }};margin-top:4px;">
                    Bs. {{ number_format($totalPagado, 2) }}
                </div>
            </div>
        </div>

        {{-- Formulario de pago --}}
        <div class="card" style="padding:24px;margin-bottom:24px;">
            <h3 style="font-size:15px;margin:0 0 16px;">Registrar nuevo pago</h3>
            <form method="POST" action="{{ route('prepostulante.pagos.store') }}" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom:16px;">
                    <label for="metodo_pago">Método de pago</label>
                    <select name="metodo_pago" id="metodo_pago" required>
                        <option value="">Seleccioná un método</option>
                        <option value="paypal">💳 PayPal</option>
                        <option value="transferencia">Transferencia bancaria</option>
                        <option value="deposito">Depósito bancario</option>
                        <option value="tigo_money">Tigo Money</option>
                        <option value="QR">Pago QR</option>
                    </select>
                </div>

                <div style="margin-bottom:16px;" id="comprobante-field">
                    <label for="comprobante">Comprobante de pago (opcional)</label>
                    <input type="file" name="comprobante" id="comprobante" accept=".jpg,.png,.pdf">
                    <span style="display:block;font-size:11px;color:#94a3b8;margin-top:4px;">JPG, PNG o PDF — Máx. 2 MB</span>
                </div>

                <button type="submit" class="button">
                    💳 Registrar Pago
                </button>
            </form>
        </div>

        {{-- Historial de pagos --}}
        <div class="card" style="padding:0;overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;">
                <h3 style="font-size:15px;margin:0;">Historial de pagos</h3>
            </div>
            @if ($pagos->isEmpty())
                <div style="padding:40px;text-align:center;color:#94a3b8;font-size:14px;">
                    No registraste ningún pago todavía.
                </div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Método</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pagos as $pago)
                            <tr>
                                <td><code style="font-size:12px;">{{ $pago->codigo_pago }}</code></td>
                                <td>{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $pago->metodo_pago }}</td>
                                <td><strong>Bs. {{ number_format($pago->monto, 2) }}</strong></td>
                                <td>
                                    @php
                                        $colores = ['pendiente' => '#f59e0b', 'confirmado' => '#059669', 'rechazado' => '#dc2626'];
                                    @endphp
                                    <span style="color:{{ $colores[$pago->estado_pago] ?? '#64748b' }};font-weight:600;">
                                        {{ $pago->estado_pago }}
                                    </span>
                                </td>
                                <td>
                                    @if ($pago->comprobante_url)
                                        <a href="{{ asset('storage/' . $pago->comprobante_url) }}" target="_blank" class="button button-ghost button-sm">Ver</a>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Información de cuentas --}}
        <div class="card" style="padding:20px;margin-top:20px;background:#f8fafc;">
            <h4 style="font-size:13px;margin:0 0 10px;color:#0f172a;">Información de pagos</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px;">
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:14px;">
                    <div style="color:#1e40af;font-weight:600;margin-bottom:8px;">💳 PayPal</div>
                    <div style="color:#475569;">Seleccioná PayPal como método y el pago se confirmará automáticamente.</div>
                    <div style="color:#94a3b8;font-size:11px;margin-top:6px;">(Integración pendiente con PayPal Sandbox)</div>
                </div>
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:14px;">
                    <div style="color:#0f172a;font-weight:600;margin-bottom:8px;">🏦 Transferencia / Depósito</div>
                    <div style="display:grid;grid-template-columns:auto 1fr;gap:4px 12px;font-size:12px;">
                        <span style="color:#64748b;">Banco:</span><span style="font-weight:500;">BNB</span>
                        <span style="color:#64748b;">Titular:</span><span style="font-weight:500;">FICCT - UAGRM</span>
                        <span style="color:#64748b;">Cuenta:</span><span style="font-weight:500;">100-123456-7-89</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
