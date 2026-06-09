<x-layouts.app title="Postulantes">
    <div style="max-width:1400px;">
        <div class="flex-between" style="margin-bottom:16px;">
            <div>
                <h1 class="page-title">Postulantes</h1>
                <p class="page-desc">Todos los postulantes registrados con sus datos completos, carreras elegidas, notas y estado de admisión.</p>
            </div>
        </div>

        @if (!$gestionActiva)
            <div class="card" style="padding:24px;text-align:center;">
                <p style="color:#64748b;">No hay una gestión académica activa.</p>
            </div>
        @else
            {{-- Filtros --}}
            <div class="card" style="margin-bottom:20px;padding:16px 20px;">
                <form method="GET" class="flex" style="flex-wrap:wrap;gap:12px;align-items:flex-end;">
                    <div style="flex:1;min-width:200px;">
                        <label for="buscar" style="margin-bottom:3px;">Buscar</label>
                        <input type="text" id="buscar" name="buscar" placeholder="Nombre, apellido o CI..."
                               value="{{ request('buscar') }}" style="margin-bottom:0;">
                    </div>
                    <div style="width:180px;">
                        <label for="estado" style="margin-bottom:3px;">Estado</label>
                        <select name="estado" id="estado" style="margin-bottom:0;">
                            <option value="">Todos</option>
                            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    <button type="submit" class="button">🔍 Filtrar</button>
                    @if (request()->anyFilled(['buscar', 'estado']))
                        <a href="{{ route('academico.postulantes.index') }}" class="button button-secondary">Limpiar</a>
                    @endif
                </form>
            </div>

            {{-- Resumen --}}
            <div class="grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));">
                <div class="metric">
                    <div class="label">Total</div>
                    <strong>{{ $postulantes->total() }}</strong>
                </div>
                <div class="metric">
                    <div class="label">Con Notas</div>
                    <strong>{{ $postulantes->filter(fn($p) => $p->notas->isNotEmpty())->count() }}</strong>
                </div>
                <div class="metric" style="border-left:4px solid #059669;">
                    <div class="label">Aprobados</div>
                    <strong style="color:#059669;">{{ $postulantes->filter(fn($p) => $p->resultado && $p->resultado->estado_academico === 'aprobado')->count() }}</strong>
                </div>
                <div class="metric" style="border-left:4px solid #dc2626;">
                    <div class="label">Reprobados</div>
                    <strong style="color:#dc2626;">{{ $postulantes->filter(fn($p) => $p->resultado && $p->resultado->estado_academico === 'reprobado')->count() }}</strong>
                </div>
                <div class="metric" style="border-left:4px solid #2563eb;">
                    <div class="label">Admitidos</div>
                    <strong style="color:#2563eb;">{{ $postulantes->filter(fn($p) => $p->admision && $p->admision->estado_admision === 'admitido')->count() }}</strong>
                </div>
            </div>

            {{-- Tabla de postulantes --}}
            <div class="card" style="padding:0;overflow:hidden;">
                <div style="overflow-x:auto;">
                    <table class="table" style="font-size:12px;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>CI</th>
                                <th>Apellidos y Nombres</th>
                                <th>1ra Opción</th>
                                <th>2da Opción</th>
                                <th>Promedio</th>
                                <th>Estado Académico</th>
                                <th>Admisión</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($postulantes as $p)
                                <tr>
                                    <td>{{ $p->id_postulante }}</td>
                                    <td>{{ $p->prepostulante?->ci ?? '—' }}</td>
                                    <td><strong>{{ $p->prepostulante?->apellidos ?? '' }} {{ $p->prepostulante?->nombres ?? '' }}</strong></td>
                                    <td>
                                        @if ($p->primeraOpcion)
                                            <span style="font-weight:600;">{{ $p->primeraOpcion->codigo_carrera }}</span>
                                            <span style="color:#64748b;font-size:11px;">{{ $p->primeraOpcion->nombre_carrera }}</span>
                                        @else
                                            <span style="color:#94a3b8;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->segundaOpcion)
                                            <span style="font-weight:600;">{{ $p->segundaOpcion->codigo_carrera }}</span>
                                            <span style="color:#64748b;font-size:11px;">{{ $p->segundaOpcion->nombre_carrera }}</span>
                                        @else
                                            <span style="color:#94a3b8;">—</span>
                                        @endif
                                    </td>
                                    <td style="font-weight:600;text-align:center;">
                                        {{ $p->resultado?->promedio_final ?? '—' }}
                                    </td>
                                    <td>
                                        @if ($p->resultado?->estado_academico === 'aprobado')
                                            <span class="badge badge-aprobado">APROBADO</span>
                                        @elseif ($p->resultado?->estado_academico === 'reprobado')
                                            <span class="badge badge-reprobado">REPROBADO</span>
                                        @else
                                            <span style="color:#94a3b8;">Sin resultado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->admision?->estado_admision === 'admitido')
                                            <span class="badge badge-aprobado">ADMITIDO</span>
                                        @elseif ($p->admision?->estado_admision === 'no_admitido')
                                            <span class="badge badge-reprobado">NO ADMITIDO</span>
                                        @elseif ($p->admision)
                                            <span style="color:#f59e0b;font-weight:600;">PENDIENTE</span>
                                        @else
                                            <span style="color:#94a3b8;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="button button-ghost button-sm"
                                                onclick="toggleDetalle({{ $p->id_postulante }})"
                                                style="padding:4px 8px;font-size:14px;">▶</button>
                                    </td>
                                </tr>
                                <tr id="detalle-{{ $p->id_postulante }}" style="display:none;">
                                    <td colspan="9" style="padding:0;">
                                        <div style="padding:16px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;">
                                                {{-- Datos personales --}}
                                                <div>
                                                    <h4 style="font-size:13px;font-weight:700;margin-bottom:8px;color:#475569;">Datos Personales</h4>
                                                    <table style="font-size:12px;width:100%;">
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">CI:</td><td style="padding:3px 0;font-weight:600;">{{ $p->prepostulante?->ci ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Nombres:</td><td style="padding:3px 0;font-weight:600;">{{ $p->prepostulante?->nombres ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Apellidos:</td><td style="padding:3px 0;font-weight:600;">{{ $p->prepostulante?->apellidos ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Correo:</td><td style="padding:3px 0;">{{ $p->correo ?? $p->prepostulante?->correo ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Teléfono:</td><td style="padding:3px 0;">{{ $p->telefono ?? $p->prepostulante?->telefono ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Fecha Nac.:</td><td style="padding:3px 0;">{{ $p->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Sexo:</td><td style="padding:3px 0;">{{ $p->sexo ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Dirección:</td><td style="padding:3px 0;">{{ $p->direccion ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Ciudad:</td><td style="padding:3px 0;">{{ $p->ciudad ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Colegio:</td><td style="padding:3px 0;">{{ $p->colegio_procedencia ?? '—' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Título Bachiller:</td><td style="padding:3px 0;">{{ $p->titulo_bachiller ? 'Sí' : 'No' }}</td></tr>
                                                        <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Estado:</td><td style="padding:3px 0;">{{ $p->estado_postulante ?? '—' }}</td></tr>
                                                    </table>
                                                </div>
                                                {{-- Carreras elegidas --}}
                                                <div>
                                                    <h4 style="font-size:13px;font-weight:700;margin-bottom:8px;color:#475569;">Carreras Elegidas</h4>
                                                    <table style="font-size:12px;width:100%;">
                                                        <tr>
                                                            <td style="padding:3px 8px 3px 0;color:#64748b;">1ra Opción:</td>
                                                            <td style="padding:3px 0;font-weight:600;">
                                                                @if ($p->primeraOpcion)
                                                                    {{ $p->primeraOpcion->codigo_carrera }} — {{ $p->primeraOpcion->nombre_carrera }}
                                                                @else
                                                                    <span style="color:#94a3b8;">No definida</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:3px 8px 3px 0;color:#64748b;">2da Opción:</td>
                                                            <td style="padding:3px 0;font-weight:600;">
                                                                @if ($p->segundaOpcion)
                                                                    {{ $p->segundaOpcion->codigo_carrera }} — {{ $p->segundaOpcion->nombre_carrera }}
                                                                @else
                                                                    <span style="color:#94a3b8;">No definida</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    @if ($p->admision)
                                                        <h4 style="font-size:13px;font-weight:700;margin:12px 0 8px;color:#475569;">Resultado de Admisión</h4>
                                                        <table style="font-size:12px;width:100%;">
                                                            <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Carrera Asignada:</td><td style="padding:3px 0;font-weight:600;">{{ $p->admision->carreraAsignada?->codigo_carrera ?? '—' }}</td></tr>
                                                            <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Opción Asignada:</td><td style="padding:3px 0;">{{ $p->admision->opcion_asignada ? $p->admision->opcion_asignada . '°' : '—' }}</td></tr>
                                                            <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Orden Mérito:</td><td style="padding:3px 0;">{{ $p->admision->orden_merito ?: '—' }}</td></tr>
                                                            <tr><td style="padding:3px 8px 3px 0;color:#64748b;">Estado:</td><td style="padding:3px 0;">{{ $p->admision->estado_admision }}</td></tr>
                                                        </table>
                                                    @endif
                                                </div>
                                                {{-- Notas por materia --}}
                                                <div>
                                                    <h4 style="font-size:13px;font-weight:700;margin-bottom:8px;color:#475569;">Notas</h4>
                                                    @if ($p->notas->isNotEmpty())
                                                        @php
                                                            $notasPorMateria = $p->notas->groupBy(fn($n) => $n->evaluacion->id_materia);
                                                        @endphp
                                                        <table style="font-size:12px;width:100%;">
                                                            @foreach ($notasPorMateria as $idMateria => $notas)
                                                                @php $materia = $notas->first()->evaluacion->materia; @endphp
                                                                <tr>
                                                                    <td colspan="2" style="padding:6px 0 2px;font-weight:700;color:#1f2937;">
                                                                        {{ $materia?->codigo_materia ?? 'Materia #'.$idMateria }}
                                                                    </td>
                                                                </tr>
                                                                @foreach ($notas as $n)
                                                                    <tr>
                                                                        <td style="padding:2px 8px 2px 0;color:#64748b;padding-left:12px;">
                                                                            {{ $n->evaluacion->nombre_evaluacion }}
                                                                            ({{ $n->evaluacion->porcentaje }}%)
                                                                        </td>
                                                                        <td style="padding:2px 0;font-weight:600;">{{ $n->nota }}</td>
                                                                    </tr>
                                                                @endforeach
                                                                @php
                                                                    $promedio = $notas->sum(fn($n) => $n->nota * ($n->evaluacion->porcentaje / 100));
                                                                @endphp
                                                                <tr>
                                                                    <td style="padding:2px 8px 2px 12px;color:#059669;font-weight:600;">Promedio materia</td>
                                                                    <td style="padding:2px 0;font-weight:700;color:#059669;">{{ number_format($promedio, 2) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    @else
                                                        <p style="color:#94a3b8;font-size:12px;">Sin notas registradas.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align:center;padding:32px;color:#64748b;">
                                        No se encontraron postulantes.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación --}}
            <div style="margin-top:16px;">
                {{ $postulantes->links() }}
            </div>
        @endif
    </div>

    <script>
        function toggleDetalle(id) {
            const row = document.getElementById('detalle-' + id);
            if (row) {
                row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
            }
        }
    </script>
</x-layouts.app>
