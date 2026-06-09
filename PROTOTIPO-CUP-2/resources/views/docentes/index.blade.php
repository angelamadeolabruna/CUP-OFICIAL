<x-layouts.app title="Gestionar Docentes">
    <div>
        <div class="flex-between">
            <div>
                <h1 class="page-title">Gestionar Docentes</h1>
                <p class="page-desc">Registrá, validá y gestioná los docentes del curso preuniversitario.</p>
            </div>
            <a href="{{ route('docentes.create') }}" class="button">+ Registrar Docente</a>
        </div>

        <div class="card" style="padding:0;overflow:hidden;">
            @if ($docentes->isEmpty())
                <div style="padding:40px;text-align:center;color:#94a3b8;">
                    No hay docentes registrados.
                    <a href="{{ route('docentes.create') }}" style="display:block;margin-top:8px;color:#2563eb;">Registrar primer docente</a>
                </div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Docente</th>
                            <th>CI</th>
                            <th>Profesión</th>
                            <th>Correo</th>
                            <th>Requisitos</th>
                            <th>Estado</th>
                            <th style="width:100px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($docentes as $docente)
                            <tr>
                                <td style="font-weight:600;">{{ $docente->nombre_completo }}</td>
                                <td>{{ $docente->ci }}</td>
                                <td>{{ $docente->profesion }}</td>
                                <td>{{ $docente->correo }}</td>
                                <td>
                                    @php
                                        $aprobados = $docente->requisitos->where('estado_revision', 'aprobado')->count();
                                        $total = $docente->requisitos->count();
                                    @endphp
                                    @if ($total === 0)
                                        <span style="color:#94a3b8;font-size:12px;">Sin requisitos</span>
                                    @else
                                        <span style="font-size:12px;">
                                            {{ $aprobados }}/{{ $total }}
                                            <span style="color:#64748b;">aprobados</span>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $estados = ['pendiente' => ['badge-activo', 'Pendiente'], 'aprobado' => ['badge-aprobado', 'Aprobado'], 'rechazado' => ['badge-reprobado', 'Rechazado']];
                                        $badge = $estados[$docente->estado_docente] ?? ['badge-inactivo', $docente->estado_docente];
                                    @endphp
                                    <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:4px;">
                                        <a href="{{ route('docentes.show', $docente->id_docente) }}" class="button button-sm button-secondary">Ver</a>
                                        <form method="POST" action="{{ route('docentes.destroy', $docente->id_docente) }}"
                                              onsubmit="return confirm('¿Eliminar a {{ $docente->nombre_completo }}?')" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="button button-sm button-danger">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
