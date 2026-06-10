<x-layouts.app title="Reporte - Grupos">
    <div style="max-width:1400px;">
        <div class="flex-between" style="margin-bottom:16px;">
            <div>
                <h1 class="page-title">Reporte de Grupos</h1>
                <p class="page-desc">Grupos con sus aulas, horarios y postulantes asignados.</p>
            </div>
            <a href="{{ route('reportes.index') }}" class="button button-secondary">← Volver</a>
        </div>

        @forelse ($grupos as $g)
            <div class="card" style="margin-bottom:16px;padding:16px 20px;">
                <div class="flex-between" style="margin-bottom:12px;">
                    <div>
                        <h3 style="font-size:15px;font-weight:700;">{{ $g->nombre_grupo }}</h3>
                        <div style="font-size:12px;color:#64748b;">
                            Materia: <strong>{{ $g->materia?->nombre_materia ?? '—' }}</strong> |
                            Aula: <strong>{{ $g->aula?->codigo_aula ?? $g->aula?->nombre ?? '—' }}</strong> |
                            Capacidad: <strong>{{ $g->capacidad_maxima }}</strong> |
                            Estado: <strong>{{ $g->estado }}</strong>
                        </div>
                    </div>
                    <div style="font-size:13px;">
                        <strong>{{ $g->postulantes->count() }}</strong> postulantes
                    </div>
                </div>

                @if ($g->horarios->isNotEmpty())
                    <div style="font-size:12px;color:#475569;margin-bottom:8px;">
                        <strong>Horarios:</strong>
                        @foreach ($g->horarios as $gh)
                            <span style="display:inline-block;padding:2px 8px;background:#e0f2fe;border-radius:4px;margin-right:4px;">
                                {{ $gh->horario?->dia_semana ?? '' }} {{ $gh->horario?->hora_inicio ?? '' }}-{{ $gh->horario?->hora_fin ?? '' }}
                            </span>
                        @endforeach
                    </div>
                @endif

                @if ($g->postulantes->isNotEmpty())
                    <div style="overflow-x:auto;">
                        <table class="table" style="font-size:11px;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>CI</th>
                                    <th>Apellidos y Nombres</th>
                                    <th>Fecha asignación</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($g->postulantes as $pg)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $pg->postulante?->prepostulante?->ci ?? '—' }}</td>
                                        <td>{{ $pg->postulante?->prepostulante?->apellidos ?? '' }} {{ $pg->postulante?->prepostulante?->nombres ?? '' }}</td>
                                        <td>{{ $pg->fecha_asignacion ? \Carbon\Carbon::parse($pg->fecha_asignacion)->format('d/m/Y') : '—' }}</td>
                                        <td>{{ $pg->estado }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="font-size:13px;color:#94a3b8;">Sin postulantes asignados.</p>
                @endif
            </div>
        @empty
            <div class="card" style="padding:24px;text-align:center;color:#64748b;">No hay grupos registrados.</div>
        @endforelse

        <div style="margin-top:16px;">{{ $grupos->links() }}</div>
    </div>
</x-layouts.app>
