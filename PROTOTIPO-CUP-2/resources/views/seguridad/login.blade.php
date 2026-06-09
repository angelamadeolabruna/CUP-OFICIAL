<x-layouts.app title="Iniciar sesión">
    <h2 class="text-[23px] font-extrabold text-blue-institucional tracking-tight mt-20">Iniciar sesión</h2>
    <p class="text-sm text-slate-500 mb-6">Ingresá con tu correo electrónico o CI.</p>

    @if ($errors->any())
        <div class="text-sm font-medium mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
        @csrf
        <div>
            <label for="credencial" class="block text-sm font-semibold text-slate-700 mb-1.5">Correo o CI</label>
            <input id="credencial" name="credencial" value="{{ old('credencial') }}" required autofocus placeholder="Ej: admin@cup.test"
                   class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl text-slate-900 bg-white placeholder:text-slate-400 transition-[border-color,shadow] duration-[0.18s] outline-none focus:border-blue-institucional focus:ring-3 focus:ring-blue-institucional/10">
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña</label>
            <input id="password" name="password" type="password" required placeholder="••••••••"
                   class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl text-slate-900 bg-white placeholder:text-slate-400 transition-[border-color,shadow] duration-[0.18s] outline-none focus:border-blue-institucional focus:ring-3 focus:ring-blue-institucional/10">
        </div>

        <button type="submit"
                class="w-full mt-2 px-5 py-3 bg-blue-institucional text-white text-sm font-bold rounded-xl transition-all duration-[0.18s] border-0 cursor-pointer hover:bg-blue-light active:scale-[0.97]">Ingresar</button>

        <div class="text-center mt-4">
            <a href="{{ route('password.request') }}" class="text-sm text-blue-institucional/70 no-underline font-medium transition-colors duration-[0.18s] hover:text-blue-institucional">¿Olvidaste tu contraseña?</a>
        </div>
    </form>
</x-layouts.app>
