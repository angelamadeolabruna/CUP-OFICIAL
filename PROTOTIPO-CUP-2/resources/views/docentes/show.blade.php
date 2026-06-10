<x-layouts.app title="Docente: {{ $docente->nombre_completo }}">
    <div>
        <a href="{{ route('docentes.index') }}" style="font-size:13px;color:#0a2a5e;text-decoration:none;display:inline-block;margin-bottom:12px;">← Volver a docentes</a>

        <div style="display:flex;gap:20px;flex-wrap:wrap;">
            {{-- Columna izquierda: datos del docente --}}
            <div style="flex:1;min-width:300px;">
                <div class="card" style="padding:24px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:48px;height:48px;border-radius:50%;background:#0a2a5e;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;">
                                {{ strtoupper(substr($docente->nombres, 0, 1)) }}{{ strtoupper(substr($docente->apellidos, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size:18px;font-weight:700;">{{ $docente->nombre_completo }}</div>
                                <div style="font-size:13px;color:#64748b;">{{ $docente->profesion }}</div>
                            </div>
                        </div>
                        @php
                            $estados = ['pendiente' => ['badge-pendiente', 'Pendiente'], 'aprobado' => ['badge-aprobado', 'Aprobado'], 'rechazado' => ['badge-reprobado', 'Rechazado']];
                            $badge = $estados[$docente->estado_docente] ?? ['badge-inactivo', $docente->estado_docente];
                        @endphp
                        <span class="badge {{ $badge[0] }}" style="font-size:13px;padding:4px 14px;">{{ $badge[1] }}</span>
                    </div>

                    <div style="display:grid;grid-template-columns:auto 1fr;gap:8px 16px;font-size:14px;">
                        <span style="color:#64748b;">CI:</span>
                        <span style="font-weight:500;">{{ $docente->ci }}</span>
                        <span style="color:#64748b;">Correo:</span>
                        <span style="font-weight:500;">{{ $docente->correo }}</span>
                        <span style="color:#64748b;">Teléfono:</span>
                        <span style="font-weight:500;">{{ $docente->telefono ?? '—' }}</span>
                        <span style="color:#64748b;">Usuario:</span>
                        <span style="font-weight:500;">
                            @if ($docente->usuario)
                                {{ $docente->usuario->nombre_usuario }}
                            @else
                                Sin usuario
                                <button onclick="document.getElementById('formCrearUsuario').style.display='block'"
                                        style="background:none;border:none;color:#0a2a5e;cursor:pointer;font-size:12px;text-decoration:underline;margin-left:6px;">
                                    Crear ahora
                                </button>
                            @endif
                        </span>
                        <span style="color:#64748b;">Registrado:</span>
                        <span style="font-weight:500;">{{ $docente->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    {{-- Crear usuario después del registro --}}
                    @if (!$docente->usuario)
                        <div id="formCrearUsuario" style="display:none;margin-top:16px;padding:16px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                            <strong style="font-size:14px;">🔑 Crear usuario para iniciar sesión</strong>
                            <form method="POST" action="{{ route('docentes.crear-usuario', $docente->id_docente) }}" style="margin-top:10px;">
                                @csrf
                                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                    <div style="flex:1;min-width:140px;">
                                        <label for="cu_nombre_usuario">Nombre de usuario *</label>
                                        <input type="text" name="nombre_usuario" id="cu_nombre_usuario" required style="margin:0;" placeholder="Ej: jperez">
                                    </div>
                                    <div style="flex:1;min-width:140px;">
                                        <label for="cu_password">Contraseña *</label>
                                        <input type="password" name="password" id="cu_password" required style="margin:0;" placeholder="Mín. 6 caracteres">
                                    </div>
                                    <div style="display:flex;align-items:flex-end;gap:6px;">
                                        <button type="submit" class="button button-sm">Crear Usuario</button>
                                        <button type="button" class="button button-sm button-secondary"
                                                onclick="document.getElementById('formCrearUsuario').style.display='none'">Cancelar</button>
                                    </div>
                                </div>
                                <div style="font-size:11px;color:#64748b;margin-top:6px;">Rol: docente — Podrá iniciar sesión con estas credenciales.</div>
                            </form>
                        </div>
                    @endif

                    {{-- Acciones de aprobación --}}
                    @if ($docente->estado_docente === 'pendiente')
                        <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
                            <form method="POST" action="{{ route('docentes.rechazar', $docente->id_docente) }}"
                                  onsubmit="return confirm('¿Rechazar a {{ $docente->nombre_completo }}?')">
                                @csrf
                                <button type="submit" class="button button-danger">Rechazar</button>
                            </form>
                            <form method="POST" action="{{ route('docentes.aprobar', $docente->id_docente) }}"
                                  onsubmit="return confirm('¿Aprobar a {{ $docente->nombre_completo }}?')">
                                @csrf
                                <button type="submit" class="button" style="background:#059669;">Aprobar Docente</button>
                            </form>
                        </div>
                    @endif

                    {{-- Eliminar docente --}}
                    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;">
                        <details style="font-size:13px;">
                            <summary style="color:#dc2626;cursor:pointer;font-weight:500;user-select:none;">🗑️ Eliminar docente</summary>
                            <div style="margin-top:10px;padding:12px;background:#fef2f2;border-radius:6px;">
                                <p style="font-size:13px;color:#991b1b;margin-bottom:10px;">
                                    Esta acción eliminará al docente y sus requisitos asociados.
                                </p>
                                <form method="POST" action="{{ route('docentes.destroy', $docente->id_docente) }}"
                                      onsubmit="return confirm('¿Eliminar definitivamente a {{ $docente->nombre_completo }}?')">
                                    @csrf
                                    @if ($docente->usuario)
                                        <label style="display:flex;align-items:center;gap:6px;font-weight:400;font-size:13px;cursor:pointer;margin-bottom:10px;">
                                            <input type="checkbox" name="eliminar_usuario" value="1"
                                                   style="width:auto;margin:0;accent-color:#dc2626;">
                                            También eliminar su usuario ({{ $docente->usuario->nombre_usuario }})
                                        </label>
                                    @endif
                                    <button type="submit" class="button button-danger button-sm">Eliminar Docente</button>
                                </form>
                            </div>
                        </details>
                    </div>
                </div>
            </div>

            {{-- Columna derecha: requisitos --}}
            <div style="flex:1;min-width:300px;">
                {{-- Subir requisito --}}
                <div class="card" style="padding:20px;margin-bottom:16px;">
                    <strong style="font-size:15px;">📎 Adjuntar Requisito</strong>
                    <form method="POST" action="{{ route('docentes.requisito.subir', $docente->id_docente) }}"
                          enctype="multipart/form-data" style="margin-top:12px;">
                        @csrf
                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                            <div style="flex:2;min-width:150px;">
                                <label for="tipo_requisito">Tipo *</label>
                                <select name="tipo_requisito" id="tipo_requisito" required style="margin:0;">
                                    <option value="">Seleccionar</option>
                                    <option value="Título Profesional">Título Profesional (Licenciatura/Ingeniería)</option>
                                    <option value="Diplomado Educación Superior">Diplomado en Educación Superior</option>
                                    <option value="Maestría">Maestría</option>
                                    <option value="Doctorado">Doctorado</option>
                                    <option value="Curso Pedagogía">Curso de Pedagogía</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div style="flex:1;min-width:120px;">
                                <label for="archivo">Archivo *</label>
                                <input type="file" name="archivo" id="archivo" accept=".pdf,.jpg,.jpeg,.png" required style="margin:0;font-size:12px;">
                                <div id="fileName" style="font-size:11px;color:#059669;margin-top:4px;font-weight:500;"></div>
                            </div>
                            <button type="submit" class="button button-sm">Subir</button>
                        </div>
                        <div style="font-size:11px;color:#94a3b8;margin-top:6px;">PDF, JPG o PNG — Máx 5MB</div>
                    </form>
                </div>

                {{-- Lista de requisitos --}}
                <div class="card" style="padding:0;overflow:hidden;">
                    <div style="padding:14px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;font-weight:600;font-size:14px;">
                        Requisitos Académicos
                        @if ($docente->requisitos->isNotEmpty())
                            <span style="color:#64748b;font-weight:400;font-size:12px;">
                                ({{ $docente->requisitos->where('estado_revision', 'aprobado')->count() }}/{{ $docente->requisitos->count() }} aprobados)
                            </span>
                        @endif
                    </div>
                    @forelse ($docente->requisitos as $req)
                        <div style="padding:12px 20px;border-bottom:1px solid #f1f5f9;">
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                                <div>
                                    <div style="font-weight:600;font-size:13px;">{{ $req->tipo_requisito }}</div>
                                    <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                        Subido: {{ $req->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    @php
                                        $revEstados = ['pendiente' => 'badge-activo', 'aprobado' => 'badge-aprobado', 'rechazado' => 'badge-reprobado'];
                                    @endphp
                                    <span class="badge {{ $revEstados[$req->estado_revision] ?? 'badge-inactivo' }}" style="font-size:10px;">
                                        {{ ucfirst($req->estado_revision) }}
                                    </span>
                                    <a href="{{ asset('storage/' . $req->archivo_url) }}" target="_blank" class="button button-sm button-secondary" style="font-size:11px;padding:4px 8px;">Ver</a>
                                </div>
                            </div>
                            @if ($req->estado_revision === 'pendiente')
                                <form method="POST" action="{{ route('docentes.requisito.revisar', $req->id_requisito_docente) }}"
                                      style="display:flex;gap:6px;margin-top:8px;align-items:center;flex-wrap:wrap;">
                                    @csrf
                                    <input type="hidden" name="estado_revision" value="" id="estado_revision_{{ $req->id_requisito_docente }}">
                                    <input type="text" name="observacion" placeholder="Observación (opcional)"
                                           style="margin:0;flex:1;min-width:120px;padding:4px 8px;font-size:12px;">
                                    <button type="submit" class="button button-sm" style="padding:4px 10px;font-size:11px;background:#059669;"
                                            onclick="document.getElementById('estado_revision_{{ $req->id_requisito_docente }}').value='aprobado'">
                                        ✅ Aprobar
                                    </button>
                                    <button type="submit" class="button button-sm button-danger" style="padding:4px 10px;font-size:11px;"
                                            onclick="document.getElementById('estado_revision_{{ $req->id_requisito_docente }}').value='rechazado'">
                                        ❌ Rechazar
                                    </button>
                                </form>
                            @elseif ($req->observacion)
                                <div style="margin-top:6px;font-size:12px;color:#64748b;padding:6px 10px;background:#f8fafc;border-radius:4px;">
                                    📝 {{ $req->observacion }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div style="padding:24px;text-align:center;color:#94a3b8;font-size:13px;">
                            No se han adjuntado requisitos todavía.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('archivo')?.addEventListener('change', function() {
            const name = this.files?.[0]?.name ?? '';
            document.getElementById('fileName').textContent = name ? '✓ ' + name : '';
        });
    </script>
</x-layouts.app>
