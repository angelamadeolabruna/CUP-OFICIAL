<?php

use App\Http\Controllers\AcademicoController;
use App\Http\Controllers\Admin\PagoVerificacionController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\UsuarioImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\LogisticaController;
use App\Http\Controllers\Prepostulante\PagoController;
use App\Http\Controllers\Prepostulante\RegistroController;
use App\Http\Controllers\Seguridad\AuthController;
use App\Http\Controllers\Seguridad\PasswordController;
use App\Http\Controllers\Seguridad\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/recuperar-password', [PasswordResetController::class, 'showSolicitud'])->name('password.request');
    Route::post('/recuperar-password', [PasswordResetController::class, 'solicitar'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetear'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/perfil/cambiar-password', [PasswordController::class, 'edit'])->name('perfil.password.edit');
    Route::post('/perfil/cambiar-password', [PasswordController::class, 'update'])->name('perfil.password.update');

    // CU05: Gestionar Cuentas y Roles (solo administrador)
    Route::middleware('rol:administrador')->prefix('admin/usuarios')->name('admin.usuarios.')->group(function () {
        // CU06: Importar (debe ir antes de rutas con {id})
        Route::get('/importar', [UsuarioImportController::class, 'showForm'])->name('importar');
        Route::post('/importar', [UsuarioImportController::class, 'importar'])->name('importar.post');

        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::get('/crear', [UsuarioController::class, 'create'])->name('create');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::get('/{id}/editar', [UsuarioController::class, 'edit'])->name('edit');
        Route::post('/{id}', [UsuarioController::class, 'update'])->name('update');
        Route::post('/{id}/toggle', [UsuarioController::class, 'toggleEstado'])->name('toggle');
        Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('destroy');
    });

    // CU11: Verificar y Confirmar Pagos (solo administrador)
    Route::middleware('rol:administrador')->prefix('admin/pagos')->name('admin.pagos.')->group(function () {
        Route::get('/', [PagoVerificacionController::class, 'index'])->name('index');
        Route::post('/{id}/confirmar', [PagoVerificacionController::class, 'confirmarPago'])->name('confirmar');
        Route::post('/{id}/rechazar', [PagoVerificacionController::class, 'rechazarPago'])->name('rechazar');
        Route::post('/prepostulante/{idPrepostulante}/confirmar', [PagoVerificacionController::class, 'confirmarPostulante'])->name('confirmar-postulante');
    });

    // CU39: Consultar Bitácora de Auditoría (solo administrador)
    Route::middleware('rol:administrador')->prefix('admin/bitacora')->name('admin.bitacora.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BitacoraController::class, 'index'])->name('index');
    });

    // CU10: Pago de Inscripción (solo prepostulante)
    Route::middleware('rol.prepostulante')->prefix('prepostulante/pagos')->name('prepostulante.pagos.')->group(function () {
        Route::get('/', [PagoController::class, 'index'])->name('index');
        Route::post('/', [PagoController::class, 'store'])->name('store');
    });

    // CU12: Completar Registro (solo prepostulante)
    Route::middleware('rol.prepostulante')->prefix('prepostulante/registro')->name('prepostulante.registro.')->group(function () {
        Route::get('/', [RegistroController::class, 'index'])->name('index');
        Route::post('/', [RegistroController::class, 'store'])->name('store');
    });

    // Paquete: Organización Logística (coordinador_academico y administrador)
    Route::middleware('rol:coordinador_academico,administrador')->prefix('logistica')->name('logistica.')->group(function () {
        // CU26: Configurar Capacidad de Aula
        Route::get('/capacidad', [LogisticaController::class, 'capacidadIndex'])->name('capacidad.index');
        Route::post('/capacidad', [LogisticaController::class, 'capacidadStore'])->name('capacidad.store');

        // CU27: Calcular Cantidad de Grupos Necesarios
        Route::get('/grupos', [LogisticaController::class, 'gruposIndex'])->name('grupos.index');

        // CU28: Asignar Grupos, Aulas, Horarios y Materias
        Route::get('/asignar', [LogisticaController::class, 'asignarIndex'])->name('asignar.index');
        Route::post('/asignar/generar', [LogisticaController::class, 'asignarGenerar'])->name('asignar.generar');
        Route::post('/asignar/{idGrupo}/aula', [LogisticaController::class, 'asignarActualizarAula'])->name('asignar.aula');
        Route::post('/asignar/horario/agregar', [LogisticaController::class, 'asignarAgregarHorario'])->name('asignar.horario.agregar');
        Route::post('/asignar/horario/{idGrupoHorario}/quitar', [LogisticaController::class, 'asignarQuitarHorario'])->name('asignar.horario.quitar');
        Route::post('/asignar/distribuir', [LogisticaController::class, 'asignarDistribuir'])->name('asignar.distribuir');

        // CU29: Registrar Aulas
        Route::get('/aulas', [LogisticaController::class, 'aulasIndex'])->name('aulas.index');
        Route::post('/aulas', [LogisticaController::class, 'aulasStore'])->name('aulas.store');
        Route::post('/aulas/{idAula}', [LogisticaController::class, 'aulasUpdate'])->name('aulas.update');
        Route::post('/aulas/{idAula}/eliminar', [LogisticaController::class, 'aulasDestroy'])->name('aulas.destroy');

        // Horarios: Registrar horarios
        Route::get('/horarios', [LogisticaController::class, 'horariosIndex'])->name('horarios.index');
        Route::post('/horarios', [LogisticaController::class, 'horariosStore'])->name('horarios.store');
        Route::post('/horarios/{idHorario}', [LogisticaController::class, 'horariosUpdate'])->name('horarios.update');
        Route::post('/horarios/{idHorario}/eliminar', [LogisticaController::class, 'horariosDestroy'])->name('horarios.destroy');
    });

    // CU32: Consultar Carga Horaria (docente)
    Route::middleware('rol:docente')->prefix('mi-carga-horaria')->name('docentes.mi-carga-horaria.')->group(function () {
        Route::get('/', [DocenteController::class, 'miCargaHoraria'])->name('index');
    });

    // CU33: Registrar Asistencia (docente)
    Route::middleware('rol:docente')->prefix('asistencia')->name('docentes.asistencia.')->group(function () {
        Route::get('/', [DocenteController::class, 'asistenciaIndex'])->name('index');
        Route::post('/', [DocenteController::class, 'asistenciaStore'])->name('store');
    });

    // CU34: Consultar Asistencia (docente, postulante_oficial, administrador, coordinador_academico)
    Route::middleware('rol:docente,postulante_oficial,administrador,coordinador_academico')
        ->prefix('consultar-asistencia')
        ->name('asistencia.consulta.')
        ->group(function () {
            Route::get('/', [DocenteController::class, 'consultarAsistencia'])->name('index');
        });

    // Paquete: Evaluación Académica - Configuración (coordinador_academico y administrador)
    Route::middleware('rol:coordinador_academico,administrador')->prefix('academico')->name('academico.')->group(function () {
        // CU18: Configurar Materias, Exámenes y Porcentajes
        Route::get('/evaluaciones', [AcademicoController::class, 'evaluacionesIndex'])->name('evaluaciones.index');
        Route::post('/evaluaciones/guardar', [AcademicoController::class, 'evaluacionesGuardar'])->name('evaluaciones.guardar');
        Route::post('/evaluaciones/{id}/eliminar', [AcademicoController::class, 'evaluacionesEliminar'])->name('evaluaciones.eliminar');
    });

    // CU19: Registrar Notas por Materia (docente, administrador, coordinador_academico)
    Route::middleware('rol:docente,coordinador_academico,administrador')->prefix('academico')->name('academico.')->group(function () {
        Route::get('/notas', [AcademicoController::class, 'notasIndex'])->name('notas.index');
        Route::post('/notas', [AcademicoController::class, 'notasStore'])->name('notas.store');
    });

    // CU21 + CU22: Calcular Promedios y Verificar Estado (docente, coordinador_academico, administrador)
    // El docente ve los promedios pero solo el admin/coordinador puede ejecutar el cálculo
    Route::middleware('rol:docente,coordinador_academico,administrador')->prefix('academico')->name('academico.')->group(function () {
        Route::get('/promedios', [AcademicoController::class, 'promediosIndex'])->name('promedios.index');
    });
    Route::middleware('rol:coordinador_academico,administrador')->prefix('academico')->name('academico.')->group(function () {
        Route::post('/promedios/calcular', [AcademicoController::class, 'promediosCalcular'])->name('promedios.calcular');
    });

    // CU14 + CU15: Listar, Editar y Eliminar Postulantes (coordinador_academico, administrador)
    Route::middleware('rol:coordinador_academico,administrador')->prefix('academico')->name('academico.')->group(function () {
        Route::get('/postulantes', [AcademicoController::class, 'postulantesIndex'])->name('postulantes.index');
        Route::get('/postulantes/{id}/editar', [AcademicoController::class, 'postulantesEdit'])->name('postulantes.edit');
        Route::put('/postulantes/{id}', [AcademicoController::class, 'postulantesUpdate'])->name('postulantes.update');
        Route::get('/postulantes/{id}/baja', [AcademicoController::class, 'postulantesBajaConfirmar'])->name('postulantes.baja.confirmar');
        Route::post('/postulantes/{id}/baja', [AcademicoController::class, 'postulantesBajaEjecutar'])->name('postulantes.baja.ejecutar');
    });

    // CU23: Ejecutar Admisión por Cupos (coordinador_academico y administrador)
    Route::middleware('rol:coordinador_academico,administrador')->prefix('academico')->name('academico.')->group(function () {
        Route::get('/admision', [AcademicoController::class, 'admisionIndex'])->name('admision.index');
        Route::post('/admision/cupos', [AcademicoController::class, 'admisionCuposGuardar'])->name('admision.cupos.guardar');
        Route::post('/admision/ejecutar', [AcademicoController::class, 'admisionEjecutar'])->name('admision.ejecutar');
    });

    // CU30: Registrar y Validar Docente (coordinador_academico y administrador)
    Route::middleware('rol:coordinador_academico,administrador')->prefix('docentes')->name('docentes.')->group(function () {
        // Rutas estáticas deben ir antes de rutas con parámetros
        // CU31: Carga Horaria
        Route::get('/carga-horaria', [DocenteController::class, 'cargaHorariaIndex'])->name('carga-horaria.index');
        Route::post('/carga-horaria', [DocenteController::class, 'cargaHorariaStore'])->name('carga-horaria.store');
        Route::post('/carga-horaria/{idCarga}/quitar', [DocenteController::class, 'cargaHorariaQuitar'])->name('carga-horaria.quitar');

        Route::get('/', [DocenteController::class, 'index'])->name('index');
        Route::get('/crear', [DocenteController::class, 'create'])->name('create');
        Route::post('/', [DocenteController::class, 'store'])->name('store');
        Route::get('/{id}', [DocenteController::class, 'show'])->name('show');
        Route::post('/{id}/aprobar', [DocenteController::class, 'aprobar'])->name('aprobar');
        Route::post('/{id}/rechazar', [DocenteController::class, 'rechazar'])->name('rechazar');
        Route::post('/{id}/requisito', [DocenteController::class, 'subirRequisito'])->name('requisito.subir');
        Route::post('/requisito/{idRequisito}/revisar', [DocenteController::class, 'revisarRequisito'])->name('requisito.revisar');
        Route::post('/{id}/crear-usuario', [DocenteController::class, 'crearUsuario'])->name('crear-usuario');
        Route::post('/{id}/eliminar', [DocenteController::class, 'destroy'])->name('destroy');
    });

    // CU35: Reportes Obligatorios (administrador, coordinador_academico)
    Route::middleware('rol:administrador,coordinador_academico')->prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportesController::class, 'index'])->name('index');
        Route::get('/postulantes', [\App\Http\Controllers\ReportesController::class, 'postulantes'])->name('postulantes');
        Route::get('/resultados-academicos', [\App\Http\Controllers\ReportesController::class, 'resultadosAcademicos'])->name('resultados-academicos');
        Route::get('/promedios', [\App\Http\Controllers\ReportesController::class, 'promedios'])->name('promedios');
        Route::get('/grupos', [\App\Http\Controllers\ReportesController::class, 'grupos'])->name('grupos');
        Route::get('/estadisticas-materia', [\App\Http\Controllers\ReportesController::class, 'estadisticasMateria'])->name('estadisticas-materia');
        Route::get('/docentes-grupo', [\App\Http\Controllers\ReportesController::class, 'docentesGrupo'])->name('docentes-grupo');
        Route::get('/grupos-mas-aprobados', [\App\Http\Controllers\ReportesController::class, 'gruposMasAprobados'])->name('grupos-mas-aprobados');
        // CU36: Exportar
        Route::get('/{tipo}/exportar/{formato}', [\App\Http\Controllers\ReportesController::class, 'exportar'])->name('exportar');
    });

    // Diagnóstico de correo
    Route::get('/mail-test', function () {
        $host = config('mail.mailers.smtp.host', 'no configurado');
        $port = config('mail.mailers.smtp.port', 'no configurado');
        $enc  = config('mail.mailers.smtp.encryption', 'no configurado');
        $user = config('mail.mailers.smtp.username', 'no configurado');
        $from = config('mail.from.address', 'no configurado');

        $result = [];
        $result['config'] = compact('host', 'port', 'enc', 'user', 'from');

        $fp = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($fp) {
            $result['fsockopen'] = "OK - conexión exitosa a {$host}:{$port}";
            fclose($fp);
        } else {
            $result['fsockopen'] = "FALLO - {$errstr} ({$errno})";
        }

        if (auth()->check()) {
            try {
                config()->set('mail.mailers.smtp.timeout', 10);
                Mail::to(auth()->user()->email)->send(new \App\Mail\BienvenidaUsuario(auth()->user(), 'test-1234'));
                $result['mail_send'] = "Correo enviado a " . auth()->user()->email;
            } catch (\Exception $e) {
                $result['mail_send'] = "Error: " . $e->getMessage();
            }
        } else {
            $result['mail_send'] = "No autenticado";
        }

        return response()->json($result);
    })->middleware('auth');
});
