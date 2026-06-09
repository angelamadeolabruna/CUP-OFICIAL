-- ============================================================
-- ESQUEMA FÍSICO COMPLETO - Sistema CUP FICCT (PostgreSQL 15+)
-- Generado a partir de las Migraciones de Laravel
-- Compatible con Supabase
-- ============================================================

-- 1. SEGURIDAD Y ACCESO
-- ============================================================

CREATE TABLE roles (
    id_rol          BIGSERIAL PRIMARY KEY,
    nombre_rol      VARCHAR(50) NOT NULL UNIQUE,
    descripcion     TEXT,
    permisos_json   JSONB,
    estado_activo   BOOLEAN DEFAULT true NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    id_usuario      BIGSERIAL PRIMARY KEY,
    id_rol          BIGINT NOT NULL REFERENCES roles(id_rol) ON DELETE RESTRICT,
    auth_uid        UUID UNIQUE,
    email           VARCHAR(120) NOT NULL UNIQUE,
    ci              VARCHAR(30) UNIQUE,
    nombre_usuario  VARCHAR(120) NOT NULL,
    password_hash   VARCHAR(255),
    estado          VARCHAR(30) DEFAULT 'activo' NOT NULL,
    ultimo_login    TIMESTAMP(0) WITH TIME ZONE,
    remember_token  VARCHAR(100),
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sesiones (
    id_sesion       BIGSERIAL PRIMARY KEY,
    id_usuario      BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    token_hash      VARCHAR(255) NOT NULL,
    ip_origen       VARCHAR(45),
    user_agent      VARCHAR(500),
    estado          VARCHAR(30) DEFAULT 'activa' NOT NULL,
    expira_en       TIMESTAMP(0) WITH TIME ZONE,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tokens_recuperacion (
    id_token        BIGSERIAL PRIMARY KEY,
    id_usuario      BIGINT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    codigo_hash     VARCHAR(255) NOT NULL,
    usado           BOOLEAN DEFAULT false NOT NULL,
    expira_en       TIMESTAMP(0) WITH TIME ZONE NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bitacoras (
    id_bitacora     BIGSERIAL PRIMARY KEY,
    id_usuario      BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    accion          VARCHAR(120) NOT NULL,
    tabla_afectada  VARCHAR(80),
    id_registro     BIGINT,
    detalle         TEXT,
    ip_origen       VARCHAR(45),
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- 2. GESTIÓN ACADÉMICA
-- ============================================================

CREATE TABLE gestiones_admision (
    id_gestion      BIGSERIAL PRIMARY KEY,
    nombre_gestion  VARCHAR(80) NOT NULL,
    fecha_inicio    DATE NOT NULL,
    fecha_fin       DATE,
    estado          VARCHAR(30) DEFAULT 'planificada' NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE carreras (
    id_carrera          BIGSERIAL PRIMARY KEY,
    codigo_carrera      VARCHAR(20) NOT NULL UNIQUE,
    nombre_carrera      VARCHAR(120) NOT NULL,
    estado_activo       BOOLEAN DEFAULT true NOT NULL,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cupos_carrera (
    id_cupo         BIGSERIAL PRIMARY KEY,
    id_gestion      BIGINT NOT NULL REFERENCES gestiones_admision(id_gestion) ON DELETE CASCADE,
    id_carrera      BIGINT NOT NULL REFERENCES carreras(id_carrera) ON DELETE RESTRICT,
    cupos_totales   INTEGER NOT NULL,
    cupos_ocupados  INTEGER DEFAULT 0 NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_gestion, id_carrera)
);

-- 3. PREINSCRIPCIÓN (PREPOSTULANTES)
-- ============================================================

CREATE TABLE prepostulantes (
    id_prepostulante    BIGSERIAL PRIMARY KEY,
    id_gestion          BIGINT NOT NULL REFERENCES gestiones_admision(id_gestion) ON DELETE RESTRICT,
    ci                  VARCHAR(30) NOT NULL UNIQUE,
    nombres             VARCHAR(120) NOT NULL,
    apellidos           VARCHAR(120) NOT NULL,
    correo              VARCHAR(120) NOT NULL UNIQUE,
    telefono            VARCHAR(30),
    estado_proceso      VARCHAR(40) DEFAULT 'prepostulado' NOT NULL,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE requisitos (
    id_requisito        BIGSERIAL PRIMARY KEY,
    id_gestion          BIGINT NOT NULL REFERENCES gestiones_admision(id_gestion) ON DELETE CASCADE,
    nombre_requisito    VARCHAR(100) NOT NULL,
    descripcion         TEXT,
    obligatorio         BOOLEAN DEFAULT true NOT NULL,
    estado_activo       BOOLEAN DEFAULT true NOT NULL,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE requisitos_presentados (
    id_requisito_presentado BIGSERIAL PRIMARY KEY,
    id_prepostulante        BIGINT NOT NULL REFERENCES prepostulantes(id_prepostulante) ON DELETE CASCADE,
    id_requisito            BIGINT NOT NULL REFERENCES requisitos(id_requisito) ON DELETE RESTRICT,
    archivo_url             VARCHAR(500),
    estado_revision         VARCHAR(30) DEFAULT 'pendiente' NOT NULL,
    observacion             TEXT,
    fecha_presentacion      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    fecha_revision          TIMESTAMP(0) WITH TIME ZONE,
    revisado_por            BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    created_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_prepostulante, id_requisito)
);

CREATE TABLE observaciones_requisito (
    id_observacion          BIGSERIAL PRIMARY KEY,
    id_requisito_presentado BIGINT NOT NULL REFERENCES requisitos_presentados(id_requisito_presentado) ON DELETE CASCADE,
    id_usuario              BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    motivo                  TEXT NOT NULL,
    created_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE pagos (
    id_pago             BIGSERIAL PRIMARY KEY,
    id_prepostulante    BIGINT NOT NULL REFERENCES prepostulantes(id_prepostulante) ON DELETE CASCADE,
    codigo_pago         VARCHAR(80) NOT NULL UNIQUE,
    monto               DECIMAL(10, 2) NOT NULL,
    metodo_pago         VARCHAR(40) DEFAULT 'transferencia' NOT NULL,
    estado_pago         VARCHAR(30) DEFAULT 'pendiente' NOT NULL,
    comprobante_url     VARCHAR(500),
    referencia_pasarela VARCHAR(120),
    fecha_pago          TIMESTAMP(0) WITH TIME ZONE,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE transacciones_pago (
    id_transaccion      BIGSERIAL PRIMARY KEY,
    id_pago             BIGINT NOT NULL REFERENCES pagos(id_pago) ON DELETE CASCADE,
    proveedor           VARCHAR(60) NOT NULL,
    codigo_transaccion  VARCHAR(120),
    estado_transaccion  VARCHAR(30) NOT NULL,
    payload_json        JSONB,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- 4. INSCRIPCIÓN OFICIAL (POSTULANTES)
-- ============================================================

CREATE TABLE datos_registro_temporal (
    id_datos_registro       BIGSERIAL PRIMARY KEY,
    id_prepostulante        BIGINT NOT NULL UNIQUE REFERENCES prepostulantes(id_prepostulante) ON DELETE CASCADE,
    carrera_primera_opcion  BIGINT NOT NULL REFERENCES carreras(id_carrera) ON DELETE RESTRICT,
    carrera_segunda_opcion  BIGINT REFERENCES carreras(id_carrera) ON DELETE SET NULL,
    fecha_nacimiento        DATE NOT NULL,
    sexo                    VARCHAR(20) NOT NULL,
    direccion               TEXT NOT NULL,
    telefono                VARCHAR(30) NOT NULL,
    correo                  VARCHAR(120) NOT NULL,
    colegio_procedencia     VARCHAR(160) NOT NULL,
    ciudad                  VARCHAR(80) NOT NULL,
    titulo_bachiller        BOOLEAN DEFAULT false NOT NULL,
    doc_identidad_url       VARCHAR(500),
    doc_titulo_url          VARCHAR(500),
    created_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE postulantes (
    id_postulante           BIGSERIAL PRIMARY KEY,
    id_prepostulante        BIGINT NOT NULL UNIQUE REFERENCES prepostulantes(id_prepostulante) ON DELETE RESTRICT,
    id_usuario              BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    id_gestion              BIGINT NOT NULL REFERENCES gestiones_admision(id_gestion) ON DELETE RESTRICT,
    carrera_primera_opcion  BIGINT NOT NULL REFERENCES carreras(id_carrera) ON DELETE RESTRICT,
    carrera_segunda_opcion  BIGINT REFERENCES carreras(id_carrera) ON DELETE SET NULL,
    fecha_nacimiento        DATE NOT NULL,
    sexo                    VARCHAR(20) NOT NULL,
    direccion               TEXT NOT NULL,
    telefono                VARCHAR(30) NOT NULL,
    correo                  VARCHAR(120) NOT NULL,
    colegio_procedencia     VARCHAR(160) NOT NULL,
    ciudad                  VARCHAR(80) NOT NULL,
    titulo_bachiller        BOOLEAN DEFAULT false NOT NULL,
    doc_identidad_url       VARCHAR(500),
    doc_titulo_url          VARCHAR(500),
    estado_postulante       VARCHAR(40) DEFAULT 'inscrito' NOT NULL,
    promedio_final          DECIMAL(5, 2),
    created_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE documentos_postulante (
    id_documento    BIGSERIAL PRIMARY KEY,
    id_postulante   BIGINT NOT NULL REFERENCES postulantes(id_postulante) ON DELETE CASCADE,
    tipo_documento  VARCHAR(80) NOT NULL,
    archivo_url     VARCHAR(500) NOT NULL,
    estado          VARCHAR(30) DEFAULT 'vigente' NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bajas_postulante (
    id_baja         BIGSERIAL PRIMARY KEY,
    id_postulante   BIGINT NOT NULL REFERENCES postulantes(id_postulante) ON DELETE CASCADE,
    id_usuario      BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    motivo          TEXT NOT NULL,
    fecha_baja      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- 5. LOGÍSTICA (AULAS, GRUPOS, HORARIOS, MATERIAS)
-- ============================================================

CREATE TABLE aulas (
    id_aula         BIGSERIAL PRIMARY KEY,
    codigo_aula     VARCHAR(30) NOT NULL UNIQUE,
    ubicacion       VARCHAR(120),
    capacidad       INTEGER NOT NULL,
    estado_activo   BOOLEAN DEFAULT true NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE capacidades_aula (
    id_capacidad    BIGSERIAL PRIMARY KEY,
    id_gestion      BIGINT NOT NULL UNIQUE REFERENCES gestiones_admision(id_gestion) ON DELETE CASCADE,
    max_estudiantes INTEGER DEFAULT 70 NOT NULL,
    descripcion     TEXT,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE materias (
    id_materia      BIGSERIAL PRIMARY KEY,
    nombre_materia  VARCHAR(80) NOT NULL UNIQUE,
    estado_activo   BOOLEAN DEFAULT true NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE horarios (
    id_horario  BIGSERIAL PRIMARY KEY,
    dia_semana  VARCHAR(20) NOT NULL,
    hora_inicio TIME WITHOUT TIME ZONE NOT NULL,
    hora_fin    TIME WITHOUT TIME ZONE NOT NULL,
    turno       VARCHAR(30),
    created_at  TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE grupos (
    id_grupo            BIGSERIAL PRIMARY KEY,
    id_gestion          BIGINT NOT NULL REFERENCES gestiones_admision(id_gestion) ON DELETE CASCADE,
    id_materia          BIGINT REFERENCES materias(id_materia) ON DELETE RESTRICT,
    id_aula             BIGINT REFERENCES aulas(id_aula) ON DELETE SET NULL,
    nombre_grupo        VARCHAR(50) NOT NULL,
    capacidad_maxima    INTEGER DEFAULT 70 NOT NULL,
    estado              VARCHAR(30) DEFAULT 'activo' NOT NULL,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE grupo_horarios (
    id_grupo_horario    BIGSERIAL PRIMARY KEY,
    id_grupo            BIGINT NOT NULL REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    id_horario          BIGINT NOT NULL REFERENCES horarios(id_horario) ON DELETE RESTRICT,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_grupo, id_horario)
);

CREATE TABLE postulante_grupos (
    id_postulante_grupo BIGSERIAL PRIMARY KEY,
    id_postulante       BIGINT NOT NULL REFERENCES postulantes(id_postulante) ON DELETE CASCADE,
    id_grupo            BIGINT NOT NULL REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    fecha_asignacion    TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    estado              VARCHAR(30) DEFAULT 'activo' NOT NULL,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_postulante, id_grupo)
);

-- 6. EVALUACIÓN ACADÉMICA
-- ============================================================

CREATE TABLE evaluaciones (
    id_evaluacion       BIGSERIAL PRIMARY KEY,
    id_gestion          BIGINT NOT NULL REFERENCES gestiones_admision(id_gestion) ON DELETE CASCADE,
    id_materia          BIGINT NOT NULL REFERENCES materias(id_materia) ON DELETE RESTRICT,
    numero_evaluacion   SMALLINT NOT NULL,
    porcentaje          DECIMAL(5, 2) NOT NULL,
    fecha_evaluacion    DATE,
    estado              VARCHAR(30) DEFAULT 'programada' NOT NULL,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_gestion, id_materia, numero_evaluacion)
);

CREATE TABLE docentes (
    id_docente      BIGSERIAL PRIMARY KEY,
    id_usuario      BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    ci              VARCHAR(30) NOT NULL UNIQUE,
    nombres         VARCHAR(120) NOT NULL,
    apellidos       VARCHAR(120) NOT NULL,
    profesion       VARCHAR(120) NOT NULL,
    correo          VARCHAR(120) NOT NULL UNIQUE,
    telefono        VARCHAR(30),
    estado_docente  VARCHAR(30) DEFAULT 'pendiente' NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notas (
    id_nota         BIGSERIAL PRIMARY KEY,
    id_postulante   BIGINT NOT NULL REFERENCES postulantes(id_postulante) ON DELETE CASCADE,
    id_evaluacion   BIGINT NOT NULL REFERENCES evaluaciones(id_evaluacion) ON DELETE CASCADE,
    id_docente      BIGINT REFERENCES docentes(id_docente) ON DELETE SET NULL,
    nota            DECIMAL(5, 2) NOT NULL,
    estado          VARCHAR(30) DEFAULT 'registrada' NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_postulante, id_evaluacion)
);

CREATE TABLE historial_notas (
    id_historial_nota   BIGSERIAL PRIMARY KEY,
    id_nota             BIGINT NOT NULL REFERENCES notas(id_nota) ON DELETE CASCADE,
    id_usuario          BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    nota_anterior       DECIMAL(5, 2),
    nota_nueva          DECIMAL(5, 2) NOT NULL,
    motivo              TEXT,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE resultados (
    id_resultado        BIGSERIAL PRIMARY KEY,
    id_postulante       BIGINT NOT NULL UNIQUE REFERENCES postulantes(id_postulante) ON DELETE CASCADE,
    promedio_final      DECIMAL(5, 2) NOT NULL,
    estado_academico    VARCHAR(20) NOT NULL,
    public              BOOLEAN DEFAULT false NOT NULL,
    fecha_calculo       TIMESTAMP(0) WITH TIME ZONE,
    fecha_publicacion   TIMESTAMP(0) WITH TIME ZONE,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admisiones (
    id_admision             BIGSERIAL PRIMARY KEY,
    id_postulante           BIGINT NOT NULL UNIQUE REFERENCES postulantes(id_postulante) ON DELETE CASCADE,
    id_resultado            BIGINT REFERENCES resultados(id_resultado) ON DELETE SET NULL,
    id_carrera_asignada     BIGINT REFERENCES carreras(id_carrera) ON DELETE SET NULL,
    opcion_asignada         SMALLINT,
    orden_merito            INTEGER,
    estado_admision         VARCHAR(30) DEFAULT 'pendiente' NOT NULL,
    created_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- 7. DOCENTES
-- ============================================================

CREATE TABLE requisitos_docente (
    id_requisito_docente    BIGSERIAL PRIMARY KEY,
    id_docente              BIGINT NOT NULL REFERENCES docentes(id_docente) ON DELETE CASCADE,
    tipo_requisito          VARCHAR(80) NOT NULL,
    archivo_url             VARCHAR(500) NOT NULL,
    estado_revision         VARCHAR(30) DEFAULT 'pendiente' NOT NULL,
    observacion             TEXT,
    created_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cargas_horarias (
    id_carga_horaria    BIGSERIAL PRIMARY KEY,
    id_docente          BIGINT NOT NULL REFERENCES docentes(id_docente) ON DELETE CASCADE,
    id_grupo            BIGINT NOT NULL REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    estado              VARCHAR(30) DEFAULT 'activo' NOT NULL,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_docente, id_grupo)
);

CREATE TABLE asistencias (
    id_asistencia       BIGSERIAL PRIMARY KEY,
    id_postulante       BIGINT NOT NULL REFERENCES postulantes(id_postulante) ON DELETE CASCADE,
    id_grupo            BIGINT NOT NULL REFERENCES grupos(id_grupo) ON DELETE CASCADE,
    id_docente          BIGINT REFERENCES docentes(id_docente) ON DELETE SET NULL,
    fecha_clase         DATE NOT NULL,
    estado_asistencia   VARCHAR(20) NOT NULL,
    observacion         TEXT,
    created_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(id_postulante, id_grupo, fecha_clase)
);

-- 8. REPORTES Y DASHBOARD
-- ============================================================

CREATE TABLE reportes (
    id_reporte      BIGSERIAL PRIMARY KEY,
    id_usuario      BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    tipo_reporte    VARCHAR(80) NOT NULL,
    filtros_json    JSONB,
    resultado_url   VARCHAR(500),
    formato         VARCHAR(20),
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE archivos_exportados (
    id_archivo      BIGSERIAL PRIMARY KEY,
    id_reporte      BIGINT NOT NULL REFERENCES reportes(id_reporte) ON DELETE CASCADE,
    formato         VARCHAR(20) NOT NULL,
    archivo_url     VARCHAR(500) NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE comandos_voz (
    id_comando_voz  BIGSERIAL PRIMARY KEY,
    id_usuario      BIGINT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    audio_url       TEXT,
    texto_reconocido TEXT NOT NULL,
    intencion       VARCHAR(80),
    estado          VARCHAR(30) DEFAULT 'procesado' NOT NULL,
    created_at      TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE dashboard_metricas (
    id_metrica      BIGSERIAL PRIMARY KEY,
    id_gestion      BIGINT NOT NULL REFERENCES gestiones_admision(id_gestion) ON DELETE CASCADE,
    nombre_metrica  VARCHAR(80) NOT NULL,
    valor           DECIMAL(12, 2) DEFAULT 0 NOT NULL,
    fecha_calculo   TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- 9. TABLAS DE RUNTIME (LARAVEL)
-- ============================================================

CREATE TABLE sessions (
    id              VARCHAR(255) PRIMARY KEY,
    user_id         BIGINT,
    ip_address      VARCHAR(45),
    user_agent      TEXT,
    payload         TEXT NOT NULL,
    last_activity   INTEGER NOT NULL
);

CREATE INDEX sessions_user_id_index ON sessions(user_id);
CREATE INDEX sessions_last_activity_index ON sessions(last_activity);

CREATE TABLE cache (
    key             VARCHAR(255) PRIMARY KEY,
    value           TEXT NOT NULL,
    expiration      INTEGER NOT NULL
);

CREATE TABLE cache_locks (
    key             VARCHAR(255) PRIMARY KEY,
    owner           VARCHAR(255) NOT NULL,
    expiration      INTEGER NOT NULL
);

-- 10. ÍNDICES ADICIONALES
-- ============================================================

CREATE INDEX idx_usuarios_rol ON usuarios(id_rol);
CREATE INDEX idx_usuarios_estado ON usuarios(estado);
CREATE INDEX idx_sesiones_usuario ON sesiones(id_usuario);
CREATE INDEX idx_sesiones_estado ON sesiones(estado);
CREATE INDEX idx_bitacoras_usuario ON bitacoras(id_usuario);
CREATE INDEX idx_bitacoras_accion ON bitacoras(accion);
CREATE INDEX idx_bitacoras_created ON bitacoras(created_at);
CREATE INDEX idx_prepostulantes_gestion ON prepostulantes(id_gestion);
CREATE INDEX idx_prepostulantes_estado ON prepostulantes(estado_proceso);
CREATE INDEX idx_pagos_prepostulante ON pagos(id_prepostulante);
CREATE INDEX idx_pagos_estado ON pagos(estado_pago);
CREATE INDEX idx_postulantes_gestion ON postulantes(id_gestion);
CREATE INDEX idx_postulantes_estado ON postulantes(estado_postulante);
CREATE INDEX idx_notas_evaluacion ON notas(id_evaluacion);
CREATE INDEX idx_notas_postulante ON notas(id_postulante);
CREATE INDEX idx_resultados_estado ON resultados(estado_academico);
CREATE INDEX idx_admisiones_estado ON admisiones(estado_admision);
CREATE INDEX idx_grupos_gestion ON grupos(id_gestion);
CREATE INDEX idx_cargas_horarias_docente ON cargas_horarias(id_docente);
CREATE INDEX idx_asistencias_grupo ON asistencias(id_grupo);
CREATE INDEX idx_asistencias_fecha ON asistencias(fecha_clase);
CREATE INDEX idx_dashboard_gestion ON dashboard_metricas(id_gestion);
