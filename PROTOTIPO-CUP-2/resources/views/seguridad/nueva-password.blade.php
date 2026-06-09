<x-layouts.app title="Nueva Contraseña">
    <div class="card">
        <h2 style="font-size:20px;font-weight:700;margin-bottom:4px;">Restablecer Contraseña</h2>
        <p style="color:#64748b;font-size:14px;margin-bottom:20px;">
            Ingresá tu correo y tu nueva contraseña para recuperar el acceso.
        </p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email">Confirmá tu Correo Electrónico</label>
                <input type="email" name="email" id="email" value="{{ request('email') }}" required autofocus placeholder="Ej: admin@cup.test">
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password">Nueva Contraseña</label>
                <input type="password" name="password" id="password" required placeholder="Mínimo 8 caracteres">
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="password_confirmation">Confirmá tu Nueva Contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>

            <div style="margin-top:24px;display:flex;justify-content:flex-end;">
                <button type="submit" style="width:100%;">Restablecer Contraseña</button>
            </div>
        </form>
    </div>
</x-layouts.app>
