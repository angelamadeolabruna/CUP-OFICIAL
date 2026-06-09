<x-layouts.app title="Iniciar sesión">
    <h2>Iniciar sesión</h2>
    <p class="sub">Ingresá con tu correo electrónico o CI.</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <label for="credencial">Correo o CI</label>
        <input id="credencial" name="credencial" value="{{ old('credencial') }}" required autofocus placeholder="Ej: admin@cup.test">

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required placeholder="••••••••">

        <button type="submit">Ingresar</button>

        <div class="login-link">
            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        </div>
    </form>
</x-layouts.app>
