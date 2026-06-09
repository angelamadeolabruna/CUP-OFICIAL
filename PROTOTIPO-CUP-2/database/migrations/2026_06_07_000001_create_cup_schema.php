<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id_rol');
            $table->string('nombre_rol', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->jsonb('permisos_json')->nullable();
            $table->boolean('estado_activo')->default(true);
            $table->timestamps();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigIncrements('id_usuario');
            $table->foreignId('id_rol')->constrained('roles', 'id_rol')->restrictOnDelete();
            $table->uuid('auth_uid')->nullable()->unique();
            $table->string('email', 120)->unique();
            $table->string('ci', 30)->nullable()->unique();
            $table->string('nombre_usuario', 120);
            $table->string('password_hash', 255)->nullable();
            $table->string('estado', 30)->default('activo');
            $table->timestamp('ultimo_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sesiones', function (Blueprint $table) {
            $table->bigIncrements('id_sesion');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario')->cascadeOnDelete();
            $table->string('token_hash', 255);
            $table->ipAddress('ip_origen')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('estado', 30)->default('activa');
            $table->timestamp('expira_en')->nullable();
            $table->timestamps();
        });

        Schema::create('tokens_recuperacion', function (Blueprint $table) {
            $table->bigIncrements('id_token');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario')->cascadeOnDelete();
            $table->string('codigo_hash', 255);
            $table->boolean('usado')->default(false);
            $table->timestamp('expira_en');
            $table->timestamps();
        });

        Schema::create('bitacoras', function (Blueprint $table) {
            $table->bigIncrements('id_bitacora');
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
            $table->string('accion', 120);
            $table->string('tabla_afectada', 80)->nullable();
            $table->unsignedBigInteger('id_registro')->nullable();
            $table->text('detalle')->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('gestiones_admision', function (Blueprint $table) {
            $table->bigIncrements('id_gestion');
            $table->string('nombre_gestion', 80);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 30)->default('planificada');
            $table->timestamps();
        });

        Schema::create('carreras', function (Blueprint $table) {
            $table->bigIncrements('id_carrera');
            $table->string('codigo_carrera', 20)->unique();
            $table->string('nombre_carrera', 120);
            $table->boolean('estado_activo')->default(true);
            $table->timestamps();
        });

        Schema::create('cupos_carrera', function (Blueprint $table) {
            $table->bigIncrements('id_cupo');
            $table->foreignId('id_gestion')->constrained('gestiones_admision', 'id_gestion')->cascadeOnDelete();
            $table->foreignId('id_carrera')->constrained('carreras', 'id_carrera')->restrictOnDelete();
            $table->unsignedInteger('cupos_totales');
            $table->unsignedInteger('cupos_ocupados')->default(0);
            $table->timestamps();
            $table->unique(['id_gestion', 'id_carrera']);
        });

        Schema::create('prepostulantes', function (Blueprint $table) {
            $table->bigIncrements('id_prepostulante');
            $table->foreignId('id_gestion')->constrained('gestiones_admision', 'id_gestion')->restrictOnDelete();
            $table->string('ci', 30)->unique();
            $table->string('nombres', 120);
            $table->string('apellidos', 120);
            $table->string('correo', 120)->unique();
            $table->string('telefono', 30)->nullable();
            $table->string('estado_proceso', 40)->default('prepostulado');
            $table->timestamps();
        });

        Schema::create('requisitos', function (Blueprint $table) {
            $table->bigIncrements('id_requisito');
            $table->foreignId('id_gestion')->constrained('gestiones_admision', 'id_gestion')->cascadeOnDelete();
            $table->string('nombre_requisito', 100);
            $table->text('descripcion')->nullable();
            $table->boolean('obligatorio')->default(true);
            $table->boolean('estado_activo')->default(true);
            $table->timestamps();
        });

        Schema::create('requisitos_presentados', function (Blueprint $table) {
            $table->bigIncrements('id_requisito_presentado');
            $table->foreignId('id_prepostulante')->constrained('prepostulantes', 'id_prepostulante')->cascadeOnDelete();
            $table->foreignId('id_requisito')->constrained('requisitos', 'id_requisito')->restrictOnDelete();
            $table->string('archivo_url', 500)->nullable();
            $table->string('estado_revision', 30)->default('pendiente');
            $table->text('observacion')->nullable();
            $table->timestamp('fecha_presentacion')->useCurrent();
            $table->timestamp('fecha_revision')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
            $table->timestamps();
            $table->unique(['id_prepostulante', 'id_requisito']);
        });

        Schema::create('observaciones_requisito', function (Blueprint $table) {
            $table->bigIncrements('id_observacion');
            $table->foreignId('id_requisito_presentado')->constrained('requisitos_presentados', 'id_requisito_presentado')->cascadeOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
            $table->text('motivo');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('id_pago');
            $table->foreignId('id_prepostulante')->constrained('prepostulantes', 'id_prepostulante')->cascadeOnDelete();
            $table->string('codigo_pago', 80)->unique();
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago', 40)->default('transferencia');
            $table->string('estado_pago', 30)->default('pendiente');
            $table->string('comprobante_url', 500)->nullable();
            $table->string('referencia_pasarela', 120)->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->timestamps();
        });

        Schema::create('transacciones_pago', function (Blueprint $table) {
            $table->bigIncrements('id_transaccion');
            $table->foreignId('id_pago')->constrained('pagos', 'id_pago')->cascadeOnDelete();
            $table->string('proveedor', 60);
            $table->string('codigo_transaccion', 120)->nullable();
            $table->string('estado_transaccion', 30);
            $table->jsonb('payload_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('datos_registro_temporal', function (Blueprint $table) {
            $table->bigIncrements('id_datos_registro');
            $table->foreignId('id_prepostulante')->unique()->constrained('prepostulantes', 'id_prepostulante')->cascadeOnDelete();
            $table->foreignId('carrera_primera_opcion')->constrained('carreras', 'id_carrera')->restrictOnDelete();
            $table->foreignId('carrera_segunda_opcion')->nullable()->constrained('carreras', 'id_carrera')->nullOnDelete();
            $table->date('fecha_nacimiento');
            $table->string('sexo', 20);
            $table->text('direccion');
            $table->string('telefono', 30);
            $table->string('correo', 120);
            $table->string('colegio_procedencia', 160);
            $table->string('ciudad', 80);
            $table->boolean('titulo_bachiller')->default(false);
            $table->string('doc_identidad_url', 500)->nullable();
            $table->string('doc_titulo_url', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('postulantes', function (Blueprint $table) {
            $table->bigIncrements('id_postulante');
            $table->foreignId('id_prepostulante')->unique()->constrained('prepostulantes', 'id_prepostulante')->restrictOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
            $table->foreignId('id_gestion')->constrained('gestiones_admision', 'id_gestion')->restrictOnDelete();
            $table->foreignId('carrera_primera_opcion')->constrained('carreras', 'id_carrera')->restrictOnDelete();
            $table->foreignId('carrera_segunda_opcion')->nullable()->constrained('carreras', 'id_carrera')->nullOnDelete();
            $table->date('fecha_nacimiento');
            $table->string('sexo', 20);
            $table->text('direccion');
            $table->string('telefono', 30);
            $table->string('correo', 120);
            $table->string('colegio_procedencia', 160);
            $table->string('ciudad', 80);
            $table->boolean('titulo_bachiller')->default(false);
            $table->string('doc_identidad_url', 500)->nullable();
            $table->string('doc_titulo_url', 500)->nullable();
            $table->string('estado_postulante', 40)->default('inscrito');
            $table->decimal('promedio_final', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('documentos_postulante', function (Blueprint $table) {
            $table->bigIncrements('id_documento');
            $table->foreignId('id_postulante')->constrained('postulantes', 'id_postulante')->cascadeOnDelete();
            $table->string('tipo_documento', 80);
            $table->string('archivo_url', 500);
            $table->string('estado', 30)->default('vigente');
            $table->timestamps();
        });

        Schema::create('bajas_postulante', function (Blueprint $table) {
            $table->bigIncrements('id_baja');
            $table->foreignId('id_postulante')->constrained('postulantes', 'id_postulante')->cascadeOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
            $table->text('motivo');
            $table->timestamp('fecha_baja')->useCurrent();
        });

        Schema::create('aulas', function (Blueprint $table) {
            $table->bigIncrements('id_aula');
            $table->string('codigo_aula', 30)->unique();
            $table->string('ubicacion', 120)->nullable();
            $table->unsignedInteger('capacidad');
            $table->boolean('estado_activo')->default(true);
            $table->timestamps();
        });

        Schema::create('capacidades_aula', function (Blueprint $table) {
            $table->bigIncrements('id_capacidad');
            $table->foreignId('id_gestion')->constrained('gestiones_admision', 'id_gestion')->cascadeOnDelete();
            $table->unsignedInteger('max_estudiantes')->default(70);
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->unique('id_gestion');
        });

        Schema::create('grupos', function (Blueprint $table) {
            $table->bigIncrements('id_grupo');
            $table->foreignId('id_gestion')->constrained('gestiones_admision', 'id_gestion')->cascadeOnDelete();
            $table->foreignId('id_aula')->nullable()->constrained('aulas', 'id_aula')->nullOnDelete();
            $table->string('nombre_grupo', 50);
            $table->unsignedInteger('capacidad_maxima')->default(70);
            $table->string('estado', 30)->default('activo');
            $table->timestamps();
        });

        Schema::create('horarios', function (Blueprint $table) {
            $table->bigIncrements('id_horario');
            $table->string('dia_semana', 20);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('turno', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('materias', function (Blueprint $table) {
            $table->bigIncrements('id_materia');
            $table->string('nombre_materia', 80)->unique();
            $table->boolean('estado_activo')->default(true);
            $table->timestamps();
        });

        Schema::create('grupo_horarios', function (Blueprint $table) {
            $table->bigIncrements('id_grupo_horario');
            $table->foreignId('id_grupo')->constrained('grupos', 'id_grupo')->cascadeOnDelete();
            $table->foreignId('id_horario')->constrained('horarios', 'id_horario')->restrictOnDelete();
            $table->foreignId('id_materia')->constrained('materias', 'id_materia')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['id_grupo', 'id_horario', 'id_materia']);
        });

        Schema::create('postulante_grupos', function (Blueprint $table) {
            $table->bigIncrements('id_postulante_grupo');
            $table->foreignId('id_postulante')->constrained('postulantes', 'id_postulante')->cascadeOnDelete();
            $table->foreignId('id_grupo')->constrained('grupos', 'id_grupo')->cascadeOnDelete();
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->string('estado', 30)->default('activo');
            $table->timestamps();
            $table->unique(['id_postulante', 'id_grupo']);
        });

        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->bigIncrements('id_evaluacion');
            $table->foreignId('id_gestion')->constrained('gestiones_admision', 'id_gestion')->cascadeOnDelete();
            $table->foreignId('id_materia')->constrained('materias', 'id_materia')->restrictOnDelete();
            $table->unsignedTinyInteger('numero_evaluacion');
            $table->decimal('porcentaje', 5, 2);
            $table->date('fecha_evaluacion')->nullable();
            $table->string('estado', 30)->default('programada');
            $table->timestamps();
            $table->unique(['id_gestion', 'id_materia', 'numero_evaluacion']);
        });

        Schema::create('notas', function (Blueprint $table) {
            $table->bigIncrements('id_nota');
            $table->foreignId('id_postulante')->constrained('postulantes', 'id_postulante')->cascadeOnDelete();
            $table->foreignId('id_evaluacion')->constrained('evaluaciones', 'id_evaluacion')->cascadeOnDelete();
            $table->foreignId('id_docente')->nullable();
            $table->decimal('nota', 5, 2);
            $table->string('estado', 30)->default('registrada');
            $table->timestamps();
            $table->unique(['id_postulante', 'id_evaluacion']);
        });

        Schema::create('historial_notas', function (Blueprint $table) {
            $table->bigIncrements('id_historial_nota');
            $table->foreignId('id_nota')->constrained('notas', 'id_nota')->cascadeOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
            $table->decimal('nota_anterior', 5, 2)->nullable();
            $table->decimal('nota_nueva', 5, 2);
            $table->text('motivo')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('resultados', function (Blueprint $table) {
            $table->bigIncrements('id_resultado');
            $table->foreignId('id_postulante')->unique()->constrained('postulantes', 'id_postulante')->cascadeOnDelete();
            $table->decimal('promedio_final', 5, 2);
            $table->string('estado_academico', 20);
            $table->boolean('publicado')->default(false);
            $table->timestamp('fecha_calculo')->nullable();
            $table->timestamp('fecha_publicacion')->nullable();
            $table->timestamps();
        });

        Schema::create('admisiones', function (Blueprint $table) {
            $table->bigIncrements('id_admision');
            $table->foreignId('id_postulante')->unique()->constrained('postulantes', 'id_postulante')->cascadeOnDelete();
            $table->foreignId('id_resultado')->nullable()->constrained('resultados', 'id_resultado')->nullOnDelete();
            $table->foreignId('id_carrera_asignada')->nullable()->constrained('carreras', 'id_carrera')->nullOnDelete();
            $table->unsignedTinyInteger('opcion_asignada')->nullable();
            $table->unsignedInteger('orden_merito')->nullable();
            $table->string('estado_admision', 30)->default('pendiente');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('docentes', function (Blueprint $table) {
            $table->bigIncrements('id_docente');
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
            $table->string('ci', 30)->unique();
            $table->string('nombres', 120);
            $table->string('apellidos', 120);
            $table->string('profesion', 120);
            $table->string('correo', 120)->unique();
            $table->string('telefono', 30)->nullable();
            $table->string('estado_docente', 30)->default('pendiente');
            $table->timestamps();
        });

        Schema::table('notas', function (Blueprint $table) {
            $table->foreign('id_docente')->references('id_docente')->on('docentes')->nullOnDelete();
        });

        Schema::create('requisitos_docente', function (Blueprint $table) {
            $table->bigIncrements('id_requisito_docente');
            $table->foreignId('id_docente')->constrained('docentes', 'id_docente')->cascadeOnDelete();
            $table->string('tipo_requisito', 80);
            $table->string('archivo_url', 500);
            $table->string('estado_revision', 30)->default('pendiente');
            $table->text('observacion')->nullable();
            $table->timestamps();
        });

        Schema::create('cargas_horarias', function (Blueprint $table) {
            $table->bigIncrements('id_carga_horaria');
            $table->foreignId('id_docente')->constrained('docentes', 'id_docente')->cascadeOnDelete();
            $table->foreignId('id_grupo')->constrained('grupos', 'id_grupo')->cascadeOnDelete();
            $table->foreignId('id_materia')->constrained('materias', 'id_materia')->restrictOnDelete();
            $table->foreignId('id_horario')->constrained('horarios', 'id_horario')->restrictOnDelete();
            $table->string('estado', 30)->default('activo');
            $table->timestamps();
            $table->unique(['id_docente', 'id_grupo', 'id_materia']);
        });

        Schema::create('asistencias', function (Blueprint $table) {
            $table->bigIncrements('id_asistencia');
            $table->foreignId('id_postulante')->constrained('postulantes', 'id_postulante')->cascadeOnDelete();
            $table->foreignId('id_grupo')->constrained('grupos', 'id_grupo')->cascadeOnDelete();
            $table->foreignId('id_docente')->nullable()->constrained('docentes', 'id_docente')->nullOnDelete();
            $table->date('fecha_clase');
            $table->string('estado_asistencia', 20);
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->unique(['id_postulante', 'id_grupo', 'fecha_clase']);
        });

        Schema::create('reportes', function (Blueprint $table) {
            $table->bigIncrements('id_reporte');
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
            $table->string('tipo_reporte', 80);
            $table->jsonb('filtros_json')->nullable();
            $table->string('resultado_url', 500)->nullable();
            $table->string('formato', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('archivos_exportados', function (Blueprint $table) {
            $table->bigIncrements('id_archivo');
            $table->foreignId('id_reporte')->constrained('reportes', 'id_reporte')->cascadeOnDelete();
            $table->string('formato', 20);
            $table->string('archivo_url', 500);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('comandos_voz', function (Blueprint $table) {
            $table->bigIncrements('id_comando_voz');
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
            $table->text('audio_url')->nullable();
            $table->text('texto_reconocido');
            $table->string('intencion', 80)->nullable();
            $table->string('estado', 30)->default('procesado');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('dashboard_metricas', function (Blueprint $table) {
            $table->bigIncrements('id_metrica');
            $table->foreignId('id_gestion')->constrained('gestiones_admision', 'id_gestion')->cascadeOnDelete();
            $table->string('nombre_metrica', 80);
            $table->decimal('valor', 12, 2)->default(0);
            $table->timestamp('fecha_calculo')->useCurrent();
        });
    }

    public function down(): void
    {
        $tables = [
            'dashboard_metricas',
            'comandos_voz',
            'archivos_exportados',
            'reportes',
            'asistencias',
            'cargas_horarias',
            'requisitos_docente',
            'docentes',
            'admisiones',
            'resultados',
            'historial_notas',
            'notas',
            'evaluaciones',
            'postulante_grupos',
            'grupo_horarios',
            'materias',
            'horarios',
            'grupos',
            'capacidades_aula',
            'aulas',
            'bajas_postulante',
            'documentos_postulante',
            'postulantes',
            'datos_registro_temporal',
            'transacciones_pago',
            'pagos',
            'observaciones_requisito',
            'requisitos_presentados',
            'requisitos',
            'prepostulantes',
            'cupos_carrera',
            'carreras',
            'gestiones_admision',
            'bitacoras',
            'tokens_recuperacion',
            'sesiones',
            'usuarios',
            'roles',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
