-- ============================================================
--  POBLACIÓN DE DATOS — Sistema CUP (FICCT - UAGRM)
--  Motor: PostgreSQL 15+
--  Gestión: 1-2026
--  Nota: passwords en texto real se mostrarían como hash bcrypt
--        en producción usar: crypt('password', gen_salt('bf'))
-- ============================================================

-- ============================================================
--  1. ROL
-- ============================================================
INSERT INTO rol (id_rol, nombre_rol, descripcion) VALUES
(1, 'ADMIN',      'Administrador con acceso total al sistema CUP'),
(2, 'DOCENTE',    'Docente con acceso a registro de notas y grupos asignados'),
(3, 'POSTULANTE', 'Aspirante al curso de nivelación con acceso a su perfil y resultados');

-- ============================================================
--  2. USUARIO  (passwords: Admin123!, Docente01!, Postulante01! ... en hash bcrypt simulado)
-- ============================================================
INSERT INTO usuario (id_usuario, ci, nombre, apellido, email, password_hash, id_rol, activo) VALUES
-- Administradores
(1,  '5012347',   'Carlos',    'Vaca Diez',      'cvacadiez@ficct.uagrm.edu.bo',   '$2a$12$AdmHash0000000000001', 1, TRUE),
(2,  '5089231',   'Lorena',    'Antelo Suárez',  'lantelo@ficct.uagrm.edu.bo',      '$2a$12$AdmHash0000000000002', 1, TRUE),
-- Docentes
(3,  '4123456',   'Hugo',      'Rocha Vargas',   'hrocha@ficct.uagrm.edu.bo',       '$2a$12$DocHash0000000000001', 2, TRUE),
(4,  '4234567',   'Patricia',  'Méndez Torres',  'pmendez@ficct.uagrm.edu.bo',      '$2a$12$DocHash0000000000002', 2, TRUE),
(5,  '4345678',   'Rodrigo',   'Salinas Paz',    'rsalinas@ficct.uagrm.edu.bo',     '$2a$12$DocHash0000000000003', 2, TRUE),
(6,  '4456789',   'Carla',     'Ibáñez Vaca',    'cibanez@ficct.uagrm.edu.bo',      '$2a$12$DocHash0000000000004', 2, TRUE),
(7,  '4567890',   'Marcelo',   'Torrez Aguilar', 'mtorrez@ficct.uagrm.edu.bo',      '$2a$12$DocHash0000000000005', 2, TRUE),
-- Postulantes
(8,  '8123456',   'Ana',       'Flores Quiroga', 'ana.flores@gmail.com',            '$2a$12$PosHash0000000000001', 3, TRUE),
(9,  '8234567',   'Luis',      'Peredo Castro',  'luis.peredo@gmail.com',           '$2a$12$PosHash0000000000002', 3, TRUE),
(10, '8345678',   'Valeria',   'Guzmán Ríos',    'valeria.guzman@gmail.com',        '$2a$12$PosHash0000000000003', 3, TRUE),
(11, '8456789',   'Diego',     'Montero Suárez', 'diego.montero@gmail.com',         '$2a$12$PosHash0000000000004', 3, TRUE),
(12, '8567890',   'Daniela',   'Rivero Mamani',  'daniela.rivero@gmail.com',        '$2a$12$PosHash0000000000005', 3, TRUE),
(13, '8678901',   'Sergio',    'Álvarez Pinto',  'sergio.alvarez@gmail.com',        '$2a$12$PosHash0000000000006', 3, TRUE),
(14, '8789012',   'Camila',    'Durán Heredia',  'camila.duran@gmail.com',          '$2a$12$PosHash0000000000007', 3, TRUE),
(15, '8890123',   'Fernanda',  'Cossío Velasco', 'fernanda.cossio@gmail.com',       '$2a$12$PosHash0000000000008', 3, TRUE);

SELECT setval('usuario_id_usuario_seq', 15);

-- ============================================================
--  3. BITACORA_AUDITORIA
-- ============================================================
INSERT INTO bitacora_auditoria (id_usuario, accion, tabla_afectada, descripcion, ip_origen, fecha_hora) VALUES
(1, 'LOGIN',  NULL,       'Inicio de sesión exitoso - Admin',                      '192.168.1.10', '2026-03-01 08:00:00-04'),
(1, 'INSERT', 'carrera',  'Registro de carrera ING-SIS',                           '192.168.1.10', '2026-03-01 08:15:00-04'),
(1, 'INSERT', 'periodo_admision', 'Creación del período Gestión 1-2026',           '192.168.1.10', '2026-03-01 08:30:00-04'),
(8, 'LOGIN',  NULL,       'Inicio de sesión - Postulante Ana Flores',              '190.121.44.12','2026-03-05 09:10:00-04'),
(8, 'INSERT', 'postulante','Registro de postulante Ana Flores Quiroga',            '190.121.44.12','2026-03-05 09:12:00-04'),
(3, 'LOGIN',  NULL,       'Inicio de sesión - Docente Hugo Rocha',                 '192.168.1.21', '2026-03-10 07:45:00-04'),
(3, 'INSERT', 'evaluacion','Registro de notas materia MAT - grupo A',              '192.168.1.21', '2026-03-10 10:00:00-04'),
(1, 'UPDATE', 'postulacion','Ejecución algoritmo admisión CU17 - 8 postulantes',   '192.168.1.10', '2026-04-01 14:00:00-04'),
(1, 'EXPORT', 'reporte',  'Exportación Dashboard KPIs - PDF',                      '192.168.1.10', '2026-04-02 09:00:00-04'),
(2, 'LOGIN',  NULL,       'Inicio de sesión - Admin Lorena Antelo',                '192.168.1.11', '2026-04-05 08:00:00-04');

-- ============================================================
--  4. PARAMETRO_SISTEMA
-- ============================================================
INSERT INTO parametro_sistema (clave, valor, descripcion) VALUES
('nota_minima_aprobacion', '60',   'Promedio mínimo para aprobar el CUP'),
('capacidad_maxima_grupo', '80',   'Divisor del algoritmo CEIL(n/80) para calcular grupos (CU19)'),
('monto_inscripcion_bs',   '150',  'Monto de inscripción en bolivianos (CU07)'),
('periodo_activo_id',      '1',    'ID del período de admisión actualmente activo'),
('max_notas_por_materia',  '3',    'Número máximo de calificaciones por materia por postulante'),
('intentos_login_max',     '5',    'Máximo de intentos fallidos antes de bloquear cuenta');

-- ============================================================
--  5. PERIODO_ADMISION
-- ============================================================
INSERT INTO periodo_admision (id_periodo, nombre, fecha_inicio, fecha_fin, estado) VALUES
(1, 'Gestión 1-2026', '2026-03-01', '2026-06-30', 'ACTIVO');

-- ============================================================
--  6. CARRERA
-- ============================================================
INSERT INTO carrera (id_carrera, nombre_carrera, sigla, cupos_totales, cupos_disponibles, activo) VALUES
(1, 'Ingeniería en Sistemas Computacionales',     'ING-SIS', 200, 155, TRUE),
(2, 'Ingeniería Informática',                     'ING-INF', 150, 120, TRUE),
(3, 'Ingeniería en Redes y Telecomunicaciones',   'ING-RED', 180, 145, TRUE),
(4, 'Robótica',          'ING-ROB', 100,  88, TRUE);

-- ============================================================
--  7. POSTULANTE
-- ============================================================
INSERT INTO postulante (id_postulante, id_usuario, ci, nombre, apellido, fecha_nacimiento, telefono, colegio_origen) VALUES
(1, 8,  '8123456', 'Ana',     'Flores Quiroga', '2007-04-12', '77812345', 'Colegio Nacional Florida'),
(2, 9,  '8234567', 'Luis',    'Peredo Castro',  '2007-08-23', '77823456', 'Colegio Santa Ana'),
(3, 10, '8345678', 'Valeria', 'Guzmán Ríos',    '2006-11-05', '77834567', 'Colegio Juan Pablo II'),
(4, 11, '8456789', 'Diego',   'Montero Suárez', '2007-01-30', '77845678', 'U.E. La Salle'),
(5, 12, '8567890', 'Daniela', 'Rivero Mamani',  '2006-07-17', '77856789', 'U.E. Santo Domingo'),
(6, 13, '8678901', 'Sergio',  'Álvarez Pinto',  '2007-02-28', '77867890', 'Colegio Nacional Florida'),
(7, 14, '8789012', 'Camila',  'Durán Heredia',  '2006-09-14', '77878901', 'U.E. Santa Teresa'),
(8, 15, '8890123', 'Fernanda','Cossío Velasco',  '2007-05-03', '77889012', 'Colegio Juan Pablo II');

SELECT setval('postulante_id_postulante_seq', 8);

-- ============================================================
--  8. POSTULACION
-- ============================================================
INSERT INTO postulacion (id_postulacion, id_postulante, id_carrera_opcion1, id_carrera_opcion2, puntaje_total, estado, id_carrera_asignada, fecha_postulacion) VALUES
(1, 1, 1, 2, 78.33, 'ADMITIDO',  1, '2026-03-05 09:20:00-04'),
(2, 2, 1, 3, 65.00, 'ADMITIDO',  1, '2026-03-06 10:00:00-04'),
(3, 3, 2, 1, 82.67, 'ADMITIDO',  2, '2026-03-07 11:15:00-04'),
(4, 4, 1, 5, 55.33, 'REPROBADO', NULL,'2026-03-08 08:30:00-04'),
(5, 5, 3, 2, 70.00, 'ADMITIDO',  3, '2026-03-09 09:45:00-04'),
(6, 6, 1, 4, 91.00, 'ADMITIDO',  1, '2026-03-10 10:30:00-04'),
(7, 7, 4, 1, 60.33, 'ADMITIDO',  4, '2026-03-11 11:00:00-04'),
(8, 8, 2, 3, 48.00, 'REPROBADO', NULL,'2026-03-12 08:00:00-04');

-- ============================================================
--  9. DOCUMENTO
-- ============================================================
INSERT INTO documento (id_postulante, tipo_documento, nombre_archivo, ruta_archivo, estado_verificacion) VALUES
(1, 'CI',                  'ci_ana_flores.pdf',      '/uploads/docs/ci/ci_ana_flores.pdf',        'VERIFICADO'),
(1, 'CERTIFICADO_BACHILLER','bach_ana_flores.pdf',   '/uploads/docs/bach/bach_ana_flores.pdf',    'VERIFICADO'),
(1, 'COMPROBANTE_PAGO',    'pago_ana_flores.pdf',    '/uploads/docs/pagos/pago_ana_flores.pdf',   'VERIFICADO'),
(2, 'CI',                  'ci_luis_peredo.pdf',     '/uploads/docs/ci/ci_luis_peredo.pdf',       'VERIFICADO'),
(2, 'CERTIFICADO_BACHILLER','bach_luis_peredo.pdf',  '/uploads/docs/bach/bach_luis_peredo.pdf',   'VERIFICADO'),
(3, 'CI',                  'ci_valeria_guzman.pdf',  '/uploads/docs/ci/ci_valeria_guzman.pdf',    'VERIFICADO'),
(3, 'CERTIFICADO_BACHILLER','bach_valeria.pdf',      '/uploads/docs/bach/bach_valeria.pdf',       'PENDIENTE'),
(4, 'CI',                  'ci_diego_montero.pdf',   '/uploads/docs/ci/ci_diego_montero.pdf',     'VERIFICADO'),
(5, 'CI',                  'ci_daniela_rivero.pdf',  '/uploads/docs/ci/ci_daniela_rivero.pdf',    'VERIFICADO'),
(5, 'COMPROBANTE_PAGO',    'pago_daniela.pdf',       '/uploads/docs/pagos/pago_daniela.pdf',      'VERIFICADO');

-- ============================================================
-- 10. PAGO
-- ============================================================
INSERT INTO pago (id_postulante, monto, concepto, estado_pago, codigo_comprobante, fecha_pago) VALUES
(1, 150.00, 'Inscripción CUP Gestión 1-2026', 'CONFIRMADO', 'TIGO-20260305-000001', '2026-03-05 09:15:00-04'),
(2, 150.00, 'Inscripción CUP Gestión 1-2026', 'CONFIRMADO', 'TIGO-20260306-000002', '2026-03-06 09:55:00-04'),
(3, 150.00, 'Inscripción CUP Gestión 1-2026', 'CONFIRMADO', 'BNB-20260307-000003',  '2026-03-07 11:10:00-04'),
(4, 150.00, 'Inscripción CUP Gestión 1-2026', 'CONFIRMADO', 'TIGO-20260308-000004', '2026-03-08 08:25:00-04'),
(5, 150.00, 'Inscripción CUP Gestión 1-2026', 'CONFIRMADO', 'BNB-20260309-000005',  '2026-03-09 09:40:00-04'),
(6, 150.00, 'Inscripción CUP Gestión 1-2026', 'CONFIRMADO', 'TIGO-20260310-000006', '2026-03-10 10:25:00-04'),
(7, 150.00, 'Inscripción CUP Gestión 1-2026', 'CONFIRMADO', 'BNB-20260311-000007',  '2026-03-11 10:55:00-04'),
(8, 150.00, 'Inscripción CUP Gestión 1-2026', 'RECHAZADO',  NULL,                   '2026-03-12 07:55:00-04');

-- ============================================================
-- 11. MATERIA
-- ============================================================
INSERT INTO materia (id_materia, nombre_materia, sigla, horas_semana, activo) VALUES
(1, 'Matemáticas',  'MAT', 6, TRUE),
(2, 'Física',       'FIS', 4, TRUE),
(3, 'Química',      'QUI', 4, TRUE);

-- ============================================================
-- 12. EVALUACION
--     (promedio es GENERATED ALWAYS AS, no se inserta)
-- ============================================================
INSERT INTO evaluacion (id_postulante, id_materia, nota1, nota2, nota3, estado, fecha_evaluacion) VALUES
-- Ana Flores — Aprobada (prom 78.33)
(1, 1, 80.00, 75.00, 82.00, 'APROBADO',  '2026-03-25 10:00:00-04'),
(1, 2, 77.00, 78.00, 76.00, 'APROBADO',  '2026-03-25 10:00:00-04'),
(1, 3, 79.00, 80.00, 78.00, 'APROBADO',  '2026-03-25 10:00:00-04'),
-- Luis Peredo — Aprobado (prom 65)
(2, 1, 60.00, 70.00, 65.00, 'APROBADO',  '2026-03-25 10:30:00-04'),
(2, 2, 62.00, 68.00, 65.00, 'APROBADO',  '2026-03-25 10:30:00-04'),
(2, 3, 65.00, 63.00, 67.00, 'APROBADO',  '2026-03-25 10:30:00-04'),
-- Valeria Guzmán — Aprobada (prom 82.67)
(3, 1, 85.00, 80.00, 84.00, 'APROBADO',  '2026-03-25 11:00:00-04'),
(3, 2, 82.00, 83.00, 81.00, 'APROBADO',  '2026-03-25 11:00:00-04'),
(3, 3, 84.00, 82.00, 83.00, 'APROBADO',  '2026-03-25 11:00:00-04'),
-- Diego Montero — Reprobado (prom 55.33)
(4, 1, 50.00, 58.00, 55.00, 'REPROBADO', '2026-03-25 11:30:00-04'),
(4, 2, 52.00, 60.00, 54.00, 'REPROBADO', '2026-03-25 11:30:00-04'),
(4, 3, 55.00, 57.00, 53.00, 'REPROBADO', '2026-03-25 11:30:00-04'),
-- Daniela Rivero — Aprobada (prom 70)
(5, 1, 70.00, 68.00, 72.00, 'APROBADO',  '2026-03-25 12:00:00-04'),
(5, 2, 71.00, 69.00, 70.00, 'APROBADO',  '2026-03-25 12:00:00-04'),
(5, 3, 70.00, 70.00, 70.00, 'APROBADO',  '2026-03-25 12:00:00-04'),
-- Sergio Álvarez — Aprobado (prom 91)
(6, 1, 92.00, 90.00, 93.00, 'APROBADO',  '2026-03-25 12:30:00-04'),
(6, 2, 90.00, 92.00, 91.00, 'APROBADO',  '2026-03-25 12:30:00-04'),
(6, 3, 91.00, 90.00, 91.00, 'APROBADO',  '2026-03-25 12:30:00-04'),
-- Camila Durán — Aprobada (prom 60.33)
(7, 1, 60.00, 61.00, 60.00, 'APROBADO',  '2026-03-25 13:00:00-04'),
(7, 2, 60.00, 60.00, 61.00, 'APROBADO',  '2026-03-25 13:00:00-04'),
(7, 3, 60.00, 60.00, 61.00, 'APROBADO',  '2026-03-25 13:00:00-04'),
-- Fernanda Cossío — Reprobada (prom 48)
(8, 1, 45.00, 50.00, 48.00, 'REPROBADO', '2026-03-25 13:30:00-04'),
(8, 2, 47.00, 49.00, 48.00, 'REPROBADO', '2026-03-25 13:30:00-04'),
(8, 3, 48.00, 47.00, 49.00, 'REPROBADO', '2026-03-25 13:30:00-04');

-- ============================================================
-- 13. GRUPO
--     8 postulantes aprobados → CEIL(6/80) = 1 grupo
--     Se crean 2 grupos de demostración
-- ============================================================
INSERT INTO grupo (id_grupo, nombre_grupo, capacidad_maxima, aula, turno, activo) VALUES
(1, 'A-MAÑ-2026', 80, 'Aula 101 - FICCT', 'MAÑANA', TRUE),
(2, 'B-TAR-2026', 80, 'Aula 205 - FICCT', 'TARDE',  TRUE);

-- ============================================================
-- 14. GRUPO_POSTULANTE  (solo los 6 admitidos)
-- ============================================================
INSERT INTO grupo_postulante (id_grupo, id_postulante, fecha_asignacion) VALUES
(1, 1, '2026-04-01 14:30:00-04'),   -- Ana
(1, 2, '2026-04-01 14:30:00-04'),   -- Luis
(1, 3, '2026-04-01 14:30:00-04'),   -- Valeria
(1, 5, '2026-04-01 14:30:00-04'),   -- Daniela
(2, 6, '2026-04-01 14:30:00-04'),   -- Sergio
(2, 7, '2026-04-01 14:30:00-04');   -- Camila

-- ============================================================
-- 15. GRUPO_MATERIA
-- ============================================================
INSERT INTO grupo_materia (id_grupo, id_materia, horario) VALUES
(1, 1, 'Lunes-Miércoles-Viernes 08:00-10:00'),
(1, 2, 'Martes-Jueves 08:00-10:00'),
(1, 3, 'Lunes-Miércoles 10:00-12:00'),
(2, 1, 'Lunes-Miércoles-Viernes 14:00-16:00'),
(2, 2, 'Martes-Jueves 14:00-16:00'),
(2, 3, 'Lunes-Miércoles 16:00-18:00');

-- ============================================================
-- 16. DOCENTE
-- ============================================================
INSERT INTO docente (id_docente, id_usuario, especialidad, titulo_academico, telefono, activo) VALUES
(1, 3, 'Cálculo Diferencial e Integral', 'MAESTRIA',   '70312345', TRUE),
(2, 4, 'Física Mecánica y Ondas',        'MAESTRIA',   '70323456', TRUE),
(3, 5, 'Química General e Inorgánica',   'LICENCIATURA','70334567', TRUE),
(4, 6, 'Álgebra Lineal',                 'DOCTORADO',  '70345678', TRUE),
(5, 7, 'Electromagnetismo',              'MAESTRIA',   '70356789', TRUE);

-- ============================================================
-- 17. ASIGNACION_DOCENTE
-- ============================================================
INSERT INTO asignacion_docente (id_docente, id_grupo, id_materia, fecha_asignacion, activo) VALUES
(1, 1, 1, '2026-04-02 08:00:00-04', TRUE),   -- Hugo → Grupo A, Matemáticas
(2, 1, 2, '2026-04-02 08:00:00-04', TRUE),   -- Patricia → Grupo A, Física
(3, 1, 3, '2026-04-02 08:00:00-04', TRUE),   -- Rodrigo → Grupo A, Química
(4, 2, 1, '2026-04-02 08:00:00-04', TRUE),   -- Carla → Grupo B, Matemáticas
(5, 2, 2, '2026-04-02 08:00:00-04', TRUE),   -- Marcelo → Grupo B, Física
(3, 2, 3, '2026-04-02 08:00:00-04', TRUE);   -- Rodrigo → Grupo B, Química (carga compartida)

-- ============================================================
--  VERIFICACIÓN RÁPIDA (ejecutar después del INSERT)
-- ============================================================
-- SELECT COUNT(*) FROM usuario;           -- esperado: 15
-- SELECT COUNT(*) FROM postulante;        -- esperado: 8
-- SELECT COUNT(*) FROM evaluacion;        -- esperado: 24
-- SELECT COUNT(*) FROM grupo_postulante;  -- esperado: 6
-- SELECT id_postulante, id_materia, nota1, nota2, nota3, promedio
--   FROM evaluacion ORDER BY id_postulante, id_materia;  -- promedio calculado automáticamente

-- ============================================================
--  FIN DE LA POBLACIÓN DE DATOS
-- ============================================================
