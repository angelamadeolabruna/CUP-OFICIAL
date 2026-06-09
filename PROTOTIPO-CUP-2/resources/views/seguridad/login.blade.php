<x-layouts.app title="Iniciar sesión">
    <div class="card">
        <h2 style="font-size:20px;font-weight:700;margin-bottom:4px;">Iniciar sesión</h2>
        <p style="color:#64748b;font-size:14px;margin-bottom:20px;">Ingresá con tu correo electrónico o CI.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <label for="credencial">Correo o CI</label>
            <input id="credencial" name="credencial" value="{{ old('credencial') }}" required autofocus placeholder="Ej: admin@cup.test">

            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" required>

            <button type="submit" style="width:100%;margin-bottom:12px;">Ingresar</button>

            <div style="text-align:center;">
                <a href="{{ route('password.request') }}" style="color:#64748b;font-size:13px;">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>
</x-layouts.app>
