<x-layouts.app title="Cambiar contraseña">
    <div style="max-width:500px;">
        <div class="card">
            <h1 class="page-title">Cambiar Contraseña</h1>
            <p class="page-desc">Actualizá tu contraseña de acceso al sistema.</p>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('perfil.password.update') }}">
                @csrf
                <label for="password_actual">Contraseña actual</label>
                <input id="password_actual" name="password_actual" type="password" required>

                <label for="password">Nueva contraseña</label>
                <input id="password" name="password" type="password" required>

                <label for="password_confirmation">Confirmar nueva contraseña</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>

                <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:10px;">
                    <a href="{{ route('dashboard') }}" class="button button-secondary">Volver</a>
                    <button type="submit">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
