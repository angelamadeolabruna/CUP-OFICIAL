-- =============================================================================
-- diseã‘o fãsico de base de datos - sistema cup ficct
-- aplicaciã³n web de admisiã³n universitaria
-- facultad de ingenierã­a de ciencias de la computaciã³n y telecomunicaciones
-- motor: postgresql
-- autor: sistema cup ficct
-- =============================================================================

-- eliminar tablas si ya existen (orden inverso a las fk)
drop table if exists asistencias cascade;
drop table if exists cargas_horarias cascade;
drop table if exists requisitos_docente cascade;
drop table if exists docentes cascade;
drop table if exists admisiones cascade;
drop table if exists resultados cascade;
drop table if exists historial_notas cascade;
drop table if exists notas cascade;
drop table if exists evaluaciones cascade;
drop table if exists materias cascade;
drop table if exists postulante_grupos cascade;
drop table if exists grupo_horarios cascade;
drop table if exists horarios cascade;
drop table if exists grupos cascade;
drop table if exists capacidades_aula cascade;
drop table if exists aulas cascade;
drop table if exists bajas_postulante cascade;
drop table if exists documentos_postulante cascade;
drop table if exists postulantes cascade;
drop table if exists transacciones_pago cascade;
drop table if exists pagos cascade;
drop table if exists observaciones_requisito cascade;
drop table if exists requisitos_presentados cascade;
drop table if exists requisitos cascade;
drop table if exists prepostulantes cascade;
drop table if exists cupos_carrera cascade;
drop table if exists carreras cascade;
drop table if exists gestiones_admision cascade;
drop table if exists bitacoras cascade;
drop table if exists tokens_recuperacion cascade;
drop table if exists sesiones cascade;
drop table if exists usuarios cascade;
drop table if exists roles cascade;

-- =============================================================================
-- mã“dulo 1: seguridad y acceso
-- =============================================================================

-- -----------------------------------------------------------------------------
-- tabla: roles
-- almacena los roles del sistema (administrador, docente, coordinador, etc.)
-- -----------------------------------------------------------------------------
create table roles (
    id_rol          bigserial       primary key,
    nombre_rol      varchar(50)     not null unique,
    descripcion     text,
    permisos_json   jsonb,                              -- permisos granulares en formato json
    estado_activo   boolean         not null default true,
    created_at      timestamp       not null default now(),
    updated_at      timestamp       not null default now()
);

comment on table  roles              is 'roles del sistema con sus permisos';
comment on column roles.permisos_json is 'json con los permisos especã­ficos del rol (ej: {"ver_notas": true, "editar_grupos": false})';

-- -----------------------------------------------------------------------------
-- tabla: usuarios
-- cuentas de acceso al sistema
-- -----------------------------------------------------------------------------
create table usuarios (
    id_usuario      bigserial       primary key,
    auth_uid        uuid            unique,             -- id de usuario en supabase auth
    id_rol          bigint          not null references roles(id_rol),
    email           varchar(120)    not null unique,
    nombre_usuario  varchar(120)    not null,
    estado          varchar(20)     not null default 'activo'
                        check (estado in ('activo', 'inactivo', 'bloqueado')),
    ultimo_login    timestamp,
    created_at      timestamp       not null default now(),
    updated_at      timestamp       not null default now()
);

comment on table  usuarios               is 'cuentas de acceso al sistema cup vinculadas a supabase auth';
comment on column usuarios.auth_uid      is 'id del usuario en auth.users de supabase';



-- -----------------------------------------------------------------------------
-- tabla: bitacoras
-- auditorã­a de todas las acciones realizadas en el sistema
-- -----------------------------------------------------------------------------
create table bitacoras (
    id_bitacora     bigserial       primary key,
    id_usuario      bigint          not null references usuarios(id_usuario),
    accion          varchar(120)    not null,            -- ej: 'insert', 'update', 'delete'
    tabla_afectada  varchar(80)     not null,
    id_registro     bigint,                             -- id del registro afectado
    detalle_json    jsonb,                              -- datos antes/despuã©s del cambio
    ip_origen       varchar(45),
    created_at      timestamp       not null default now()
);

-- =============================================================================
-- mã“dulo 2: gestiã“n acadã‰mica (gestiones, carreras, cupos)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- tabla: gestiones_admision
-- cada convocatoria/perã­odo de admisiã³n (ej: 2026-i, 2026-ii)
-- -----------------------------------------------------------------------------
create table gestiones_admision (
    id_gestion      bigserial       primary key,
    nombre_gestion  varchar(80)     not null,
    fecha_inicio    date            not null,
    fecha_fin       date            not null,
    estado          varchar(20)     not null default 'planificada'
                        check (estado in ('planificada', 'activa', 'cerrada', 'archivada')),
    created_at      timestamp       not null default now(),
    updated_at      timestamp       not null default now(),
    constraint chk_fechas check (fecha_fin >= fecha_inicio)
);

-- -----------------------------------------------------------------------------
-- tabla: carreras
-- carreras ofertadas por la ficct
-- -----------------------------------------------------------------------------
create table carreras (
    id_carrera      bigserial       primary key,
    codigo_carrera  varchar(20)     not null unique,    -- ej: 'inf', 'tlc', 'sis'
    nombre_carrera  varchar(120)    not null,
    estado_activo   boolean         not null default true,
    created_at      timestamp       not null default now(),
    updated_at      timestamp       not null default now()
);

-- -----------------------------------------------------------------------------
-- tabla: cupos_carrera
-- cupos disponibles por carrera en cada gestiã³n
-- -----------------------------------------------------------------------------
create table cupos_carrera (
    id_cupo         bigserial       primary key,
    id_gestion      bigint          not null references gestiones_admision(id_gestion),
    id_carrera      bigint          not null references carreras(id_carrera),
    cupo_total      integer         not null check (cupo_total > 0),
    cupos_ocupados  integer         not null default 0  check (cupos_ocupados >= 0),
    created_at      timestamp       not null default now(),
    updated_at      timestamp       not null default now(),
    unique (id_gestion, id_carrera),
    constraint chk_cupos check (cupos_ocupados <= cupo_total)
);

-- =============================================================================
-- mã“dulo 3: prepostulantes, requisitos y pagos
-- =============================================================================

-- -----------------------------------------------------------------------------
-- tabla: prepostulantes
-- personas que inician el proceso pero aãºn no completan el registro formal
-- -----------------------------------------------------------------------------
create table prepostulantes (
    id_prepostulante    bigserial       primary key,
    id_gestion          bigint          not null references gestiones_admision(id_gestion),
    ci                  varchar(30)     not null unique,
    nombres             varchar(120)    not null,
    apellidos           varchar(120)    not null,
    correo              varchar(120)    not null,
    telefono            varchar(30),
    estado_proceso      varchar(40)     not null default 'pendiente_requisitos'
                            check (estado_proceso in (
                                'pendiente_requisitos',     -- cu07: reciã©n registrado
                                'requisitos_en_revision',   -- cu08: documentos presentados
                                'requisitos_aceptados',     -- cu09: admin aprobã³ documentos
                                'requisitos_observados',    -- cu09: admin observã³ documentos
                                'pago_pendiente',           -- cu09: habilitado para pagar
                                'pago_confirmado',          -- cu11: pago verificado
                                'registro_completo',        -- cu12: datos completos
                                'rechazado'                 -- proceso cancelado
                            )),
    created_at          timestamp       not null default now(),
    updated_at          timestamp       not null default now()
);

comment on table prepostulantes is 'personas que inician el proceso de admisiã³n antes de completar su postulaciã³n formal';

-- -----------------------------------------------------------------------------
-- tabla: requisitos
-- requisitos definidos por gestiã³n (tã­tulo bachiller, etc.)
-- -----------------------------------------------------------------------------
create table requisitos (
    id_requisito    bigserial       primary key,
    id_gestion      bigint          not null references gestiones_admision(id_gestion),
    nombre_requisito varchar(100)   not null,
    descripcion     text,
    obligatorio     boolean         not null default true,
    estado_activo   boolean         not null default true
);

-- -----------------------------------------------------------------------------
-- tabla: requisitos_presentados
-- documentos presentados por cada prepostulante
-- -----------------------------------------------------------------------------
create table requisitos_presentados (
    id_requisito_presentado bigserial   primary key,
    id_prepostulante        bigint      not null references prepostulantes(id_prepostulante),
    id_requisito            bigint      not null references requisitos(id_requisito),
    archivo_url             text,                       -- url al archivo subido (cloud storage)
    estado_revision         varchar(30) not null default 'pendiente'
                                check (estado_revision in ('pendiente','aprobado','rechazado','observado')),
    observacion             text,
    fecha_presentacion      timestamp   not null default now(),
    fecha_revision          timestamp,
    revisado_por            bigint      references usuarios(id_usuario),
    unique (id_prepostulante, id_requisito)
);

-- -----------------------------------------------------------------------------
-- tabla: observaciones_requisito
-- historial de observaciones sobre un requisito presentado
-- -----------------------------------------------------------------------------
create table observaciones_requisito (
    id_observacion              bigserial   primary key,
    id_requisito_presentado     bigint      not null references requisitos_presentados(id_requisito_presentado),
    id_usuario                  bigint      not null references usuarios(id_usuario),
    motivo                      text        not null,
    created_at                  timestamp   not null default now()
);

-- -----------------------------------------------------------------------------
-- tabla: pagos
-- registro de pagos realizados por prepostulantes
-- -----------------------------------------------------------------------------
create table pagos (
    id_pago             bigserial       primary key,
    id_prepostulante    bigint          not null references prepostulantes(id_prepostulante),
    codigo_pago         varchar(60)     not null unique,
    monto               numeric(10,2)   not null check (monto > 0),
    metodo_pago         varchar(50),                    -- ej: 'tarjeta', 'transferencia', 'qr'
    estado_pago         varchar(30)     not null default 'pendiente'
                            check (estado_pago in ('pendiente','pagado','rechazado','reembolsado')),
    comprobante_url     text,
    referencia_pasarela varchar(120),                   -- cã³digo de la pasarela de pago
    fecha_pago          timestamp,
    created_at          timestamp       not null default now(),
    updated_at          timestamp       not null default now()
);

-- -----------------------------------------------------------------------------
-- tabla: transacciones_pago
-- detalle de cada intento de transacciã³n (log de la pasarela)
-- -----------------------------------------------------------------------------
create table transacciones_pago (
    id_transaccion      bigserial       primary key,
    id_pago             bigint          not null references pagos(id_pago),
    proveedor           varchar(60)     not null,       -- ej: 'paypal', 'stripe', 'tigo money'
    codigo_transaccion  varchar(120)    not null,
    estado_transaccion  varchar(30)     not null,
    payload_json        jsonb,                          -- respuesta completa de la pasarela
    created_at          timestamp       not null default now()
);

-- =============================================================================
-- mã“dulo 4: postulantes
-- =============================================================================

-- -----------------------------------------------------------------------------
-- tabla: postulantes
-- registro formal del postulante (tras completar requisitos y pago)
-- -----------------------------------------------------------------------------
create table postulantes (
    id_postulante           bigserial       primary key,
    id_prepostulante        bigint          not null unique references prepostulantes(id_prepostulante),
    id_usuario              bigint          unique references usuarios(id_usuario), -- cuenta generada
    id_gestion              bigint          not null references gestiones_admision(id_gestion),
    carrera_primera_opcion  bigint          not null references carreras(id_carrera),
    carrera_segunda_opcion  bigint          references carreras(id_carrera),
    fecha_nacimiento        date            not null,
    sexo                    varchar(20)     check (sexo in ('masculino','femenino','otro')),
    direccion               text,
    telefono                varchar(30),
    correo                  varchar(120)    not null,
    colegio_procedencia     varchar(160),
    ciudad                  varchar(80),
    titulo_bachiller        boolean         not null default false,
    estado_postulante       varchar(40)     not null default 'inscrito'
                                check (estado_postulante in ('inscrito','en_curso','aprobado','reprobado','dado_de_baja')),
    created_at              timestamp       not null default now(),
    updated_at              timestamp       not null default now()
);

comment on table postulantes is 'postulante formal con todos los datos del proceso de admisiã³n';

-- -----------------------------------------------------------------------------
-- tabla: documentos_postulante
-- documentos subidos directamente por el postulante
-- -----------------------------------------------------------------------------
create table documentos_postulante (
    id_documento    bigserial       primary key,
    id_postulante   bigint          not null references postulantes(id_postulante),
    tipo_documento  varchar(80)     not null,           -- ej: 'ci', 'tã­tulo bachiller', 'foto'
    archivo_url     text            not null,
    estado          varchar(30)     not null default 'pendiente'
                        check (estado in ('pendiente','aprobado','rechazado')),
    created_at      timestamp       not null default now()
);

-- -----------------------------------------------------------------------------
-- tabla: bajas_postulante
-- registro de bajas/anulaciones de inscripciã³n
-- -----------------------------------------------------------------------------
create table bajas_postulante (
    id_baja         bigserial       primary key,
    id_postulante   bigint          not null references postulantes(id_postulante),
    id_usuario      bigint          not null references usuarios(id_usuario),   -- quiã©n dio de baja
    motivo          text            not null,
    fecha_baja      timestamp       not null default now()
);

-- =============================================================================
-- mã“dulo 5: aulas, grupos y horarios
-- =============================================================================

-- -----------------------------------------------------------------------------
-- tabla: aulas
-- aulas fã­sicas disponibles
-- -----------------------------------------------------------------------------
create table aulas (
    id_aula         bigserial       primary key,
    codigo_aula     varchar(30)     not null unique,
    ubicacion       varchar(120),
    capacidad       integer         not null check (capacidad > 0),
    estado_activo   boolean         not null default true
);

-- -----------------------------------------------------------------------------
-- tabla: capacidades_aula
-- capacidad configurada por gestiã³n (puede variar por gestiã³n/protocolo)
-- -----------------------------------------------------------------------------
create table capacidades_aula (
    id_capacidad    bigserial       primary key,
    id_gestion      bigint          not null references gestiones_admision(id_gestion),
    max_estudiantes integer         not null default 70 check (max_estudiantes > 0),
    descripcion     text
);

-- -----------------------------------------------------------------------------
-- tabla: grupos
-- grupos del curso preuniversitario
-- regla: mã¡ximo 70 estudiantes por grupo
-- fã“rmula grupos: ceil(total_inscritos / 70)
-- -----------------------------------------------------------------------------
create table grupos (
    id_grupo            bigserial       primary key,
    id_gestion          bigint          not null references gestiones_admision(id_gestion),
    id_aula             bigint          references aulas(id_aula),
    nombre_grupo        varchar(50)     not null,           -- ej: 'grupo a', 'grupo 1'
    capacidad_maxima    integer         not null default 70 check (capacidad_maxima > 0),
    estado              varchar(30)     not null default 'activo'
                            check (estado in ('activo','cerrado','cancelado')),
    created_at          timestamp       not null default now(),
    updated_at          timestamp       not null default now(),
    unique (id_gestion, nombre_grupo)
);

comment on table grupos is 'grupos del curso preuniversitario. mã¡x 70 alumnos. ceil(inscritos/70) grupos.';

-- -----------------------------------------------------------------------------
-- tabla: horarios
-- bloques horarios disponibles
-- -----------------------------------------------------------------------------
create table horarios (
    id_horario      bigserial       primary key,
    dia_semana      varchar(20)     not null
                        check (dia_semana in ('lunes','martes','miercoles','jueves','viernes','sabado')),
    hora_inicio     time            not null,
    hora_fin        time            not null,
    turno           varchar(30)     not null
                        check (turno in ('maã±ana','tarde','noche')),
    constraint chk_horario check (hora_fin > hora_inicio)
);

-- =============================================================================
-- mã“dulo 6: materias y evaluaciones
-- =============================================================================

-- -----------------------------------------------------------------------------
-- tabla: materias
-- materias del curso preuniversitario
-- segãºn el examen: computaciã³n, matemã¡ticas, inglã©s, fã­sica
-- -----------------------------------------------------------------------------
create table materias (
    id_materia      bigserial       primary key,
    nombre_materia  varchar(80)     not null unique,
    estado_activo   boolean         not null default true
);

-- insertar las 4 materias del sistema
insert into materias (nombre_materia) values
    ('computaciã³n'),
    ('matemã¡ticas'),
    ('inglã©s'),
    ('fã­sica');

-- -----------------------------------------------------------------------------
-- tabla: grupo_horarios
-- asignaciã³n de materia y horario a un grupo (tabla de uniã³n)
-- -----------------------------------------------------------------------------
create table grupo_horarios (
    id_grupo_horario    bigserial   primary key,
    id_grupo            bigint      not null references grupos(id_grupo),
    id_horario          bigint      not null references horarios(id_horario),
    id_materia          bigint      not null references materias(id_materia),
    unique (id_grupo, id_horario, id_materia)
);

-- -----------------------------------------------------------------------------
-- tabla: postulante_grupos
-- asignaciã³n de postulantes a grupos
-- -----------------------------------------------------------------------------
create table postulante_grupos (
    id_postulante_grupo bigserial   primary key,
    id_postulante       bigint      not null references postulantes(id_postulante),
    id_grupo            bigint      not null references grupos(id_grupo),
    fecha_asignacion    timestamp   not null default now(),
    estado              varchar(30) not null default 'activo'
                            check (estado in ('activo','retirado','transferido')),
    unique (id_postulante, id_grupo)
);

-- -----------------------------------------------------------------------------
-- tabla: evaluaciones
-- exã¡menes programados (3 por materia por gestiã³n)
-- regla: solo 3 evaluaciones por materia
-- -----------------------------------------------------------------------------
create table evaluaciones (
    id_evaluacion       bigserial       primary key,
    id_gestion          bigint          not null references gestiones_admision(id_gestion),
    id_materia          bigint          not null references materias(id_materia),
    numero_evaluacion   integer         not null check (numero_evaluacion between 1 and 3),
    porcentaje          numeric(5,2)    not null check (porcentaje > 0 and porcentaje <= 100),
    fecha_evaluacion    date,
    estado              varchar(30)     not null default 'programada'
                            check (estado in ('programada','realizada','cancelada')),
    unique (id_gestion, id_materia, numero_evaluacion)
);

comment on table evaluaciones is 'mã¡ximo 3 evaluaciones por materia por gestiã³n. porcentajes definidos por administraciã³n.';

-- =============================================================================
-- mã“dulo 7: docentes
-- =============================================================================

-- -----------------------------------------------------------------------------
-- tabla: docentes
-- docentes contratados para el curso preuniversitario
-- regla: profesional en el ã¡rea + maestrã­a + diplomado en educaciã³n superior
-- regla: pueden ser asignados de 1 a 4 grupos
-- -----------------------------------------------------------------------------
create table docentes (
    id_docente      bigserial       primary key,
    id_usuario      bigint          unique references usuarios(id_usuario),
    ci              varchar(30)     not null unique,
    nombres         varchar(120)    not null,
    apellidos       varchar(120)    not null,
    profesion       varchar(120),
    correo          varchar(120)    not null unique,
    telefono        varchar(30),
    estado_docente  varchar(30)     not null default 'activo'
                        check (estado_docente in ('activo','inactivo','suspendido')),
    created_at      timestamp       not null default now(),
    updated_at      timestamp       not null default now()
);

comment on table docentes is 'docentes del curso. requisitos: profesional + maestrã­a + diplomado educ. superior. mã¡x 4 grupos.';

-- -----------------------------------------------------------------------------
-- tabla: requisitos_docente
-- documentos presentados por el docente para la contrataciã³n
-- -----------------------------------------------------------------------------
create table requisitos_docente (
    id_requisito_docente    bigserial   primary key,
    id_docente              bigint      not null references docentes(id_docente),
    tipo_requisito          varchar(80) not null,   -- ej: 'tã­tulo profesional', 'maestrã­a', 'diplomado'
    archivo_url             text,
    estado_revision         varchar(30) not null default 'pendiente'
                                check (estado_revision in ('pendiente','aprobado','rechazado')),
    observacion             text,
    created_at              timestamp   not null default now()
);

-- -----------------------------------------------------------------------------
-- tabla: cargas_horarias
-- asignaciã³n de docente a grupo/materia/horario
-- regla: un docente puede tener de 1 a 4 grupos en total
-- -----------------------------------------------------------------------------
create table cargas_horarias (
    id_carga_horaria    bigserial   primary key,
    id_docente          bigint      not null references docentes(id_docente),
    id_grupo            bigint      not null references grupos(id_grupo),
    id_materia          bigint      not null references materias(id_materia),
    id_horario          bigint      not null references horarios(id_horario),
    estado              varchar(30) not null default 'activo'
                            check (estado in ('activo','inactivo')),
    created_at          timestamp   not null default now(),
    unique (id_docente, id_grupo, id_materia)
);

comment on table cargas_horarias is 'un docente puede tener mã¡ximo 4 grupos asignados en total por gestiã³n.';

-- =============================================================================
-- mã“dulo 8: notas y resultados
-- regla: nota_materia = sum(nota_i * porcentaje_i / 100) por cada materia
-- regla: promedio_final = avg(nota_materia) de las 4 materias
-- regla: aprobado si promedio_final >= 60
-- =============================================================================

-- -----------------------------------------------------------------------------
-- tabla: notas
-- notas de cada evaluaciã³n por postulante
-- regla: notas entre 0 y 100
-- -----------------------------------------------------------------------------
create table notas (
    id_nota         bigserial       primary key,
    id_postulante   bigint          not null references postulantes(id_postulante),
    id_evaluacion   bigint          not null references evaluaciones(id_evaluacion),
    id_docente      bigint          not null references docentes(id_docente),
    nota            numeric(5,2)    not null check (nota >= 0 and nota <= 100),
    estado          varchar(30)     not null default 'registrada'
                        check (estado in ('registrada','rectificada','anulada')),
    created_at      timestamp       not null default now(),
    updated_at      timestamp       not null default now(),
    unique (id_postulante, id_evaluacion)
);

-- -----------------------------------------------------------------------------
-- tabla: historial_notas
-- auditorã­a de cambios en notas (trazabilidad)
-- -----------------------------------------------------------------------------
create table historial_notas (
    id_historial_nota   bigserial       primary key,
    id_nota             bigint          not null references notas(id_nota),
    id_usuario          bigint          not null references usuarios(id_usuario),
    nota_anterior       numeric(5,2)    not null,
    nota_nueva          numeric(5,2)    not null,
    motivo              text,
    created_at          timestamp       not null default now()
);

-- -----------------------------------------------------------------------------
-- tabla: resultados
-- resultado final calculado automã¡ticamente por el sistema
-- fã“rmula: promedio_final = promedio de las notas ponderadas por materia
-- paso 1: nota_materia = sum(nota_i * porcentaje_i / 100) para cada materia
-- paso 2: promedio_final = avg(nota_materia) de las 4 materias
-- -----------------------------------------------------------------------------
create table resultados (
    id_resultado        bigserial       primary key,
    id_postulante       bigint          not null unique references postulantes(id_postulante),
    promedio_final      numeric(5,2),
    estado_academico    varchar(20)     not null default 'pendiente'
                            check (estado_academico in ('pendiente','aprobado','reprobado')),
    publicado           boolean         not null default false,
    fecha_calculo       timestamp,
    fecha_publicacion   timestamp
);

comment on table resultados is 'aprobado si promedio_final >= 60. calculado automã¡ticamente.';

-- -----------------------------------------------------------------------------
-- tabla: admisiones
-- registro de admisiã³n a carrera (para postulantes aprobados)
-- regla: si cupos llenos en 1ra opciã³n â†’ asignar 2da opciã³n
-- -----------------------------------------------------------------------------
create table admisiones (
    id_admision         bigserial       primary key,
    id_postulante       bigint          not null unique references postulantes(id_postulante),
    id_resultado        bigint          not null references resultados(id_resultado),
    id_carrera_asignada bigint          not null references carreras(id_carrera),
    opcion_asignada     integer         not null check (opcion_asignada in (1, 2)),
    orden_merito        integer         not null check (orden_merito > 0),
    estado_admision     varchar(30)     not null default 'admitido'
                            check (estado_admision in ('admitido','rechazado','pendiente')),
    created_at          timestamp       not null default now()
);

comment on table admisiones is 'si cupos llenos en 1ra opciã³n, se asigna 2da opciã³n. opcion_asignada: 1 o 2.';

-- =============================================================================
-- mã“dulo 9: asistencias
-- =============================================================================

create table asistencias (
    id_asistencia       bigserial       primary key,
    id_postulante       bigint          not null references postulantes(id_postulante),
    id_grupo            bigint          not null references grupos(id_grupo),
    id_docente          bigint          not null references docentes(id_docente),
    fecha               date            not null,
    estado_asistencia   varchar(20)     not null default 'presente'
                            check (estado_asistencia in ('presente','ausente','tardanza','justificado')),
    observacion         text,
    created_at          timestamp       not null default now(),
    updated_at          timestamp       not null default now(),
    unique (id_postulante, id_grupo, fecha)
);


-- =============================================================================
-- ãndices para optimizaciã“n de consultas
-- =============================================================================

-- seguridad
create index idx_usuarios_email       on usuarios(email);
create index idx_usuarios_auth_uid    on usuarios(auth_uid);
create index idx_usuarios_id_rol      on usuarios(id_rol);
create index idx_bitacoras_id_usuario on bitacoras(id_usuario);
create index idx_bitacoras_created_at on bitacoras(created_at);

-- admisiã³n
create index idx_prepostulantes_ci          on prepostulantes(ci);
create index idx_prepostulantes_id_gestion  on prepostulantes(id_gestion);
create index idx_postulantes_id_gestion     on postulantes(id_gestion);
create index idx_postulantes_estado         on postulantes(estado_postulante);
create index idx_cupos_carrera_gestion      on cupos_carrera(id_gestion);

-- evaluaciones y notas
create index idx_notas_id_postulante    on notas(id_postulante);
create index idx_notas_id_evaluacion    on notas(id_evaluacion);
create index idx_evaluaciones_gestion   on evaluaciones(id_gestion);
create index idx_evaluaciones_materia   on evaluaciones(id_materia);

-- grupos y asistencias
create index idx_grupos_id_gestion          on grupos(id_gestion);
create index idx_postulante_grupos_postulante on postulante_grupos(id_postulante);
create index idx_asistencias_postulante     on asistencias(id_postulante);
create index idx_asistencias_fecha          on asistencias(fecha);
create index idx_cargas_horarias_docente    on cargas_horarias(id_docente);

-- resultados y admisiones
create index idx_resultados_estado          on resultados(estado_academico);
create index idx_admisiones_carrera         on admisiones(id_carrera_asignada);

-- =============================================================================
-- vistas ãštiles para el sistema
-- =============================================================================

-- vista: promedio final por postulante (calculado dinã¡micamente)
-- paso 1: calcula nota ponderada por materia (sum de nota*porcentaje/100)
-- paso 2: promedia las 4 materias (avg de notas por materia)
create or replace view v_promedio_postulante as
select
    sub.id_postulante,
    sub.id_gestion,
    sub.ci,
    sub.nombre_completo,
    round(avg(sub.nota_materia), 2) as promedio_final,
    case
        when avg(sub.nota_materia) >= 60 then 'aprobado'
        else 'reprobado'
    end as estado
from (
    -- subconsulta: nota ponderada por materia
    select
        p.id_postulante,
        p.id_gestion,
        pp.ci,
        pp.nombres || ' ' || pp.apellidos as nombre_completo,
        e.id_materia,
        sum(n.nota * (e.porcentaje / 100.0)) as nota_materia
    from postulantes p
    join prepostulantes pp  on p.id_prepostulante = pp.id_prepostulante
    join notas n            on n.id_postulante = p.id_postulante
    join evaluaciones e     on e.id_evaluacion = n.id_evaluacion
    group by p.id_postulante, p.id_gestion, pp.ci, pp.nombres, pp.apellidos, e.id_materia
) sub
group by sub.id_postulante, sub.id_gestion, sub.ci, sub.nombre_completo;

-- vista: cantidad de grupos necesarios por gestiã³n
-- usa la capacidad configurada en capacidades_aula (cu26) en vez de valor fijo
create or replace view v_grupos_necesarios as
select
    g.id_gestion,
    g.nombre_gestion,
    count(p.id_postulante)                                                  as total_inscritos,
    coalesce(ca.max_estudiantes, 70)                                        as capacidad_configurada,
    ceil(count(p.id_postulante)::float / coalesce(ca.max_estudiantes, 70))::integer as grupos_necesarios
from gestiones_admision g
left join postulantes p on p.id_gestion = g.id_gestion
    and p.estado_postulante not in ('dado_de_baja')
left join capacidades_aula ca on ca.id_gestion = g.id_gestion
group by g.id_gestion, g.nombre_gestion, ca.max_estudiantes;

-- vista: indicadores del dashboard
create or replace view v_dashboard as
select
    g.id_gestion,
    g.nombre_gestion,
    count(distinct p.id_postulante)                                 as total_inscritos,
    count(distinct case when r.estado_academico = 'aprobado'  then p.id_postulante end) as total_aprobados,
    count(distinct case when r.estado_academico = 'reprobado' then p.id_postulante end) as total_reprobados,
    count(distinct gr.id_grupo)                                     as total_grupos
from gestiones_admision g
left join postulantes p  on p.id_gestion = g.id_gestion
left join resultados r   on r.id_postulante = p.id_postulante
left join grupos gr      on gr.id_gestion = g.id_gestion
group by g.id_gestion, g.nombre_gestion;

-- vista: notas completas por postulante y materia
create or replace view v_notas_detalle as
select
    p.id_postulante,
    pp.ci,
    pp.nombres || ' ' || pp.apellidos  as nombre_completo,
    m.nombre_materia,
    e.numero_evaluacion,
    e.porcentaje,
    n.nota,
    round(n.nota * (e.porcentaje / 100.0), 2)  as nota_ponderada
from postulantes p
join prepostulantes pp  on p.id_prepostulante = pp.id_prepostulante
join notas n            on n.id_postulante = p.id_postulante
join evaluaciones e     on e.id_evaluacion = n.id_evaluacion
join materias m         on m.id_materia = e.id_materia
order by p.id_postulante, m.nombre_materia, e.numero_evaluacion;

-- vista: carga horaria de docentes
create or replace view v_carga_docente as
select
    d.id_docente,
    d.nombres || ' ' || d.apellidos    as nombre_docente,
    count(distinct ch.id_grupo)        as total_grupos_asignados,
    case
        when count(distinct ch.id_grupo) > 4 then 'excede lãmite (mã¡x 4)'
        else 'ok'
    end as validacion_carga
from docentes d
left join cargas_horarias ch on ch.id_docente = d.id_docente
join grupos g on g.id_grupo = ch.id_grupo
group by d.id_docente, d.nombres, d.apellidos;

-- =============================================================================
-- funciones y triggers
-- =============================================================================

-- funciã³n: actualizar updated_at automã¡ticamente
create or replace function fn_updated_at()
returns trigger as $$
begin
    new.updated_at = now();
    return new;
end;
$$ language plpgsql;

-- aplicar trigger updated_at a las tablas que lo tienen
create trigger trg_updated_at_usuarios
    before update on usuarios
    for each row execute function fn_updated_at();

create trigger trg_updated_at_postulantes
    before update on postulantes
    for each row execute function fn_updated_at();

create trigger trg_updated_at_grupos
    before update on grupos
    for each row execute function fn_updated_at();

create trigger trg_updated_at_notas
    before update on notas
    for each row execute function fn_updated_at();

-- funciã³n: calcular automã¡ticamente el resultado final del postulante
-- paso 1: nota_materia = sum(nota_i * porcentaje_i / 100) para cada materia
-- paso 2: promedio_final = avg(nota_materia) de las 4 materias
-- paso 3: aprobado si promedio_final >= 60, reprobado si < 60
create or replace function fn_calcular_resultado(p_id_postulante bigint)
returns void as $$
declare
    v_promedio      numeric(5,2);
    v_estado        varchar(20);
    v_id_gestion    bigint;
begin
    -- obtener la gestiã³n del postulante
    select id_gestion into v_id_gestion
    from postulantes where id_postulante = p_id_postulante;

    -- calcular promedio: primero nota ponderada por materia, luego promedio de materias
    select round(avg(nota_materia), 2)
    into v_promedio
    from (
        select e.id_materia,
               sum(n.nota * (e.porcentaje / 100.0)) as nota_materia
        from notas n
        join evaluaciones e on e.id_evaluacion = n.id_evaluacion
        where n.id_postulante = p_id_postulante
          and e.id_gestion = v_id_gestion
        group by e.id_materia
    ) notas_por_materia;

    -- determinar estado
    if v_promedio is null then
        v_estado := 'pendiente';
    elsif v_promedio >= 60 then
        v_estado := 'aprobado';
    else
        v_estado := 'reprobado';
    end if;

    -- insertar o actualizar resultado
    insert into resultados (id_postulante, promedio_final, estado_academico, fecha_calculo)
    values (p_id_postulante, v_promedio, v_estado, now())
    on conflict (id_postulante) do update
        set promedio_final   = excluded.promedio_final,
            estado_academico = excluded.estado_academico,
            fecha_calculo    = now();
end;
$$ language plpgsql;

comment on function fn_calcular_resultado is 'calcula nota ponderada por materia, luego promedio de 4 materias. aprobado si >= 60.';

-- funciã³n: calcular grupos necesarios para una gestiã³n
-- usa la capacidad configurada en capacidades_aula (cu26)
-- fã“rmula: ceil(total_inscritos / capacidad_configurada)
create or replace function fn_calcular_grupos(p_id_gestion bigint)
returns integer as $$
declare
    v_total         integer;
    v_capacidad     integer;
    v_grupos        integer;
begin
    -- contar inscritos activos
    select count(*) into v_total
    from postulantes
    where id_gestion = p_id_gestion
      and estado_postulante not in ('dado_de_baja');

    -- obtener capacidad configurada (cu26), o 70 por defecto
    select coalesce(max_estudiantes, 70) into v_capacidad
    from capacidades_aula
    where id_gestion = p_id_gestion
    order by id_capacidad desc limit 1;

    if v_capacidad is null then
        v_capacidad := 70;
    end if;

    v_grupos := ceil(v_total::float / v_capacidad)::integer;
    return greatest(v_grupos, 0);
end;
$$ language plpgsql;

comment on function fn_calcular_grupos is 'ceil(inscritos / capacidad_configurada). usa capacidades_aula o 70 por defecto.';

-- funciã³n: validar que un docente no supere 4 grupos
create or replace function fn_validar_carga_docente()
returns trigger as $$
declare
    v_total_grupos integer;
begin
    select count(distinct id_grupo)
    into v_total_grupos
    from cargas_horarias
    where id_docente = new.id_docente
      and estado = 'activo';

    if v_total_grupos >= 4 then
        raise exception 'el docente ya tiene 4 grupos asignados (mã¡ximo permitido).';
    end if;

    return new;
end;
$$ language plpgsql;

create trigger trg_validar_carga_docente
    before insert on cargas_horarias
    for each row execute function fn_validar_carga_docente();

-- funciã³n: actualizar cupos ocupados cuando se admite un postulante
create or replace function fn_actualizar_cupos()
returns trigger as $$
begin
    if new.estado_admision = 'admitido' then
        update cupos_carrera
        set cupos_ocupados = cupos_ocupados + 1,
            updated_at = now()
        where id_carrera = new.id_carrera_asignada
          and id_gestion = (
              select id_gestion from postulantes where id_postulante = new.id_postulante
          );
    end if;
    return new;
end;
$$ language plpgsql;

create trigger trg_actualizar_cupos
    after insert on admisiones
    for each row execute function fn_actualizar_cupos();

-- =============================================================================
-- datos iniciales (seed)
-- =============================================================================

-- roles del sistema
insert into roles (nombre_rol, descripcion) values
    ('administrador',   'acceso total al sistema'),
    ('coordinador',     'gestiã³n del proceso de admisiã³n'),
    ('docente',         'registro de notas y asistencias de su carga horaria'),
    ('postulante',      'acceso solo a su informaciã³n personal'),
    ('autoridad',       'consultas y reportes sin modificaciã³n');

-- =============================================================================
-- fin del script
-- tablas: 23 | vistas: 5 | funciones: 4 | triggers: 6 | ãndices: 16
-- =============================================================================

