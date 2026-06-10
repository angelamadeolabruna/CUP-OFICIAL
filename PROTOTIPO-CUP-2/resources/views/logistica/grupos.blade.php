<x-layouts.app title="Calcular Grupos Necesarios">
    <div style="max-width:600px;">
        <h1 class="page-title">Calcular Cantidad de Grupos</h1>
        <p class="page-desc">Calculá cuántos grupos se habilitarán por materia según los inscritos y la capacidad máxima.</p>

        <div style="margin-bottom:16px;padding:12px 16px;background:#f8fafc;border-radius:6px;font-size:13px;">
            <strong>Gestión activa:</strong> {{ $gestion?->nombre_gestion ?? '—' }}
        </div>

        {{-- $totalMaterias debe venir del controlador --}}

        @if ($error)
            <div class="error">{{ $error }}</div>
        @endif

        <div class="card" style="padding:24px;">
            @if (!$gestion)
                <p style="color:#64748b;">No hay una gestión académica activa.</p>
            @else
                {{-- Resumen de datos --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
                    <div style="padding:16px;background:#f1f5f9;border-radius:8px;text-align:center;">
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Total inscritos</div>
                        <div style="font-size:32px;font-weight:700;color:#0f172a;margin-top:4px;">{{ $totalInscritos }}</div>
                    </div>
                    <div style="padding:16px;background:#f1f5f9;border-radius:8px;text-align:center;">
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Capacidad por grupo</div>
                        <div style="font-size:32px;font-weight:700;color:#0f172a;margin-top:4px;">
                            {{ $capacidadMaxima ?? '—' }}
                        </div>
                    </div>
                </div>

                {{-- Fórmula --}}
                <div style="padding:12px 16px;background:#f8fafc;border-radius:6px;margin-bottom:24px;font-size:13px;font-family:monospace;">
                    grupos por materia = CEIL( {{ $totalInscritos }} / {{ $capacidadMaxima ?? '?' }} )
                </div>

                {{-- Resultado --}}
                @if ($gruposCalculados !== null)
                    <div style="padding:24px;background:#f0fdf4;border:2px solid #86efac;border-radius:10px;text-align:center;">
                        <div style="font-size:13px;color:#065f46;font-weight:600;margin-bottom:4px;">GRUPOS POR MATERIA</div>
                        <div style="font-size:48px;font-weight:800;color:#059669;">{{ $gruposCalculados }}</div>
                        <div style="font-size:12px;color:#065f46;margin-top:8px;">
                            Distribuyendo {{ $totalInscritos }} inscritos en grupos de hasta {{ $capacidadMaxima }} estudiantes
                        </div>
                        <div style="font-size:13px;color:#065f46;margin-top:8px;">
                            Total: <strong>{{ $gruposCalculados * $totalMaterias }} grupos</strong>
                            ({{ $gruposCalculados }} × {{ $totalMaterias }} materias)
                        </div>
                    </div>
                @else
                    <div style="padding:24px;background:#fff7ed;border:2px solid #fcd34d;border-radius:10px;text-align:center;color:#92400e;font-size:14px;">
                        No se puede realizar el cálculo.
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts.app>

