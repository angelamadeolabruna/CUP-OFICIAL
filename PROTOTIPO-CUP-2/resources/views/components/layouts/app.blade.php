<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sistema CUP FICCT' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        :root {
            --blue: #0a2a5e;
            --blue-dark: #071d42;
            --blue-light: #0f3d7a;
            --guindo: #7B1818;
            --guindo-light: #9f1d1d;
            --guindo-dark: #5c1111;
            --white: #ffffff;
            --bg: #f1f5f9;
            --text: #0f172a;
            --text-secondary: #64748b;
            --border: #e2e8f0;
        }

        /* ===== LAYOUT ===== */
        .layout { display: flex; min-height: 100vh; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 270px;
            background: linear-gradient(180deg, var(--blue) 0%, var(--blue-dark) 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 100;
            transition: width .3s ease;
            border-right: 1px solid rgba(255,255,255,.06);
        }
        .sidebar-header {
            padding: 24px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .sidebar-header .mini-logo {
            width: 40px; height: 40px;
            flex-shrink: 0;
            border-radius: 10px;
            overflow: hidden;
        }
        .sidebar-header .mini-logo img {
            width: 100%; height: 100%;
            object-fit: contain;
        }
        .sidebar-header .brand-text h2 {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -.3px;
            color: #fff;
            line-height: 1.2;
        }
        .sidebar-header .brand-text p { font-size: 10px; color: rgba(255,255,255,.5); font-weight: 500; }
        .nav { padding: 12px 0; flex: 1; overflow-y: auto; }
        .nav::-webkit-scrollbar { width: 4px; }
        .nav::-webkit-scrollbar-track { background: transparent; }
        .nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 4px; }
        .nav-section {
            padding: 20px 24px 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            color: rgba(255,255,255,.3);
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 24px;
            color: rgba(255,255,255,.55);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all .18s ease;
            border-left: 3px solid transparent;
            margin: 1px 0;
        }
        .nav-item:hover {
            background: rgba(255,255,255,.06);
            color: rgba(255,255,255,.85);
        }
        .nav-item.active {
            background: linear-gradient(90deg, rgba(123,24,24,.2) 0%, transparent 100%);
            color: #fff;
            border-left-color: var(--guindo);
        }
        .nav-item .icon { font-size: 17px; width: 24px; text-align: center; flex-shrink: 0; }
        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,.06);
            font-size: 11px;
            color: rgba(255,255,255,.3);
            flex-shrink: 0;
            font-weight: 500;
        }

        /* ===== MAIN AREA ===== */
        .main-area {
            margin-left: 270px;
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 2px solid var(--guindo);
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 32px;
            flex-shrink: 0;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .user-dropdown { position: relative; }
        .user-dropdown-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 6px 16px 6px 8px;
            cursor: pointer;
            font-size: 14px;
            color: var(--text);
            transition: all .18s ease;
        }
        .user-dropdown-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--guindo));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        .user-info { text-align: left; }
        .user-info .name { font-weight: 600; font-size: 13px; line-height: 1.3; color: var(--text); }
        .user-info .role { font-size: 11px; color: var(--text-secondary); font-weight: 500; }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0,0,0,.1);
            min-width: 220px;
            z-index: 200;
            overflow: hidden;
            animation: dropdownIn .15s ease;
        }
        @keyframes dropdownIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .user-dropdown:hover .dropdown-menu { display: block; }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all .1s ease;
            border: 0;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }
        .dropdown-item:hover { background: #f1f5f9; }
        .dropdown-item.danger { color: var(--guindo); }
        .dropdown-item.danger:hover { background: #fef2f2; }
        .dropdown-divider { border-top: 1px solid #f1f5f9; margin: 4px 0; }

        /* ===== CONTENT ===== */
        .content { padding: 32px; flex: 1; }

        /* ===== CARDS ===== */
        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.02);
            transition: box-shadow .2s ease;
        }
        .card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }
        .metric {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 22px;
            transition: all .2s ease;
            position: relative;
            overflow: hidden;
        }
        .metric::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 4px;
            height: 100%;
            border-radius: 12px 0 0 12px;
        }
        .metric:nth-child(1)::before { background: var(--guindo); }
        .metric:nth-child(2)::before { background: var(--blue); }
        .metric:nth-child(3)::before { background: var(--guindo); }
        .metric:nth-child(4)::before { background: var(--blue); }
        .metric:nth-child(5)::before { background: var(--guindo); }
        .metric:nth-child(6)::before { background: var(--blue); }
        .metric:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
            transform: translateY(-2px);
        }
        .metric .label { font-size: 13px; color: var(--text-secondary); font-weight: 500; }
        .metric strong {
            display: block;
            font-size: 32px;
            margin-top: 6px;
            color: var(--text);
            font-weight: 800;
            letter-spacing: -.5px;
        }

        /* ===== FORMS ===== */
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 13px;
            color: #374151;
        }
        input, select, textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 14px;
            font-family: inherit;
            transition: all .15s ease;
            background: #fff;
            color: var(--text);
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(10,42,94,.1);
        }
        input::placeholder { color: #94a3b8; }

        /* ===== BUTTONS ===== */
        button, .button {
            background: var(--blue);
            color: #fff;
            border: 0;
            border-radius: 8px;
            padding: 10px 22px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .18s ease;
            font-family: inherit;
        }
        button:hover, .button:hover {
            background: var(--blue-light);
            box-shadow: 0 4px 12px rgba(10,42,94,.3);
            transform: translateY(-1px);
        }
        button:active, .button:active { transform: translateY(0); }
        .button-secondary {
            background: #fff;
            color: var(--blue);
            border: 1.5px solid var(--blue);
        }
        .button-secondary:hover {
            background: #f8fafc;
            box-shadow: 0 2px 8px rgba(10,42,94,.1);
        }
        .button-ghost {
            background: transparent;
            color: var(--text-secondary);
            padding: 8px 16px;
        }
        .button-ghost:hover {
            background: #f1f5f9;
            color: var(--text);
        }
        .button-sm { padding: 7px 14px; font-size: 13px; }
        .button-danger {
            background: var(--guindo);
        }
        .button-danger:hover {
            background: var(--guindo-light);
            box-shadow: 0 4px 12px rgba(123,24,24,.3);
        }

        /* ===== ALERTS ===== */
        .error {
            color: var(--guindo);
            font-size: 13px;
            margin-bottom: 10px;
            padding: 10px 14px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            font-weight: 500;
        }
        .status {
            color: #059669;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 16px;
            padding: 12px 18px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
        }

        /* ===== TABLES ===== */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
        }
        .table-container .card { padding: 0; overflow: hidden; }
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;
            min-width: 600px;
        }
        .table th {
            text-align: left;
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
            font-weight: 600;
            color: var(--blue);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .6px;
        }
        .table th:first-child { border-radius: 8px 0 0 0; }
        .table th:last-child { border-radius: 0 8px 0 0; }
        .table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
        }
        .table tr:hover td { background: #f8fafc; }
        .table tr:last-child td:first-child { border-radius: 0 0 0 8px; }
        .table tr:last-child td:last-child { border-radius: 0 0 8px 0; }

        /* ===== BADGES ===== */
        .badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            display: inline-block;
            letter-spacing: .3px;
        }
        .badge-activo { background: #dcfce7; color: #166534; }
        .badge-inactivo { background: #fee2e2; color: #991b1b; }
        .badge-aprobado { background: #dcfce7; color: #166534; }
        .badge-reprobado { background: #fee2e2; color: #991b1b; }

        /* ===== TYPOGRAPHY ===== */
        .page-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--blue);
            margin-bottom: 4px;
            letter-spacing: -.3px;
        }
        .page-desc {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 24px;
            font-weight: 400;
        }

        /* ===== UTILITIES ===== */
        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .flex { display: flex; gap: 10px; align-items: center; }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .grid-2-4 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .filter-field { width: 250px; }
        @media (max-width: 600px) {
            .filter-field { width: 100%; }
        }

        /* ===== LOGIN ===== */
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            background: var(--blue);
        }
        .login-brand {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        .login-brand::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,.03);
        }
        .login-brand::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(123,24,24,.1);
        }
        .login-brand .logo-img {
            width: 140px;
            height: 140px;
            position: relative;
            z-index: 1;
            margin-bottom: 24px;
        }
        .login-brand .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 4px 20px rgba(0,0,0,.2));
        }
        .login-brand h1 {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.5px;
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .login-brand p {
            font-size: 14px;
            color: rgba(255,255,255,.6);
            margin-top: 6px;
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 320px;
        }
        .login-brand .accent-line {
            width: 60px;
            height: 3px;
            background: var(--guindo);
            border-radius: 4px;
            margin: 16px auto;
            position: relative;
            z-index: 1;
        }
        .login-form-side {
            width: 480px;
            min-width: 480px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .login-box {
            width: 100%;
            max-width: 380px;
        }
        .login-box h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--blue);
            margin-bottom: 4px;
        }
        .login-box .sub {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 28px;
        }
        .login-box button {
            width: 100%;
            justify-content: center;
            padding: 12px;
            font-size: 15px;
        }
        .login-link {
            text-align: center;
            margin-top: 16px;
        }
        .login-link a {
            color: var(--text-secondary);
            font-size: 13px;
            text-decoration: none;
            transition: color .15s;
        }
        .login-link a:hover { color: var(--blue); }

        .login-brand .footer-text {
            position: absolute;
            bottom: 30px;
            font-size: 11px;
            color: rgba(255,255,255,.3);
            z-index: 1;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .login-brand { display: none; }
            .login-form-side { width: 100%; min-width: unset; }
            .login-wrapper { justify-content: center; }
        }

        @media (max-width: 900px) {
            .grid-2 { grid-template-columns: 1fr; }
            .grid-2-4 { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar { width: 68px; }
            .sidebar-header .brand-text, .nav-item span, .nav-section, .sidebar-footer { display: none; }
            .sidebar-header { padding: 16px; justify-content: center; }
            .sidebar-header .mini-logo { width: 32px; height: 32px; }
            .nav-item { justify-content: center; padding: 14px; gap: 0; }
            .nav-item .icon { margin: 0; }
            .main-area { margin-left: 68px; }
            .topbar { padding: 0 16px; height: 60px; }
            .content { padding: 20px; }
            .user-info { display: none; }
        }

        @media (max-width: 480px) {
            .content { padding: 16px; }
            .card { padding: 20px; }
            .grid { grid-template-columns: 1fr; }
            .grid-2-4 { grid-template-columns: 1fr; }
            .topbar { padding: 0 12px; }
            .table th, .table td { padding: 8px 10px; font-size: 12px; }
        }
    </style>
</head>
<body>
    @auth
        <div class="layout">
            <aside class="sidebar">
                <div class="sidebar-header">
                    <div class="mini-logo">
                        <img src="{{ asset('logo-ficct.png') }}" alt="Logo">
                    </div>
                    <div class="brand-text">
                        <h2>CUP FICCT</h2>
                        <p>UAGRM — Admisiones</p>
                    </div>
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
        <div class="login-wrapper">
            <div class="login-brand">
                <div class="logo-img">
                    <img src="{{ asset('logo-ficct.png') }}" alt="Logo FICCT">
                </div>
                <h1>Facultad de Ingeniería<br>en Ciencias de la Computación</h1>
                <div class="accent-line"></div>
                <p>Sistema de Admisiones — CUP FICCT · UAGRM</p>
                <div class="footer-text">© {{ date('Y') }} UAGRM — Todos los derechos reservados</div>
            </div>
            <div class="login-form-side">
                <div class="login-box">
                    @if (session('status'))
                        <div class="status">{{ session('status') }}</div>
                    @endif
                    {{ $slot }}
                </div>
            </div>
        </div>
    @endauth
</body>
</html>
