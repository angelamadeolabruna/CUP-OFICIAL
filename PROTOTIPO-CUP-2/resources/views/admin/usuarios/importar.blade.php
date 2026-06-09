<x-layouts.app title="Importar Cuentas">
    <div style="max-width:720px;">
        <div style="margin-bottom:18px;">
            <a href="{{ route('admin.usuarios.index') }}" class="button button-ghost button-sm">← Volver al listado</a>
        </div>

        <div class="card" style="margin-bottom:20px;">
            <h1 class="page-title">Importar Cuentas desde Archivo</h1>
            <p class="page-desc">
                Cargá un archivo CSV o Excel (.xlsx) con las columnas: <strong>nombre_usuario, email, ci, rol, password</strong>.
                Se crearán las cuentas y se enviarán las credenciales por correo automáticamente.
            </p>

            @if (session('import_creados') !== null)
                <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:16px;margin-bottom:20px;">
                    <strong style="color:#166534;">Resultado de la importación</strong>
                    <ul style="margin:10px 0 0;padding-left:20px;color:#166534;">
                        <li>Total de filas procesadas: <strong>{{ session('import_total') }}</strong></li>
                        <li>Cuentas creadas: <strong>{{ session('import_creados') }}</strong></li>
                        <li>Correos enviados: <strong>{{ session('import_correos') }}</strong></li>
                        @if (count(session('import_errores', [])) > 0)
                            <li style="color:#991b1b;margin-top:8px;">Errores: <strong>{{ count(session('import_errores')) }}</strong></li>
                        @endif
                    </ul>
                    @if (count(session('import_errores', [])) > 0)
                        <div style="margin-top:12px;max-height:200px;overflow-y:auto;background:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:10px;">
                            <ul style="margin:0;padding-left:18px;font-size:13px;">
                                @foreach (session('import_errores') as $error)
                                    <li style="color:#991b1b;margin-bottom:4px;">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            @if (session('dev_credenciales'))
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:16px;margin-bottom:20px;font-size:13px;">
                    <strong style="color:#1e40af;">Credenciales generadas (Modo desarrollo):</strong>
                    <div style="margin-top:8px;max-height:220px;overflow-y:auto;">
                        @foreach (session('dev_credenciales') as $cred)
                            <div style="padding:6px 0;border-bottom:1px solid #e2e8f0;display:grid;grid-template-columns:1fr 1fr auto;gap:8px;font-size:12px;">
                                <span style="color:#1e293b;">{{ $cred['nombre'] }}</span>
                                <span style="color:#475569;">{{ $cred['email'] }}</span>
                                <code style="background:#e2e8f0;padding:2px 6px;border-radius:4px;color:#1e40af;">{{ $cred['password'] }}</code>
                            </div>
                        @endforeach
                    </div>
                </div>
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

            <form method="POST" action="{{ route('admin.usuarios.importar.post') }}" enctype="multipart/form-data">
                @csrf

                <div>
                    <label for="archivo">Seleccionar archivo (CSV o Excel) *</label>
                    <input type="file" name="archivo" id="archivo" accept=".csv,.xlsx,.xls,.txt" required style="padding:10px;">
                    @error('archivo') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:16px;margin-top:20px;">
                    <h3 style="font-size:14px;color:#0f172a;margin:0 0 10px;">Formato del archivo</h3>
                    <p style="font-size:13px;color:#475569;margin:0 0 12px;">
                        El archivo debe tener una fila de cabecera con estos nombres exactos y una fila por cada usuario:
                    </p>
                    <div style="overflow-x:auto;border:1px solid #cbd5e1;border-radius:4px;font-size:12px;">
                        <table style="width:100%;border-collapse:collapse;font-family:Consolas,'Courier New',monospace;">
                            <thead>
                                <tr style="background:#1e293b;color:#fff;">
                                    <th style="padding:8px 12px;text-align:left;border-right:1px solid #334155;">nombre_usuario</th>
                                    <th style="padding:8px 12px;text-align:left;border-right:1px solid #334155;">email</th>
                                    <th style="padding:8px 12px;text-align:left;border-right:1px solid #334155;">ci</th>
                                    <th style="padding:8px 12px;text-align:left;border-right:1px solid #334155;">rol</th>
                                    <th style="padding:8px 12px;text-align:left;">password</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background:#f1f5f9;">
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">Dr. Carlos Roca</td>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">carlos@uagrm.edu.bo</td>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">5432100</td>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">docente</td>
                                    <td style="padding:8px 12px;border-top:1px solid #e2e8f0;">MiClave2026</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">María López</td>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">maria@example.com</td>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">9876543</td>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">coordinador_academico</td>
                                    <td style="padding:8px 12px;border-top:1px solid #e2e8f0;">Bienvenido2026</td>
                                </tr>
                                <tr style="background:#f1f5f9;">
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">Admin Sistema</td>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">admin@ficct.edu.bo</td>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">1234567</td>
                                    <td style="padding:8px 12px;border-right:1px solid #e2e8f0;border-top:1px solid #e2e8f0;">administrador</td>
                                    <td style="padding:8px 12px;border-top:1px solid #e2e8f0;">AdminPass123</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px;">
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:4px;padding:10px;">
                            <strong style="color:#0f172a;">📄 CSV</strong>
                            <pre style="background:#f1f5f9;padding:8px;border-radius:4px;margin:6px 0 0;font-size:11px;overflow-x:auto;">nombre_usuario,email,ci,rol,password
Dr. Carlos Roca,carlos@uagrm.edu.bo,5432100,docente,MiClave2026
María López,maria@example.com,9876543,coordinador_academico,Bienvenido2026
Admin Sistema,admin@ficct.edu.bo,1234567,administrador,AdminPass123</pre>
                        </div>
                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:4px;padding:10px;">
                            <strong style="color:#0f172a;">✅ Roles válidos</strong>
                            <ul style="margin:6px 0 0;padding-left:16px;color:#475569;">
                                <li>administrador</li>
                                <li>coordinador_academico</li>
                                <li>docente</li>
                                <li>prepostulante</li>
                                <li>postulante_oficial</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:10px;">
                    <a href="{{ route('admin.usuarios.index') }}" class="button button-secondary">Cancelar</a>
                    <button type="submit">Importar Cuentas</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
