<x-layouts.app title="Reporte - Promedios por Materia">
    <div style="max-width:1400px;">
        <div class="flex-between" style="margin-bottom:16px;">
            <div>
                <h1 class="page-title">Reporte de Promedios por Materia</h1>
                <p class="page-desc">Desglose de notas y promedios por materia para cada postulante.</p>
            </div>
            <a href="{{ route('reportes.index') }}" class="button button-secondary">← Volver</a>
        </div>

        <div class="card" style="margin-bottom:20px;padding:16px 20px;">
            <form method="GET" class="flex" style="flex-wrap:wrap;gap:12px;align-items:flex-end;">
                <div style="width:250px;">
                    <label style="margin-bottom:3px;">Filtrar por materia</label>
                    <select name="id_materia" style="margin-bottom:0;" onchange="this.form.submit()">
                        <option value="">Todas las materias</option>
                        @foreach ($materias as $m)
                            <option value="{{ $m->id_materia }}" {{ request('id_materia') == $m->id_materia ? 'selected' : '' }}>{{ $m->nombre_materia }}</option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('reportes.promedios') }}" class="button button-secondary button-sm" style="align-self:flex-end;">Limpiar</a>
            </form>
        </div>

        @forelse ($promediosPostulantes as $item)
            <div class="card" style="margin-bottom:12px;padding:16px 20px;">
                <div class="flex-between" style="margin-bottom:8px;">
                    <div>
                        <strong>{{ $item['postulante']->prepostulante?->apellidos }} {{ $item['postulante']->prepostulante?->nombres }}</strong>
                        <span style="color:#64748b;font-size:13px;margin-left:8px;">CI {{ $item['postulante']->prepostulante?->ci }}</span>
                    </div>
                    <div>
                        @if ($item['promedio_final'])
                            <span style="font-weight:700;font-size:15px;">Prom. final: {{ $item['promedio_final'] }}</span>
                        @endif
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table" style="font-size:12px;">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th style="width:120px;">Promedio</th>
                                <th style="width:80px;text-align:center;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item['promedios'] as $prom)
                                <tr>
                                    <td>{{ $prom['materia']?->nombre_materia ?? '—' }}</td>
                                    <td style="font-weight:600;">{{ number_format($prom['promedio'], 2) }}</td>
                                    <td style="text-align:center;">
                                        @if ($prom['promedio'] >= 60)
                                            <span class="badge badge-aprobado">APROBADO</span>
                                        @else
                                            <span class="badge badge-reprobado">REPROBADO</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="card" style="padding:24px;text-align:center;color:#64748b;">
                No hay postulantes con notas registradas.
            </div>
        @endforelse

        <div style="margin-top:16px;">{{ $postulantes->links() }}</div>
    </div>
</x-layouts.app>
