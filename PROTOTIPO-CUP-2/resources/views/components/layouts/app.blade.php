<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sistema CUP FICCT' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; color: #1f2937; min-height: 100vh; }

        .layout { display: flex; min-height: 100vh; }

        .sidebar { width: 260px; background: #0f172a; color: #fff; display: flex; flex-direction: column; position: fixed; left: 0; top: 0; bottom: 0; z-index: 100; transition: width .25s; }
        .sidebar-header { padding: 22px 20px 18px; border-bottom: 1px solid rgba(255,255,255,.08); flex-shrink: 0; }
        .sidebar-header h2 { font-size: 18px; font-weight: 800; letter-spacing: -.3px; }
        .sidebar-header p { font-size: 11px; color: #64748b; margin-top: 1px; letter-spacing: .5px; }
        .nav { padding: 10px 0; flex: 1; overflow-y: auto; }
        .nav-section { padding: 18px 20px 5px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #475569; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 20px; color: #94a3b8; text-decoration: none; font-size: 14px; transition: all .15s; border-left: 3px solid transparent; }
        .nav-item:hover { background: rgba(255,255,255,.04); color: #e2e8f0; }
        .nav-item.active { background: rgba(59,130,246,.12); color: #60a5fa; border-left-color: #3b82f6; }
        .nav-item .icon { font-size: 16px; width: 22px; text-align: center; flex-shrink: 0; }
        .sidebar-footer { padding: 14px 20px; border-top: 1px solid rgba(255,255,255,.08); font-size: 11px; color: #475569; flex-shrink: 0; }

        .main-area { margin-left: 260px; flex: 1; min-height: 100vh; display: flex; flex-direction: column; }

        .topbar { background: #fff; border-bottom: 1px solid #e2e8f0; height: 64px; display: flex; align-items: center; justify-content: flex-end; padding: 0 28px; flex-shrink: 0; gap: 12px; }
        .user-dropdown { position: relative; }
        .user-dropdown-btn { display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 7px 14px 7px 10px; cursor: pointer; font-size: 14px; color: #1f2937; transition: .15s; }
        .user-dropdown-btn:hover { background: #f1f5f9; }
        .user-avatar { width: 32px; height: 32px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; }
        .user-info { text-align: left; }
        .user-info .name { font-weight: 600; font-size: 13px; line-height: 1.2; }
        .user-info .role { font-size: 11px; color: #64748b; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: calc(100% + 6px); background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,.1); min-width: 210px; z-index: 200; overflow: hidden; }
        .user-dropdown:hover .dropdown-menu { display: block; }
        .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 11px 16px; color: #1f2937; text-decoration: none; font-size: 13px; transition: .1s; border: 0; background: none; width: 100%; text-align: left; cursor: pointer; }
        .dropdown-item:hover { background: #f1f5f9; }
        .dropdown-item.danger { color: #dc2626; }
        .dropdown-item.danger:hover { background: #fef2f2; }
        .dropdown-divider { border-top: 1px solid #f1f5f9; }

        .content { padding: 28px; flex: 1; }

        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; }
        .metric { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; }
        .metric .label { font-size: 13px; color: #64748b; font-weight: 500; }
        .metric strong { display: block; font-size: 30px; margin-top: 4px; color: #0f172a; }

        label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; color: #374151; }
        input, select, textarea { width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px; padding: 9px 12px; margin-bottom: 14px; font-size: 14px; font-family: inherit; }
        input:focus, select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        button, .button { background: #2563eb; color: #fff; border: 0; border-radius: 6px; padding: 10px 18px; font-weight: 600; font-size: 14px; cursor: pointer; text-decoration: none; display: inline-block; transition: .12s; font-family: inherit; }
        button:hover, .button:hover { background: #1d4ed8; }
        .button-secondary { background: #fff; color: #374151; border: 1px solid #d1d5db; }
        .button-secondary:hover { background: #f9fafb; }
        .button-ghost { background: transparent; color: #64748b; padding: 6px 12px; }
        .button-ghost:hover { background: #f1f5f9; }
        .button-sm { padding: 6px 12px; font-size: 13px; }
        .button-danger { background: #dc2626; }
        .button-danger:hover { background: #b91c1c; }
        .error { color: #dc2626; font-size: 13px; margin-bottom: 8px; }
        .status { color: #059669; font-size: 14px; font-weight: 500; margin-bottom: 12px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; }

        .table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .table th { text-align: left; padding: 10px 12px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-weight: 600; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
        .table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .table tr:hover td { background: #f8fafc; }

        .badge { font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; display: inline-block; }
        .badge-activo { background: #dcfce7; color: #166534; }
        .badge-inactivo { background: #fee2e2; color: #991b1b; }
        .badge-aprobado { background: #dcfce7; color: #166534; }
        .badge-reprobado { background: #fee2e2; color: #991b1b; }

        .page-title { font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .page-desc { color: #64748b; font-size: 14px; margin-bottom: 20px; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .flex { display: flex; gap: 8px; align-items: center; }

        @media (max-width: 768px) {
            .sidebar { width: 64px; }
            .sidebar-header h2, .sidebar-header p, .nav-item span, .nav-section, .sidebar-footer { display: none; }
            .nav-item { justify-content: center; padding: 14px; }
            .main-area { margin-left: 64px; }
            .topbar { padding: 0 14px; }
            .content { padding: 16px; }
            .user-info { display: none; }
        }
    </style>
</head>
<body>
    @auth
        <div class="layout">
            <aside class="sidebar">
                <div class="sidebar-header">
                    <h2>CUP FICCT</h2>
                    <p>UAGRM — Admisiones</p>
                </div>
                <nav class="nav">
                    <a href="{{ route('dashboard') }}" class="nav-item @if(request()->routeIs('dashboard')) active @endif">
                        <span class="icon">📊</span>
                        <span>Dashboard</span>
                    </a>

                    @if(auth()->user()->rol?->nombre_rol === 'administrador')
                        <div class="nav-section">Administración</div>
                        <a href="{{ route('admin.usuarios.index') }}" class="nav-item @if(request()->routeIs('admin.usuarios.*') && !request()->routeIs('admin.usuarios.importar*')) active @endif">
                            <span class="icon">👥</span>
                            <span>Gestionar Cuentas</span>
                        </a>
                        <a href="{{ route('admin.usuarios.importar') }}" class="nav-item @if(request()->routeIs('admin.usuarios.importar*')) active @endif">
                            <span class="icon">📥</span>
                            <span>Importar Cuentas</span>
                        </a>
                        <a href="{{ route('admin.pagos.index') }}" class="nav-item @if(request()->routeIs('admin.pagos.*')) active @endif">
                            <span class="icon">💳</span>
                            <span>Verificar Pagos</span>
                        </a>
                        <a href="{{ route('academico.postulantes.index') }}" class="nav-item @if(request()->routeIs('academico.postulantes*')) active @endif">
                            <span class="icon">👤</span>
                            <span>Ver Postulantes</span>
                        </a>
                        <a href="{{ route('admin.bitacora.index') }}" class="nav-item @if(request()->routeIs('admin.bitacora*')) active @endif">
                            <span class="icon">📜</span>
                            <span>Bitácora</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->rol?->nombre_rol, ['coordinador_academico', 'administrador']))
                        <div class="nav-section">Gestión de Docentes</div>
                        <a href="{{ route('docentes.index') }}" class="nav-item @if(request()->routeIs('docentes.*') && !request()->routeIs('docentes.carga-horaria*')) active @endif">
                            <span class="icon">👨‍🏫</span>
                            <span>Docentes</span>
                        </a>
                        <a href="{{ route('docentes.carga-horaria.index') }}" class="nav-item @if(request()->routeIs('docentes.carga-horaria*')) active @endif">
                            <span class="icon">📅</span>
                            <span>Carga Horaria</span>
                        </a>
                        <a href="{{ route('asistencia.consulta.index') }}" class="nav-item @if(request()->routeIs('asistencia.consulta*')) active @endif">
                            <span class="icon">📊</span>
                            <span>Consultar Asistencia</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->rol?->nombre_rol, ['coordinador_academico', 'administrador']))
                        <div class="nav-section">Evaluación Académica</div>
                        <a href="{{ route('academico.evaluaciones.index') }}" class="nav-item @if(request()->routeIs('academico.evaluaciones*')) active @endif">
                            <span class="icon">📐</span>
                            <span>Configurar Evaluaciones</span>
                        </a>
                        <a href="{{ route('academico.notas.index') }}" class="nav-item @if(request()->routeIs('academico.notas*')) active @endif">
                            <span class="icon">📝</span>
                            <span>Registrar Notas</span>
                        </a>
                        <a href="{{ route('academico.promedios.index') }}" class="nav-item @if(request()->routeIs('academico.promedios*')) active @endif">
                            <span class="icon">📊</span>
                            <span>Promedios y Resultados</span>
                        </a>
                        <a href="{{ route('academico.admision.index') }}" class="nav-item @if(request()->routeIs('academico.admision*')) active @endif">
                            <span class="icon">🎯</span>
                            <span>Admisión por Cupos</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->rol?->nombre_rol, ['administrador', 'coordinador_academico']))
                        <div class="nav-section">Reportes</div>
                        <a href="{{ route('reportes.index') }}" class="nav-item @if(request()->routeIs('reportes.*')) active @endif">
                            <span class="icon">📋</span>
                            <span>Reportes Obligatorios</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->rol?->nombre_rol, ['coordinador_academico', 'administrador']))
                        <div class="nav-section">Organización Logística</div>
                        <a href="{{ route('logistica.capacidad.index') }}" class="nav-item @if(request()->routeIs('logistica.capacidad.*')) active @endif">
                            <span class="icon">📏</span>
                            <span>Capacidad de Aula</span>
                        </a>
                        <a href="{{ route('logistica.grupos.index') }}" class="nav-item @if(request()->routeIs('logistica.grupos.*')) active @endif">
                            <span class="icon">🧮</span>
                            <span>Calcular Grupos</span>
                        </a>
                        <a href="{{ route('logistica.asignar.index') }}" class="nav-item @if(request()->routeIs('logistica.asignar.*')) active @endif">
                            <span class="icon">📋</span>
                            <span>Asignar Grupos</span>
                        </a>
                        <a href="{{ route('logistica.aulas.index') }}" class="nav-item @if(request()->routeIs('logistica.aulas.*')) active @endif">
                            <span class="icon">🏛️</span>
                            <span>Aulas</span>
                        </a>
                        <a href="{{ route('logistica.horarios.index') }}" class="nav-item @if(request()->routeIs('logistica.horarios.*')) active @endif">
                            <span class="icon">🕐</span>
                            <span>Horarios</span>
                        </a>
                    @endif

                    @if(auth()->user()->rol?->nombre_rol === 'docente')
                        <div class="nav-section">Docencia</div>
                        <a href="{{ route('docentes.mi-carga-horaria.index') }}" class="nav-item @if(request()->routeIs('docentes.mi-carga-horaria*')) active @endif">
                            <span class="icon">📚</span>
                            <span>Mi Carga Horaria</span>
                        </a>
                        <a href="{{ route('academico.notas.index') }}" class="nav-item @if(request()->routeIs('academico.notas*')) active @endif">
                            <span class="icon">📝</span>
                            <span>Registrar Notas</span>
                        </a>
                        <a href="{{ route('docentes.asistencia.index') }}" class="nav-item @if(request()->routeIs('docentes.asistencia*') && !request()->routeIs('asistencia.consulta*')) active @endif">
                            <span class="icon">✅</span>
                            <span>Registrar Asistencia</span>
                        </a>
                        <a href="{{ route('asistencia.consulta.index') }}" class="nav-item @if(request()->routeIs('asistencia.consulta*')) active @endif">
                            <span class="icon">📊</span>
                            <span>Consultar Asistencia</span>
                        </a>
                        <a href="{{ route('academico.promedios.index') }}" class="nav-item @if(request()->routeIs('academico.promedios*')) active @endif">
                            <span class="icon">📊</span>
                            <span>Promedios y Resultados</span>
                        </a>
                    @endif

                    @if(auth()->user()->rol?->nombre_rol === 'prepostulante')
                        <div class="nav-section">Mi Postulación</div>
                        <a href="#" class="nav-item">
                            <span class="icon">📋</span>
                            <span>Requisitos</span>
                        </a>
                        <a href="{{ route('prepostulante.pagos.index') }}" class="nav-item">
                            <span class="icon">💳</span>
                            <span>Pago</span>
                        </a>
                        <a href="{{ route('prepostulante.registro.index') }}" class="nav-item @if(request()->routeIs('prepostulante.registro.*')) active @endif">
                            <span class="icon">✏️</span>
                            <span>Completar Registro</span>
                        </a>
                    @endif

                    @if(auth()->user()->rol?->nombre_rol === 'postulante_oficial')
                        <div class="nav-section">Mi Información</div>
                        <a href="{{ route('asistencia.consulta.index') }}" class="nav-item @if(request()->routeIs('asistencia.consulta*')) active @endif">
                            <span class="icon">✅</span>
                            <span>Mi Asistencia</span>
                        </a>
                        <a href="#" class="nav-item">
                            <span class="icon">📖</span>
                            <span>Notas</span>
                        </a>
                        <a href="#" class="nav-item">
                            <span class="icon">🎯</span>
                            <span>Resultado Final</span>
                        </a>
                    @endif
                </nav>
                <div class="sidebar-footer">© CUP FICCT {{ date('Y') }}</div>
            </aside>

            <div class="main-area">
                <header class="topbar">
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->nombre_usuario, 0, 1)) }}</div>
                            <div class="user-info">
                                <div class="name">{{ auth()->user()->nombre_usuario }}</div>
                                <div class="role">{{ auth()->user()->rol?->nombre_rol }}</div>
                            </div>
                            <span style="font-size:10px;color:#94a3b8;">▼</span>
                        </button>
                        <div class="dropdown-menu">
                            <a href="{{ route('perfil.password.edit') }}" class="dropdown-item">
                                <span>🔑</span> Cambiar Contraseña
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item danger">
                                    <span>🚪</span> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="content">
                    @if (session('status'))
                        <div class="status">{{ session('status') }}</div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
    @else
        <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f0f2f5;padding:20px;">
            <div style="width:100%;max-width:440px;">
                <div style="text-align:center;margin-bottom:28px;">
                    <h1 style="font-size:24px;color:#0f172a;font-weight:800;">CUP FICCT</h1>
                    <p style="color:#64748b;font-size:14px;margin-top:4px;">Sistema de Admisiones — UAGRM</p>
                </div>
                @if (session('status'))
                    <div class="status">{{ session('status') }}</div>
                @endif
                {{ $slot }}
            </div>
        </div>
    @endauth
</body>
</html>
