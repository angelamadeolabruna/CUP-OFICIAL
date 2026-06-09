<x-layouts.app title="Configurar Capacidad de Aula">
    <div style="max-width:600px;">
        <h1 class="page-title">Configurar Capacidad de Aula</h1>
        <p class="page-desc">Definí la capacidad máxima de estudiantes por grupo para la gestión activa.</p>

        @if (session('status'))
            <div class="success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (!$gestion)
            <div class="card" style="padding:24px;">
                <p style="color:#64748b;">No hay una gestión académica activa. Creá una antes de configurar la capacidad.</p>
            </div>
        @else
            <div style="margin-bottom:20px;padding:12px 16px;background:#f8fafc;border-radius:6px;font-size:13px;">
                <strong>Gestión activa:</strong> {{ $gestion->nombre_gestion }}
                <span style="color:#64748b;margin-left:8px;">({{ $gestion->fecha_inicio->format('d/m/Y') }} — {{ $gestion->fecha_fin?->format('d/m/Y') ?? '—' }})</span>
            </div>

            @if ($capacidad && !request()->has('edit'))
                {{-- Modo visualización: config ya guardada --}}
                <div class="card" style="padding:24px;border:2px solid #86efac;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="background:#059669;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;">CONFIGURADO</span>
                            <span style="color:#059669;font-weight:600;">Capacidad de aula</span>
                        </div>
                        <a href="{{ route('logistica.capacidad.index', ['edit' => 1]) }}" class="button button-ghost button-sm">✏️ Editar</a>
                    </div>
                    <div style="display:grid;grid-template-columns:auto 1fr;gap:10px 20px;font-size:14px;">
                        <span style="color:#64748b;">Máximo estudiantes:</span>
                        <span style="font-weight:700;color:#0f172a;">{{ $capacidad->max_estudiantes }} por grupo</span>
                        @if ($capacidad->descripcion)
                            <span style="color:#64748b;">Descripción:</span>
                            <span style="color:#0f172a;">{{ $capacidad->descripcion }}</span>
                        @endif
                        <span style="color:#64748b;">Actualizado:</span>
                        <span style="color:#0f172a;">{{ $capacidad->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            @else
                {{-- Modo edición --}}
                <div class="card" style="padding:24px;border:2px solid {{ $capacidad ? '#f59e0b' : '#e2e8f0' }};">
                    @if ($capacidad)
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;padding:8px 12px;background:#fef3c7;border-radius:6px;font-size:13px;color:#92400e;">
                            ⚠️ Estás modificando una configuración ya existente.
                        </div>
                    @endif
                    <form method="POST" action="{{ route('logistica.capacidad.store') }}">
                        @csrf

                        <div style="margin-bottom:20px;">
                            <label for="max_estudiantes">Capacidad máxima por grupo *</label>
                            <input type="number" name="max_estudiantes" id="max_estudiantes"
                                   value="{{ old('max_estudiantes', $capacidad?->max_estudiantes ?? 70) }}"
                                   min="1" max="500" required>
                        </div>

                        <div style="margin-bottom:24px;">
                            <label for="descripcion">Descripción (opcional)</label>
                            <textarea name="descripcion" id="descripcion" rows="2" style="resize:vertical;">{{ old('descripcion', $capacidad?->descripcion) }}</textarea>
                            <span style="display:block;font-size:11px;color:#94a3b8;margin-top:4px;">
                                Ej: Capacidad máxima oficial por grupo CUP
                            </span>
                        </div>

                        <div style="display:flex;justify-content:flex-end;gap:10px;">
                            @if ($capacidad)
                                <a href="{{ route('logistica.capacidad.index') }}" class="button button-secondary">Cancelar</a>
                            @endif
                            <button type="submit" class="button">💾 Guardar configuración</button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Historial de cambios --}}
            @if ($capacidad)
                <div style="margin-top:16px;padding:12px 16px;background:#f1f5f9;border-radius:6px;font-size:12px;color:#64748b;display:flex;align-items:center;gap:8px;">
                    🛡️ Esta configuración está protegida — solo un coordinador o administrador puede modificarla.
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
