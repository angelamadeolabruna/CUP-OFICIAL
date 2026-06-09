<x-layouts.app title="Reporte - Docentes por Grupo">
    <div style="max-width:1400px;">
        <div class="flex-between" style="margin-bottom:16px;">
            <div>
                <h1 class="page-title">Docentes por Grupo</h1>
                <p class="page-desc">Asignación de docentes a grupos con sus materias y horarios.</p>
            </div>
            <a href="{{ route('reportes.index') }}" class="button button-secondary">← Volver</a>
        </div>

        @forelse ($docentes as $d)
            <div class="card" style="margin-bottom:12px;padding:16px 20px;">
                <div style="margin-bottom:8px;">
                    <strong>{{ $d->apellidos }} {{ $d->nombres }}</strong>
                    <span style="color:#64748b;font-size:13px;margin-left:8px;">{{ $d->profesion }} | CI {{ $d->ci }}</span>
                </div>
                @if ($d->cargasHorarias->isNotEmpty())
                    <div style="overflow-x:auto;">
                        <table class="table" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th>Grupo</th>
                                    <th>Materia</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($d->cargasHorarias as $ch)
                                    <tr>
                                        <td><strong>{{ $ch->grupo?->nombre_grupo ?? '—' }}</strong></td>
                                        <td>{{ $ch->materia?->nombre_materia ?? '—' }}</td>
                                        <td>{{ $ch->estado }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="font-size:13px;color:#94a3b8;">Sin carga horaria asignada.</p>
                @endif
            </div>
        @empty
            <div class="card" style="padding:24px;text-align:center;color:#64748b;">No hay docentes con grupos asignados.</div>
        @endforelse

        <div style="margin-top:16px;">{{ $docentes->links() }}</div>
    </div>
</x-layouts.app>
