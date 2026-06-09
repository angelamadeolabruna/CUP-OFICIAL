<x-layouts.app title="Reporte - Grupos con Más Aprobados">
    <div style="max-width:1400px;">
        <div class="flex-between" style="margin-bottom:16px;">
            <div>
                <h1 class="page-title">Grupos con Más Aprobados</h1>
                <p class="page-desc">Ranking de grupos por cantidad y porcentaje de postulantes aprobados.</p>
            </div>
            <div class="flex">
                <a href="{{ route('reportes.exportar', ['tipo' => 'grupos-mas-aprobados', 'formato' => 'pdf']) }}" class="button button-secondary">📄 PDF</a>
                <a href="{{ route('reportes.exportar', ['tipo' => 'grupos-mas-aprobados', 'formato' => 'csv']) }}" class="button button-secondary">📊 CSV</a>
                <a href="{{ route('reportes.index') }}" class="button button-secondary">← Volver</a>
            </div>
        </div>

        <div class="card" style="padding:0;overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="table" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Grupo</th>
                            <th>Materia</th>
                            <th>Aula</th>
                            <th style="text-align:center;">Total Postulantes</th>
                            <th style="text-align:center;">Aprobados</th>
                            <th style="text-align:center;">% Aprobación</th>
                            <th style="text-align:center;">Barra</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ranking as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $r['grupo']->nombre_grupo }}</strong></td>
                                <td>{{ $r['grupo']->materia?->nombre_materia ?? '—' }}</td>
                                <td>{{ $r['grupo']->aula?->codigo_aula ?? $r['grupo']->aula?->nombre ?? '—' }}</td>
                                <td style="text-align:center;">{{ $r['total'] }}</td>
                                <td style="text-align:center;font-weight:600;color:#059669;">{{ $r['aprobados'] }}</td>
                                <td style="text-align:center;font-weight:700;">{{ $r['porcentaje'] }}%</td>
                                <td style="min-width:120px;">
                                    <div style="height:8px;background:#fee2e2;border-radius:4px;overflow:hidden;">
                                        <div style="height:100%;width:{{ $r['porcentaje'] }}%;background:#059669;border-radius:4px;"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align:center;padding:32px;color:#64748b;">No hay grupos con datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
