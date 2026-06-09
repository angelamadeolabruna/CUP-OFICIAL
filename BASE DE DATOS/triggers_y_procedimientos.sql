-- ============================================================
--  TRIGGERS Y PROCEDIMIENTOS ALMACENADOS
--  Sistema CUP (FICCT - UAGRM)
-- ============================================================

-- ============================================================
-- 1. PROCEDIMIENTO ALMACENADO: Algoritmo de Admisión (CU17)
-- ============================================================
-- Este procedimiento reemplaza decenas de líneas de código PHP.
-- Recorre todos los postulantes APROBADOS, del mejor promedio al peor,
-- y les asigna su 1ra o 2da opción descontando los cupos automáticamente.

CREATE OR REPLACE PROCEDURE sp_ejecutar_admision_cupos()
LANGUAGE plpgsql
AS $$
DECLARE
    r_postulante RECORD;
    v_cupos_disp INT;
BEGIN
    -- Recorrer postulantes aprobados ordenados por puntaje_total de forma Descendente (los mejores primero)
    FOR r_postulante IN 
        SELECT id_postulacion, id_carrera_opcion1, id_carrera_opcion2 
        FROM postulacion 
        WHERE estado = 'APROBADO'
        ORDER BY puntaje_total DESC
    LOOP
        -- Paso 1: Intentar en la 1ra Opción
        SELECT cupos_disponibles INTO v_cupos_disp FROM carrera WHERE id_carrera = r_postulante.id_carrera_opcion1;
        
        IF v_cupos_disp > 0 THEN
            -- Asignar a la 1ra opción y cambiar estado
            UPDATE postulacion 
            SET id_carrera_asignada = r_postulante.id_carrera_opcion1, estado = 'ADMITIDO' 
            WHERE id_postulacion = r_postulante.id_postulacion;
            
            -- Descontar el cupo
            UPDATE carrera SET cupos_disponibles = cupos_disponibles - 1 WHERE id_carrera = r_postulante.id_carrera_opcion1;
        ELSE
            -- Paso 2: Si no hay cupo, intentar en la 2da Opción
            IF r_postulante.id_carrera_opcion2 IS NOT NULL THEN
                SELECT cupos_disponibles INTO v_cupos_disp FROM carrera WHERE id_carrera = r_postulante.id_carrera_opcion2;
                
                IF v_cupos_disp > 0 THEN
                    -- Asignar a la 2da opción
                    UPDATE postulacion 
                    SET id_carrera_asignada = r_postulante.id_carrera_opcion2, estado = 'ADMITIDO' 
                    WHERE id_postulacion = r_postulante.id_postulacion;
                    
                    -- Descontar el cupo
                    UPDATE carrera SET cupos_disponibles = cupos_disponibles - 1 WHERE id_carrera = r_postulante.id_carrera_opcion2;
                ELSE
                    -- Se quedó sin plaza en ambas opciones
                    UPDATE postulacion SET estado = 'REPROBADO' WHERE id_postulacion = r_postulante.id_postulacion;
                END IF;
            END IF;
        END IF;
    END LOOP;
END;
$$;

COMMENT ON PROCEDURE sp_ejecutar_admision_cupos() IS 'Ejecuta la asignación masiva por orden de mérito y opciones de carrera (CU17).';


-- ============================================================
-- 2. TRIGGER: Auditoría automática de Cambio de Notas (Seguridad)
-- ============================================================
-- Este trigger vigila la tabla de 'evaluacion'. Si un docente malintencionado
-- modifica una nota ya registrada, el trigger automáticamente captura el 
-- evento y lo guarda en la bitácora para que el Administrador lo descubra.

CREATE OR REPLACE FUNCTION fn_auditar_modificacion_notas()
RETURNS TRIGGER AS $$
BEGIN
    -- Insertar un registro en la tabla de auditoría indicando qué cambió
    INSERT INTO bitacora_auditoria (id_usuario, accion, tabla_afectada, descripcion)
    VALUES (
        NULL, -- Se asume usuario del sistema si no se pasa contexto
        'UPDATE', 
        'evaluacion', 
        'ALERTA: Se modificaron las notas del postulante ID: ' || NEW.id_postulante || '. Nota1 anterior: ' || COALESCE(OLD.nota1, 0) || ' -> Nueva: ' || COALESCE(NEW.nota1, 0)
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_auditar_notas
AFTER UPDATE ON evaluacion
FOR EACH ROW
-- Solo se dispara si realmente cambiaron los valores numéricos de las notas
WHEN (OLD.nota1 IS DISTINCT FROM NEW.nota1 OR 
      OLD.nota2 IS DISTINCT FROM NEW.nota2 OR 
      OLD.nota3 IS DISTINCT FROM NEW.nota3)
EXECUTE FUNCTION fn_auditar_modificacion_notas();

COMMENT ON FUNCTION fn_auditar_modificacion_notas() IS 'Función del trigger para auditar la alteración de notas.';
