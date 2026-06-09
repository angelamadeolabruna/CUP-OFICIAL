<x-layouts.app title="Reporte - Estadísticas por Materia">
    <div style="max-width:1400px;">
        <div class="flex-between" style="margin-bottom:16px;">
            <div>
                <h1 class="page-title">Estadísticas por Materia</h1>
                <p class="page-desc">Promedio general, aprobados y reprobados por materia.</p>
            </div>
            <div class="flex">
                <a href="{{ route('reportes.exportar', ['tipo' => 'estadisticas-materia', 'formato' => 'pdf']) }}" class="button button-secondary">📄 PDF</a>
                <a href="{{ route('reportes.exportar', ['tipo' => 'estadisticas-materia', 'formato' => 'csv']) }}" class="button button-secondary">📊 CSV</a>
                <a href="{{ route('reportes.index') }}" class="button button-secondary">← Volver</a>
            </div>
        </div>

        <div class="card" style="padding:0;overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="table" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th style="text-align:center;">Total Postulantes</th>
                            <th style="text-align:center;">Con Notas</th>
                            <th style="text-align:center;">Promedio General</th>
                            <th style="text-align:center;">Aprobados</th>
                            <th style="text-align:center;">Reprobados</th>
                            <th style="text-align:center;">% Aprobación</th>
                            <th style="text-align:center;">Barra</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($estadisticas as $e)
                            <tr>
                                <td><strong>{{ $e['materia']->nombre_materia }}</strong></td>
                                <td style="text-align:center;">{{ $e['total_postulantes'] }}</td>
                                <td style="text-align:center;">{{ $e['con_notas'] }}</td>
                                <td style="text-align:center;font-weight:600;">
                                    {{ $e['promedio_general'] ?? '—' }}
                                </td>
                                <td style="text-align:center;color:#059669;font-weight:600;">{{ $e['aprobados'] }}</td>
                                <td style="text-align:center;color:#dc2626;font-weight:600;">{{ $e['reprobados'] }}</td>
                                <td style="text-align:center;font-weight:600;">
                                    {{ $e['porcentaje_aprobacion'] }}%
                                </td>
                                <td style="min-width:120px;">
                                    <div style="height:8px;background:#fee2e2;border-radius:4px;overflow:hidden;">
                                        <div style="height:100%;width:{{ $e['porcentaje_aprobacion'] }}%;background:#059669;border-radius:4px;"></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align:center;padding:32px;color:#64748b;">No hay datos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
