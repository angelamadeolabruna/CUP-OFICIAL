# PROTOTIPO-CUP-LIMPIO

Base limpia del sistema CUP FICCT reconstruida desde la documentacion PUDS.

## Regla principal

La base de datos se modifica solo con migraciones Laravel. No se deben usar scripts sueltos tipo `fix_*`, `cleanup_*` o cambios manuales en Supabase.

## Orden de implementacion

1. Seguridad y Acceso.
2. Prepostulacion, requisitos y pagos.
3. Registro oficial del postulante.
4. Evaluacion academica.
5. Admision por cupos.
6. Logistica de grupos, aulas y horarios.
7. Docentes y asistencia.
8. Reportes, dashboard y bitacora.

## Migracion principal

Archivo:

`database/migrations/2026_06_07_000001_create_cup_schema.php`

Incluye la base completa del sistema:

- roles, usuarios, sesiones, tokens_recuperacion, bitacoras
- gestiones_admision, carreras, cupos_carrera
- prepostulantes, requisitos, requisitos_presentados, pagos
- datos_registro_temporal, postulantes, documentos_postulante
- aulas, grupos, horarios, materias, evaluaciones, notas
- resultados, admisiones
- docentes, cargas_horarias, asistencias
- reportes, archivos_exportados, comandos_voz, dashboard_metricas

## Siguiente paso tecnico

Implementar el paquete 1: Seguridad y Acceso.

Controladores esperados:

- `Seguridad/AuthController`
- `Seguridad/PasswordResetController`
- `Seguridad/PasswordController`
- `Admin/UsuarioController`
- `Admin/UsuarioImportController`

Servicios esperados:

- `Seguridad/SupabaseAuthService`
- `Seguridad/BitacoraService`

Los nombres deben mantenerse consistentes con los diagramas de comunicacion y analisis de clases.

## Validacion realizada

Estado actual verificado:

- `composer install` ejecutado correctamente.
- `php artisan --version` responde Laravel 12.61.0.
- `php artisan route:list` responde correctamente.
- `php artisan migrate --seed --force` ejecutado correctamente con SQLite local.
- Laravel confirmo 39 tablas creadas.

Nota: `php artisan db:show --counts` mostro las 39 tablas, pero se detuvo al formatear conteos porque la instalacion local de PHP no tiene habilitada la extension `intl`. No afecta la migracion ni el funcionamiento base.
