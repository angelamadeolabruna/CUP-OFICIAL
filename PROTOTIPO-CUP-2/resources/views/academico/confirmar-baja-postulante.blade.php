<x-layouts.app title="Confirmar Baja de Postulante">
    <div style="max-width:640px;">
        <div style="margin-bottom:18px;">
            <a href="{{ route('academico.postulantes.index') }}" class="button button-ghost button-sm">← Volver al listado</a>
        </div>

        <div class="card" style="border-left:4px solid #dc2626;">
            <h1 class="page-title" style="color:#dc2626;">Confirmar Baja de Postulante</h1>
            <p class="page-desc">
                Estás por dar de baja a <strong>{{ $postulante->prepostulante?->nombres ?? '' }} {{ $postulante->prepostulante?->apellidos ?? '' }}</strong>
                (CI: {{ $postulante->prepostulante?->ci ?? '—' }}).
            </p>

            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px 16px;margin:16px 0;">
                <p style="margin:0;color:#991b1b;font-weight:600;margin-bottom:4px;">⚠️ Esta acción cambiará el estado a <em>baja</em></p>
                <p style="margin:0;color:#b91c1c;font-size:13px;">
                    El postulante quedará inactivo en el sistema. La información histórica se conserva.
                    @if ($postulante->resultado)
                        <br>Tiene resultados académicos registrados.
                    @endif
                    @if ($postulante->admision)
                        <br>Tiene un registro de admisión.
                    @endif
                </p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('academico.postulantes.baja.ejecutar', $postulante->id_postulante) }}">
                @csrf

                <div>
                    <label for="motivo">Motivo de la Baja *</label>
                    <textarea name="motivo" id="motivo" rows="4" placeholder="Explicá el motivo de la baja (mín. 10 caracteres)..."
                              style="resize:vertical;width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-family:inherit;font-size:14px;">{{ old('motivo') }}</textarea>
                    @error('motivo') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div style="margin-top:24px;display:flex;gap:10px;">
                    <button type="submit" class="button" style="background:#dc2626;border-color:#dc2626;"
                            onclick="return confirm('¿Estás seguro de dar de baja a este postulante? Esta acción no se puede deshacer fácilmente.');">
                        Confirmar Baja
                    </button>
                    <a href="{{ route('academico.postulantes.index') }}" class="button button-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
