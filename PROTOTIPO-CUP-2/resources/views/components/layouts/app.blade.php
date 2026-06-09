<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sistema CUP FICCT' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .nav::-webkit-scrollbar { width: 4px; }
        .nav::-webkit-scrollbar-track { background: transparent; }
        .nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 4px; }
        .metric::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; border-radius: 12px 0 0 12px; }
        .metric:nth-child(1)::before { background: #7B1818; }
        .metric:nth-child(2)::before { background: #0a2a5e; }
        .metric:nth-child(3)::before { background: #7B1818; }
        .metric:nth-child(4)::before { background: #0a2a5e; }
        .metric:nth-child(5)::before { background: #7B1818; }
        .metric:nth-child(6)::before { background: #0a2a5e; }
        @keyframes dropdownIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        .login-brand::before { content: ''; position: absolute; top: -30%; right: -20%; width: 500px; height: 500px; border-radius: 50%; background: rgba(255,255,255,.03); }
        .login-brand::after { content: ''; position: absolute; bottom: -20%; left: -10%; width: 400px; height: 400px; border-radius: 50%; background: rgba(123,24,24,.1); }
        .table th:first-child { border-radius: 8px 0 0 0; }
        .table th:last-child { border-radius: 0 8px 0 0; }
        .table tr:last-child td:first-child { border-radius: 0 0 0 8px; }
        .table tr:last-child td:last-child { border-radius: 0 0 8px 0; }
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; }
        .filter-field { width: 250px; }
        @media (max-width: 600px) { .filter-field { width: 100%; } }
        @media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } .grid-2-4 { grid-template-columns: 1fr; } }
        @media (max-width: 480px) { .table th, .table td { padding: 8px 10px; font-size: 12px; } }
    </style>
</head>
<body class="bg-[#f1f5f9] text-[#0f172a] antialiased min-h-screen font-['Inter','Segoe_UI',Arial,sans-serif]">
    @auth
        <div class="flex min-h-screen">
            <aside class="w-[270px] bg-gradient-to-b from-blue-institucional to-blue-dark text-white flex flex-col fixed left-0 top-0 bottom-0 z-[100] transition-all duration-300 border-r border-r-white/6">
                <div class="px-6 py-6 pb-5 border-b border-b-white/8 flex-shrink-0 flex items-center gap-3.5">
                    <div class="w-10 h-10 flex-shrink-0 rounded-xl overflow-hidden">
                        <img src="{{ asset('logo-ficct.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="brand-text">
                        <h2 class="text-lg font-extrabold tracking-tight text-white leading-tight">CUP FICCT</h2>
                        <p class="text-[10px] text-white/50 font-medium">UAGRM — Admisiones</p>
                    </div>
                </div>
                <nav class="nav py-3 flex-1 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('dashboard')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                        <span class="icon text-[17px] w-6 text-center flex-shrink-0">📊</span>
                        <span>Dashboard</span>
                    </a>

                    @if(auth()->user()->rol?->nombre_rol === 'administrador')
                        <div class="px-6 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-[1.4px] text-white/30">Administración</div>
                        <a href="{{ route('admin.usuarios.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('admin.usuarios.*') && !request()->routeIs('admin.usuarios.importar*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">👥</span>
                            <span>Gestionar Cuentas</span>
                        </a>
                        <a href="{{ route('admin.usuarios.importar') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('admin.usuarios.importar*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📥</span>
                            <span>Importar Cuentas</span>
                        </a>
                        <a href="{{ route('admin.pagos.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('admin.pagos.*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">💳</span>
                            <span>Verificar Pagos</span>
                        </a>
                        <a href="{{ route('academico.postulantes.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('academico.postulantes*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">👤</span>
                            <span>Ver Postulantes</span>
                        </a>
                        <a href="{{ route('admin.bitacora.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('admin.bitacora*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📜</span>
                            <span>Bitácora</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->rol?->nombre_rol, ['coordinador_academico', 'administrador']))
                        <div class="px-6 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-[1.4px] text-white/30">Gestión de Docentes</div>
                        <a href="{{ route('docentes.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('docentes.*') && !request()->routeIs('docentes.carga-horaria*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">👨‍🏫</span>
                            <span>Docentes</span>
                        </a>
                        <a href="{{ route('docentes.carga-horaria.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('docentes.carga-horaria*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📅</span>
                            <span>Carga Horaria</span>
                        </a>
                        <a href="{{ route('asistencia.consulta.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('asistencia.consulta*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📊</span>
                            <span>Consultar Asistencia</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->rol?->nombre_rol, ['coordinador_academico', 'administrador']))
                        <div class="px-6 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-[1.4px] text-white/30">Evaluación Académica</div>
                        <a href="{{ route('academico.evaluaciones.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('academico.evaluaciones*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📐</span>
                            <span>Configurar Evaluaciones</span>
                        </a>
                        <a href="{{ route('academico.notas.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('academico.notas*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📝</span>
                            <span>Registrar Notas</span>
                        </a>
                        <a href="{{ route('academico.promedios.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('academico.promedios*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📊</span>
                            <span>Promedios y Resultados</span>
                        </a>
                        <a href="{{ route('academico.admision.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('academico.admision*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">🎯</span>
                            <span>Admisión por Cupos</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->rol?->nombre_rol, ['administrador', 'coordinador_academico']))
                        <div class="px-6 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-[1.4px] text-white/30">Reportes</div>
                        <a href="{{ route('reportes.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('reportes.*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📋</span>
                            <span>Reportes Obligatorios</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->rol?->nombre_rol, ['coordinador_academico', 'administrador']))
                        <div class="px-6 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-[1.4px] text-white/30">Organización Logística</div>
                        <a href="{{ route('logistica.capacidad.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('logistica.capacidad.*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📏</span>
                            <span>Capacidad de Aula</span>
                        </a>
                        <a href="{{ route('logistica.grupos.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('logistica.grupos.*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">🧮</span>
                            <span>Calcular Grupos</span>
                        </a>
                        <a href="{{ route('logistica.asignar.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('logistica.asignar.*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📋</span>
                            <span>Asignar Grupos</span>
                        </a>
                        <a href="{{ route('logistica.aulas.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('logistica.aulas.*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">🏛️</span>
                            <span>Aulas</span>
                        </a>
                        <a href="{{ route('logistica.horarios.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('logistica.horarios.*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">🕐</span>
                            <span>Horarios</span>
                        </a>
                    @endif

                    @if(auth()->user()->rol?->nombre_rol === 'docente')
                        <div class="px-6 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-[1.4px] text-white/30">Docencia</div>
                        <a href="{{ route('docentes.mi-carga-horaria.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('docentes.mi-carga-horaria*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📚</span>
                            <span>Mi Carga Horaria</span>
                        </a>
                        <a href="{{ route('academico.notas.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('academico.notas*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📝</span>
                            <span>Registrar Notas</span>
                        </a>
                        <a href="{{ route('docentes.asistencia.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('docentes.asistencia*') && !request()->routeIs('asistencia.consulta*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">✅</span>
                            <span>Registrar Asistencia</span>
                        </a>
                        <a href="{{ route('asistencia.consulta.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('asistencia.consulta*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📊</span>
                            <span>Consultar Asistencia</span>
                        </a>
                        <a href="{{ route('academico.promedios.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('academico.promedios*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📊</span>
                            <span>Promedios y Resultados</span>
                        </a>
                    @endif

                    @if(auth()->user()->rol?->nombre_rol === 'prepostulante')
                        <div class="px-6 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-[1.4px] text-white/30">Mi Postulación</div>
                        <a href="#" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📋</span>
                            <span>Requisitos</span>
                        </a>
                        <a href="{{ route('prepostulante.pagos.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">💳</span>
                            <span>Pago</span>
                        </a>
                        <a href="{{ route('prepostulante.registro.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('prepostulante.registro.*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">✏️</span>
                            <span>Completar Registro</span>
                        </a>
                    @endif

                    @if(auth()->user()->rol?->nombre_rol === 'postulante_oficial')
                        <div class="px-6 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-[1.4px] text-white/30">Mi Información</div>
                        <a href="{{ route('asistencia.consulta.index') }}" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85 @if(request()->routeIs('asistencia.consulta*')) !text-white !border-l-guindo !bg-[linear-gradient(90deg,rgba(123,24,24,0.2)_0%,transparent_100%)] @endif">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">✅</span>
                            <span>Mi Asistencia</span>
                        </a>
                        <a href="#" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">📖</span>
                            <span>Notas</span>
                        </a>
                        <a href="#" class="flex items-center gap-3.5 px-6 py-2.5 text-white/55 no-underline text-sm font-medium transition-all duration-[0.18s] border-l-3 border-transparent my-px hover:bg-white/6 hover:text-white/85">
                            <span class="icon text-[17px] w-6 text-center flex-shrink-0">🎯</span>
                            <span>Resultado Final</span>
                        </a>
                    @endif
                </nav>
                <div class="px-6 py-4 border-t border-t-white/6 text-[11px] text-white/30 flex-shrink-0 font-medium">© CUP FICCT {{ date('Y') }}</div>
            </aside>

            <div class="ml-[270px] flex-1 min-h-screen flex flex-col">
                <header class="bg-white/85 backdrop-blur-md border-b-2 border-b-guindo h-[68px] flex items-center justify-end px-8 flex-shrink-0 gap-3 sticky top-0 z-50">
                    <div class="relative group">
                        <button class="flex items-center gap-3 bg-slate-50 border border-slate-200 rounded-xl px-4 py-1.5 pr-4 pl-2 cursor-pointer text-sm text-slate-900 transition-all duration-[0.18s] hover:bg-slate-100 hover:border-slate-300 hover:shadow-sm">
                            <div class="w-[34px] h-[34px] rounded-full bg-gradient-to-br from-blue-institucional to-guindo text-white flex items-center justify-center font-bold text-sm flex-shrink-0">{{ strtoupper(substr(auth()->user()->nombre_usuario, 0, 1)) }}</div>
                            <div class="text-left">
                                <div class="font-semibold text-xs leading-tight text-slate-900">{{ auth()->user()->nombre_usuario }}</div>
                                <div class="text-[11px] text-slate-500 font-medium">{{ auth()->user()->rol?->nombre_rol }}</div>
                            </div>
                            <span class="text-[10px] text-slate-400">▼</span>
                        </button>
                        <div class="hidden group-hover:block absolute right-0 top-[calc(100%+8px)] bg-white border border-slate-200 rounded-xl shadow-[0_12px_40px_rgba(0,0,0,0.1)] min-w-[220px] z-[200] overflow-hidden animate-[dropdownIn_0.15s_ease]">
                            <a href="{{ route('perfil.password.edit') }}" class="flex items-center gap-3 px-4.5 py-3 text-slate-900 no-underline text-sm font-medium transition-all duration-[0.1s] border-0 bg-transparent w-full text-left cursor-pointer hover:bg-slate-100">
                                <span>🔑</span> Cambiar Contraseña
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 px-4.5 py-3 text-guindo no-underline text-sm font-medium transition-all duration-[0.1s] border-0 bg-transparent w-full text-left cursor-pointer hover:bg-red-50">
                                    <span>🚪</span> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="p-8 flex-1">
                    @if (session('status'))
                        <div class="text-[#059669] text-sm font-medium mb-4 px-4.5 py-3 bg-[#ecfdf5] border border-[#a7f3d0] rounded-xl">{{ session('status') }}</div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
    @else
        <div class="min-h-screen flex bg-blue-institucional">
            <div class="flex-1 flex flex-col items-center justify-center p-10 relative overflow-hidden">
                <div class="logo-img w-[140px] h-[140px] relative z-[1] mb-6">
                    <img src="{{ asset('logo-ficct.png') }}" alt="Logo FICCT" class="w-full h-full object-contain drop-shadow-[0_4px_20px_rgba(0,0,0,0.2)]">
                </div>
                <h1 class="text-[28px] font-extrabold text-white tracking-tight relative z-[1] text-center">Facultad de Ingeniería<br>en Ciencias de la Computación</h1>
                <div class="w-[60px] h-[3px] bg-guindo rounded-[4px] my-4 relative z-[1]"></div>
                <p class="text-sm text-white/60 mt-1.5 relative z-[1] text-center max-w-[320px]">Sistema de Admisiones — CUP FICCT · UAGRM</p>
                <div class="absolute bottom-[30px] text-[11px] text-white/30 z-[1]">© {{ date('Y') }} UAGRM — Todos los derechos reservados</div>
            </div>
            <div class="w-[480px] min-w-[480px] bg-white flex items-center justify-center p-10">
                <div class="w-full max-w-[380px]">
                    @if (session('status'))
                        <div class="text-[#059669] text-sm font-medium mb-4 px-4.5 py-3 bg-[#ecfdf5] border border-[#a7f3d0] rounded-xl">{{ session('status') }}</div>
                    @endif
                    {{ $slot }}
                </div>
            </div>
        </div>
    @endauth
</body>
</html>
