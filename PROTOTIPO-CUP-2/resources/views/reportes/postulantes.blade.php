<x-layouts.app title="Reporte - Postulantes">
    <div style="max-width:1400px;">
        <div class="flex-between" style="margin-bottom:16px;">
            <div>
                <h1 class="page-title">Reporte de Postulantes</h1>
                <p class="page-desc">Listado completo de postulantes con datos personales y carreras elegidas.</p>
            </div>
            <div class="flex">
                <a href="{{ route('reportes.exportar', ['tipo' => 'postulantes', 'formato' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="button button-secondary">📄 PDF</a>
                <a href="{{ route('reportes.exportar', ['tipo' => 'postulantes', 'formato' => 'csv']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="button button-secondary">📊 CSV</a>
                <a href="{{ route('reportes.index') }}" class="button button-secondary">← Volver</a>
            </div>
        </div>

        <div class="card" style="margin-bottom:20px;padding:16px 20px;">
            <form method="GET" class="flex" style="flex-wrap:wrap;gap:12px;align-items:flex-end;">
                <div style="width:200px;">
                    <label style="margin-bottom:3px;">Estado</label>
                    <select name="estado_postulante" style="margin-bottom:0;" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="activo" {{ request('estado_postulante') === 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ request('estado_postulante') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div style="width:250px;">
                    <label style="margin-bottom:3px;">Carrera (cualquier opción)</label>
                    <select name="id_carrera" style="margin-bottom:0;" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        @foreach ($carreras as $c)
                            <option value="{{ $c->id_carrera }}" {{ request('id_carrera') == $c->id_carrera ? 'selected' : '' }}>{{ $c->codigo_carrera }} — {{ $c->nombre_carrera }}</option>
                        @endforeach
                    </select>
                </div>
                @if (request()->anyFilled(['estado_postulante', 'id_carrera']))
                    <a href="{{ route('reportes.postulantes') }}" class="button button-secondary button-sm" style="align-self:flex-end;">Limpiar</a>
                @endif
            </form>
        </div>

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
                            <th>Estado Acad.</th>
                            <th>Admisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($postulantes as $p)
                            <tr>
                                <td>{{ $p->id_postulante }}</td>
                                <td>{{ $p->prepostulante?->ci ?? '—' }}</td>
                                <td><strong>{{ $p->prepostulante?->apellidos ?? '' }} {{ $p->prepostulante?->nombres ?? '' }}</strong></td>
                                <td>{{ $p->primeraOpcion?->codigo_carrera ?? '—' }}</td>
                                <td>{{ $p->segundaOpcion?->codigo_carrera ?? '—' }}</td>
                                <td style="text-align:center;font-weight:600;">{{ $p->resultado?->promedio_final ?? '—' }}</td>
                                <td>
                                    @if ($p->resultado?->estado_academico === 'aprobado')
                                        <span class="badge badge-aprobado">APROBADO</span>
                                    @elseif ($p->resultado?->estado_academico === 'reprobado')
                                        <span class="badge badge-reprobado">REPROBADO</span>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($p->admision?->estado_admision === 'admitido')
                                        <span class="badge badge-aprobado">ADMITIDO</span>
                                    @elseif ($p->admision?->estado_admision === 'no_admitido')
                                        <span class="badge badge-reprobado">NO ADMITIDO</span>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align:center;padding:32px;color:#64748b;">No hay postulantes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-top:16px;">{{ $postulantes->links() }}</div>
    </div>
</x-layouts.app>
