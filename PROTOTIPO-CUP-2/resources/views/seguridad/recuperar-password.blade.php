<x-layouts.app title="Recuperar Contraseña">
    <div class="card">
        <h2 style="font-size:20px;font-weight:700;margin-bottom:4px;">Recuperar Contraseña</h2>
        <p style="color:#64748b;font-size:14px;margin-bottom:20px;">
            Ingresá tu correo electrónico registrado y te enviaremos instrucciones para restablecer tu contraseña.
        </p>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
            @if (session('dev_reset_token'))
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:12px;margin-top:15px;font-size:13px;">
                    <strong style="color:#1e40af;">Enlace de recuperación (Modo desarrollo):</strong>
                    <br>
                    <a href="{{ route('password.reset', session('dev_reset_token')) }}?email={{ urlencode(session('dev_reset_email')) }}" style="color:#2563eb;text-decoration:underline;word-break:break-all;">
                        {{ route('password.reset', session('dev_reset_token')) }}?email={{ urlencode(session('dev_reset_email')) }}
                    </a>
                </div>
            @endif
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div>
                <label for="email">Correo Electrónico</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus placeholder="Ej: admin@cup.test">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div style="margin-top:24px;display:flex;justify-content:space-between;align-items:center;">
                <a href="{{ route('login') }}" class="button button-secondary button-sm">← Volver al Ingreso</a>
                <button type="submit">Enviar Enlace</button>
            </div>
        </form>
    </div>
</x-layouts.app>
