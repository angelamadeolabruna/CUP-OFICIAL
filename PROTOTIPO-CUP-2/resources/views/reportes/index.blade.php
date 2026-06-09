<x-layouts.app title="Reportes">
    <div style="max-width:1200px;">
        <div style="margin-bottom:20px;">
            <h1 class="page-title">Reportes Obligatorios</h1>
            <p class="page-desc">Generá reportes oficiales del proceso de admisión.</p>
        </div>

        @if (!$gestionActiva)
            <div class="card" style="padding:24px;text-align:center;">
                <p style="color:#64748b;">No hay una gestión académica activa.</p>
            </div>
        @else
            <div style="margin-bottom:20px;padding:12px 16px;background:#f8fafc;border-radius:6px;font-size:13px;">
                <strong>Gestión activa:</strong> {{ $gestionActiva->nombre_gestion }}
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
                <a href="{{ route('reportes.postulantes') }}" class="card" style="display:block;text-decoration:none;color:inherit;transition:box-shadow .15s;cursor:pointer;">
                    <div style="font-size:28px;margin-bottom:8px;">👤</div>
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Postulantes</h3>
                    <p style="font-size:13px;color:#64748b;">Listado completo de postulantes con datos personales y carreras elegidas.</p>
                    <div style="margin-top:12px;font-size:12px;color:#2563eb;font-weight:600;">Generar →</div>
                </a>

                <a href="{{ route('reportes.resultados-academicos') }}" class="card" style="display:block;text-decoration:none;color:inherit;transition:box-shadow .15s;cursor:pointer;">
                    <div style="font-size:28px;margin-bottom:8px;">✅</div>
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Resultados Académicos</h3>
                    <p style="font-size:13px;color:#64748b;">Postulantes aprobados y reprobados, con filtro por estado.</p>
                    <div style="margin-top:12px;font-size:12px;color:#2563eb;font-weight:600;">Generar →</div>
                </a>

                <a href="{{ route('reportes.promedios') }}" class="card" style="display:block;text-decoration:none;color:inherit;transition:box-shadow .15s;cursor:pointer;">
                    <div style="font-size:28px;margin-bottom:8px;">📊</div>
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Promedios por Materia</h3>
                    <p style="font-size:13px;color:#64748b;">Promedios de cada postulante desglosados por materia y evaluación.</p>
                    <div style="margin-top:12px;font-size:12px;color:#2563eb;font-weight:600;">Generar →</div>
                </a>

                <a href="{{ route('reportes.grupos') }}" class="card" style="display:block;text-decoration:none;color:inherit;transition:box-shadow .15s;cursor:pointer;">
                    <div style="font-size:28px;margin-bottom:8px;">🧮</div>
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Grupos</h3>
                    <p style="font-size:13px;color:#64748b;">Grupos con sus aulas, horarios y postulantes asignados.</p>
                    <div style="margin-top:12px;font-size:12px;color:#2563eb;font-weight:600;">Generar →</div>
                </a>

                <a href="{{ route('reportes.estadisticas-materia') }}" class="card" style="display:block;text-decoration:none;color:inherit;transition:box-shadow .15s;cursor:pointer;">
                    <div style="font-size:28px;margin-bottom:8px;">📈</div>
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Estadísticas por Materia</h3>
                    <p style="font-size:13px;color:#64748b;">Promedio general, cantidad de aprobados y reprobados por materia.</p>
                    <div style="margin-top:12px;font-size:12px;color:#2563eb;font-weight:600;">Generar →</div>
                </a>

                <a href="{{ route('reportes.docentes-grupo') }}" class="card" style="display:block;text-decoration:none;color:inherit;transition:box-shadow .15s;cursor:pointer;">
                    <div style="font-size:28px;margin-bottom:8px;">👨‍🏫</div>
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Docentes por Grupo</h3>
                    <p style="font-size:13px;color:#64748b;">Asignación de docentes a grupos con sus materias y horarios.</p>
                    <div style="margin-top:12px;font-size:12px;color:#2563eb;font-weight:600;">Generar →</div>
                </a>

                <a href="{{ route('reportes.grupos-mas-aprobados') }}" class="card" style="display:block;text-decoration:none;color:inherit;transition:box-shadow .15s;cursor:pointer;">
                    <div style="font-size:28px;margin-bottom:8px;">🏆</div>
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Grupos con Más Aprobados</h3>
                    <p style="font-size:13px;color:#64748b;">Ranking de grupos por cantidad y porcentaje de aprobados.</p>
                    <div style="margin-top:12px;font-size:12px;color:#2563eb;font-weight:600;">Generar →</div>
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>
