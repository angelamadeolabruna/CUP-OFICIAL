<x-layouts.app title="Iniciar sesión">
    <div class="bg-white rounded-2xl shadow-[0_8px_40px_rgba(0,0,0,0.12)] px-8 py-9">
        <h2 class="text-[22px] font-extrabold text-blue-institucional tracking-tight">Iniciar sesión</h2>
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
                    class="w-full mt-2 px-5 py-3 bg-guindo text-white text-sm font-bold rounded-xl transition-all duration-[0.18s] border-0 cursor-pointer hover:bg-guindo-light active:scale-[0.97]">Ingresar</button>

            <div class="text-center mt-4">
                <a href="{{ route('password.request') }}" class="text-sm text-slate-500 no-underline font-medium transition-colors duration-[0.18s] hover:text-guindo">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>
</x-layouts.app>
