<x-layouts.app title="Reporte - Resultados Académicos">
    <div style="max-width:1400px;">
        <div class="flex-between" style="margin-bottom:16px;">
            <div>
                <h1 class="page-title">Reporte de Resultados Académicos</h1>
                <p class="page-desc">Postulantes aprobados y reprobados del proceso de admisión.</p>
            </div>
            <div class="flex">
                <a href="{{ route('reportes.exportar', ['tipo' => 'resultados-academicos', 'formato' => 'pdf']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="button button-secondary">📄 PDF</a>
                <a href="{{ route('reportes.exportar', ['tipo' => 'resultados-academicos', 'formato' => 'csv']) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="button button-secondary">📊 CSV</a>
                <a href="{{ route('reportes.index') }}" class="button button-secondary">← Volver</a>
            </div>
        </div>

        <div class="grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));">
            <div class="metric" style="border-left:4px solid #059669;">
                <div class="label">Aprobados</div>
                <strong style="color:#059669;">{{ $conteo['aprobados'] }}</strong>
            </div>
            <div class="metric" style="border-left:4px solid #dc2626;">
                <div class="label">Reprobados</div>
                <strong style="color:#dc2626;">{{ $conteo['reprobados'] }}</strong>
            </div>
            <div class="metric">
                <div class="label">Total c/resultado</div>
                <strong>{{ $conteo['aprobados'] + $conteo['reprobados'] }}</strong>
            </div>
        </div>

        <div class="card" style="margin-bottom:20px;padding:16px 20px;">
            <form method="GET" class="flex" style="flex-wrap:wrap;gap:12px;align-items:flex-end;">
                <div style="width:200px;">
                    <label style="margin-bottom:3px;">Filtrar por estado</label>
                    <select name="estado" style="margin-bottom:0;" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="aprobado" {{ request('estado') === 'aprobado' ? 'selected' : '' }}>Aprobados</option>
                        <option value="reprobado" {{ request('estado') === 'reprobado' ? 'selected' : '' }}>Reprobados</option>
                    </select>
                </div>
                <a href="{{ route('reportes.resultados-academicos') }}" class="button button-secondary button-sm" style="align-self:flex-end;">Limpiar</a>
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
                            <th>Promedio Final</th>
                            <th>1ra Opción</th>
                            <th>2da Opción</th>
                            <th>Estado</th>
                            <th>Admisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($postulantes as $p)
                            <tr>
                                <td>{{ $p->id_postulante }}</td>
                                <td>{{ $p->prepostulante?->ci ?? '—' }}</td>
                                <td><strong>{{ $p->prepostulante?->apellidos ?? '' }} {{ $p->prepostulante?->nombres ?? '' }}</strong></td>
                                <td style="text-align:center;font-weight:700;">{{ $p->resultado?->promedio_final ?? '—' }}</td>
                                <td>{{ $p->primeraOpcion?->codigo_carrera ?? '—' }}</td>
                                <td>{{ $p->segundaOpcion?->codigo_carrera ?? '—' }}</td>
                                <td>
                                    @if ($p->resultado->estado_academico === 'aprobado')
                                        <span class="badge badge-aprobado">APROBADO</span>
                                    @else
                                        <span class="badge badge-reprobado">REPROBADO</span>
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
                            <tr><td colspan="8" style="text-align:center;padding:32px;color:#64748b;">No hay postulantes con resultados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div style="margin-top:16px;">{{ $postulantes->links() }}</div>
    </div>
</x-layouts.app>
