<x-layouts.app title="Configurar Evaluaciones">
    <div style="max-width:960px;">
        <div class="flex-between" style="margin-bottom:8px;">
            <div>
                <h1 class="page-title">Configuración de Evaluaciones Académicas</h1>
                <p class="page-desc">Definí las materias, exámenes y porcentajes de ponderación para la gestión académica.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="error" style="margin-bottom:16px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (!$idGestion || $materias->isEmpty())
            <div class="card" style="padding:24px;">
                <p style="color:#64748b;">No hay gestiones académicas registradas. Creá una antes de configurar las evaluaciones.</p>
            </div>
        @else
            {{-- Selector de gestión --}}
            <div style="margin-bottom:24px;">
                <form method="GET" action="{{ route('academico.evaluaciones.index') }}" style="display:flex;align-items:end;gap:12px;">
                    <div style="flex:1;">
                        <label for="id_gestion">Gestión Académica</label>
                        <select name="id_gestion" id="id_gestion" onchange="this.form.submit()">
                            @foreach ($gestiones as $g)
                                <option value="{{ $g->id_gestion }}" @selected($g->id_gestion == $idGestion)>
                                    {{ $g->nombre_gestion }}
                                    @if ($g->estado === 'activa') (Activa) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <noscript><button type="submit" class="button button-sm">Ir</button></noscript>
                </form>
            </div>

            @php $gestionActual = $gestiones->firstWhere('id_gestion', $idGestion); @endphp
            @if ($gestionActual)
                <div style="margin-bottom:20px;padding:12px 16px;background:#f8fafc;border-radius:6px;font-size:13px;">
                    <strong>Gestión seleccionada:</strong> {{ $gestionActual->nombre_gestion }}
                    <span style="color:#64748b;margin-left:8px;">
                        ({{ $gestionActual->fecha_inicio->format('d/m/Y') }} — {{ $gestionActual->fecha_fin?->format('d/m/Y') ?? '—' }})
                        — Estado: <strong>{{ $gestionActual->estado }}</strong>
                    </span>
                </div>
            @endif

            {{-- Materias --}}
            @foreach ($materias as $materia)
                @php
                    $evaluaciones = $materia->evaluaciones;
                    $sumaPorcentajes = $evaluaciones->sum('porcentaje');
                    $numerosUsados = $evaluaciones->pluck('numero_evaluacion');
                    $numerosDisponibles = collect([1, 2, 3])->diff($numerosUsados);
                @endphp
                <div class="card" style="margin-bottom:20px;border-left:4px solid #3b82f6;">
                    <div class="flex-between" style="margin-bottom:16px;">
                        <h2 style="font-size:18px;font-weight:700;">{{ $materia->nombre_materia }}</h2>
                        @if ($evaluaciones->isNotEmpty())
                            <span style="font-size:13px;color:#64748b;">
                                Ponderación total:
                                <strong style="color:{{ $sumaPorcentajes == 100 ? '#059669' : '#dc2626' }}">
                                    {{ $sumaPorcentajes }}%
                                </strong>
                                @if ($sumaPorcentajes == 100)
                                    <span style="color:#059669;"> ✓</span>
                                @else
                                    <span style="color:#dc2626;"> (debe sumar 100%)</span>
                                @endif
                            </span>
                        @endif
                    </div>

                    @if ($evaluaciones->isNotEmpty())
                        <table class="table" style="margin-bottom:16px;">
                            <thead>
                                <tr>
                                    <th style="width:50px;">N°</th>
                                    <th>Examen</th>
                                    <th style="width:120px;">Ponderación</th>
                                    <th style="width:140px;">Fecha</th>
                                    <th style="width:120px;">Estado</th>
                                    <th style="width:100px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($evaluaciones as $e)
                                    <tr>
                                        <td><strong>{{ $e->numero_evaluacion }}</strong></td>
                                        <td>
                                            @switch($e->numero_evaluacion)
                                                @case(1) Primer Examen Parcial @break
                                                @case(2) Segundo Examen Parcial @break
                                                @case(3) Examen Final @break
                                            @endswitch
                                        </td>
                                        <td><strong>{{ $e->porcentaje }}%</strong></td>
                                        <td>{{ $e->fecha_evaluacion?->format('d/m/Y') ?? '—' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $e->estado === 'programada' ? 'inactivo' : 'activo' }}">
                                                {{ $e->estado }}
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('academico.evaluaciones.eliminar', $e->id_evaluacion) }}"
                                                  onsubmit="return confirm('¿Eliminar este examen? Se perderán las notas asociadas.')">
                                                @csrf
                                                <button type="submit" class="button-sm button-danger" style="padding:4px 10px;font-size:12px;">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="padding:16px;background:#f8fafc;border-radius:6px;margin-bottom:16px;text-align:center;color:#64748b;">
                            No hay exámenes configurados para esta materia.
                        </div>
                    @endif

                    {{-- Formulario para agregar examen --}}
                    @if ($numerosDisponibles->isNotEmpty())
                        <form method="POST" action="{{ route('academico.evaluaciones.guardar') }}" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;padding-top:8px;border-top:1px solid #f1f5f9;">
                            @csrf
                            <input type="hidden" name="id_gestion" value="{{ $idGestion }}">
                            <input type="hidden" name="id_materia" value="{{ $materia->id_materia }}">

                            <div>
                                <label for="numero_evaluacion_{{ $materia->id_materia }}">N° Examen</label>
                                <select name="numero_evaluacion" id="numero_evaluacion_{{ $materia->id_materia }}" required style="width:140px;">
                                    @foreach ($numerosDisponibles as $n)
                                        <option value="{{ $n }}">
                                            {{ $n }}°
                                            @switch($n)
                                                @case(1) Parcial @break
                                                @case(2) Parcial @break
                                                @case(3) Final @break
                                            @endswitch
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="porcentaje_{{ $materia->id_materia }}">Porcentaje (%)</label>
                                <input type="number" name="porcentaje" id="porcentaje_{{ $materia->id_materia }}"
                                       min="1" max="100" step="0.01"
                                       placeholder="Ej: 30" required style="width:120px;">
                            </div>

                            <div>
                                <label for="fecha_{{ $materia->id_materia }}">Fecha (opcional)</label>
                                <input type="date" name="fecha_evaluacion" id="fecha_{{ $materia->id_materia }}" style="width:160px;">
                            </div>

                            <button type="submit" class="button button-sm">+ Agregar Examen</button>
                        </form>
                    @else
                        <div style="padding:10px 0;font-size:13px;color:#64748b;border-top:1px solid #f1f5f9;">
                            ✓ Máximo de 3 exámenes alcanzado para esta materia.
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Resumen de ponderaciones --}}
            <div class="card" style="background:#f8fafc;">
                <h3 style="font-size:14px;font-weight:600;margin-bottom:12px;">Resumen de Ponderaciones</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>1° Parcial</th>
                            <th>2° Parcial</th>
                            <th>Final</th>
                            <th>Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materias as $materia)
                            @php
                                $evals = $materia->evaluaciones->keyBy('numero_evaluacion');
                                $total = $evals->sum('porcentaje');
                            @endphp
                            <tr>
                                <td><strong>{{ $materia->nombre_materia }}</strong></td>
                                <td>{{ optional($evals->get(1))->porcentaje ?? '—' }}%</td>
                                <td>{{ optional($evals->get(2))->porcentaje ?? '—' }}%</td>
                                <td>{{ optional($evals->get(3))->porcentaje ?? '—' }}%</td>
                                <td><strong>{{ $total }}%</strong></td>
                                <td>
                                    @if ($total == 100)
                                        <span class="badge badge-activo">Completo</span>
                                    @elseif ($total > 0)
                                        <span class="badge badge-inactivo">Incompleto</span>
                                    @else
                                        <span style="color:#94a3b8;">Sin configurar</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top:12px;padding:8px 12px;background:#fef3c7;border-radius:6px;font-size:12px;color:#92400e;">
                    ⚠️ Todas las materias deben tener una ponderación total del <strong>100%</strong> para poder registrar notas.
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
