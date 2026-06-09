# Requerimientos Oficiales de la Ingeniera: Sistema CUP FICCT - UAGRM

Este documento contiene la recopilación exhaustiva y detallada de todos los requisitos, restricciones, reglas de negocio y plazos establecidos por la docente **Ing. Angélica Garzón Cuéllar** para el proyecto del Segundo Parcial de la materia **Sistemas Informáticos I (FICCT - UAGRM)**.

---

## 📅 Cronograma y Hitos de Presentación

La defensa y entrega del proyecto se divide en hitos estrictos y obligatorios. El desarrollo es **iterativo e incremental** (2 Ciclos en total):

1. **Presentación 1 (Ciclo 1): Domingo, 31 de Mayo de 2026**
   * **Entregable:** 50% de avance en la codificación de la aplicación web y el documento metodológico completo para el Ciclo 1.
   * **Requisito:** Si un caso de uso está documentado en el perfil de esta entrega, **debe estar programado y funcional**. Si la documentación está incompleta, se pierde el derecho a este parcial.
2. **Presentación 2 (Ciclo 2): Martes, 9 de Junio de 2026**
   * **Entregable:** 100% de avance en el software y la documentación metodológica total integrada.
3. **Defensa Final: Jueves, 11 de Junio de 2026**
   * **Entregable:** Defensa del sistema, código fuente completo y demostración en producción.
4. **Requisitos de Entrega:**
   * Todo el código debe estar subido a un repositorio Git (GitHub/GitLab).
   * Se deben generar y enviar los **enlaces (URL) y códigos QR** correspondientes a:
     * El Frontend desplegado.
     * El Backend desplegado.
     * La Base de Datos en producción.

---

## 💻 Stack Tecnológico Obligatorio

* **Plataforma:** Aplicación Web.
* **Diseño:** Debe ser **100% responsive** (adaptable a dispositivos móviles, tablets y computadoras de escritorio).
* **Lenguaje Backend & Frontend:** **PHP**. Se puede estructurar la autenticación, autorización y las vistas completas utilizando PHP como framework o arquitectura integrada (MVC).
* **Base de Datos:** **PostgreSQL**.
* **Infraestructura:** Desplegado en **servidores en la nube** (producción real).

---

## 🏢 Ámbito y Alcance del Sistema (Límites y Fronteras)

* **Modelo:** El sistema se limitará **exclusivamente a la facultad FICCT** (no a toda la universidad).
* **Carreras de la FICCT (4):**
  1. Ingeniería en Sistemas.
  2. Ingeniería Informática.
  3. Ingeniería en Redes y Telecomunicaciones (llamada abreviadamente "Ing. en Redes" o "Redes").
  4. Ingeniería Robótica.
* **Límite del Sistema:** El sistema gestiona todo el proceso del CUP hasta la determinación de los estudiantes admitidos. Los procesos posteriores (como matriculación formal en oficinas centrales de la UAGRM) quedan **fuera del alcance**.

---

## 📋 Reglas de Negocio y Flujo Transaccional

### 1. Gestión de Postulantes
* **Estado:** Los estudiantes se registran como **Postulantes** (no son alumnos regulares de la universidad hasta que aprueben el CUP y se defina su admisión). Si reprueban el curso, no ingresan a la universidad.
* **Opciones de Carrera:** Al inscribirse, cada postulante debe elegir obligatoriamente:
  * **1ra Opción** de carrera (de entre las 4 de la FICCT).
  * **2da Opción** de carrera (de entre las 4 de la FICCT).
* **Requisitos Físicos (Documentos entregados):** Los postulantes deben cumplir con la entrega de un conjunto de requisitos que la aplicación debe rastrear y registrar:
  * Fotocopia de la Cédula de Identidad (CI).
  * Libreta escolar (libreta de colegio).
  * Título de bachiller.

### 2. Gestión de Aulas y Cálculo Automático de Grupos
* **Restricción de Aula:** Límite estricto de **60 estudiantes por aula/grupo** para garantizar condiciones óptimas de estudio.
* **Cálculo de Grupos:** El sistema debe calcular de forma automática la cantidad de grupos que la facultad debe abrir en base al número total de estudiantes inscritos.
  * *Fórmula:* $Grupos = \lceil Inscritos / 60 \rceil$ (Redondeo hacia arriba).
  * *Ejemplo:* Si hay 1000 estudiantes inscritos, el sistema calcula automáticamente que se deben habilitar **17 grupos**.

### 3. Selección y Restricciones de Docentes
* **Reclutamiento:** Profesores nuevos que se postulan y son contratados de manera temporal específicamente para el CUP.
* **Requisitos del Perfil Docente:** Para poder ser contratado y dar clases, la aplicación debe validar que el docente cumpla con:
  * Ser titulado en el área de tecnología (FICCT). Docentes de áreas comerciales o financieras **no están permitidos** debido al nivel exigido.
  * Contar con grado de **Maestría** en el área.
  * Contar con **Diplomado en Educación Superior**.
* **Límites de Carga Horaria:** 
  * Un docente puede dictar clases como **máximo a 4 grupos**.
  * No necesariamente se tienen que contratar $N$ docentes, se optimiza su asignación cuidando las restricciones.
* **Cruce de Horarios:** El sistema debe impedir por completo que un docente tenga **solapamiento de horarios** (no se deben pisar las clases por ningún motivo).

### 4. Estructura Académica (Materias y Horarios)
* **Materias del CUP (4):**
  1. Computación.
  2. Matemáticas.
  3. Física.
  4. Inglés.
* **Turnos:** Mañana, Tarde y Noche.
* **Días y Modalidad:** 
  * Clases de Lunes a Sábado.
  * De Lunes a Viernes: Clases presenciales.
  * Sábados: Clases virtuales.
  * Todo debe respetar una carga horaria consistente sin choques para los estudiantes.

### 5. Sistema de Evaluación
* **Cantidad de Exámenes:** **3 exámenes** por cada una de las 4 materias.
* **Ponderación/Distribución de Notas:**
  * Primer Examen Parcial: **30%** (30 puntos).
  * Segundo Examen Parcial: **30%** (30 puntos).
  * Examen Final: **40%** (40 puntos).
  * *Nota:* Banco de preguntas altamente modificado y expandido para evitar filtraciones.
* **Criterio de Aprobación del CUP:**
  * La nota mínima de aprobación por materia es **mayor o igual a 60** (60/100).
  * **Regla Crítica:** Para aprobar el CUP, el estudiante debe **aprobar las 4 materias individualmente** con nota $\ge 60$. Si reprueba una sola materia (ej. saca 50 en Física, aunque tenga 100 en las otras tres), queda **Reprobado** del curso preuniversitario.

### 6. Algoritmo de Admisión por Cupos (El Núcleo del Software)
Cuando finaliza el CUP, la facultad debe definir quiénes ingresan oficialmente a cada carrera. Esto se calcula de la siguiente manera:
1. **Quotas por Carrera (Cupos):** Cada carrera tiene un número limitado de cupos establecido por la facultad (por ejemplo: Sistemas = 250 o 300 cupos). La carrera con menor cantidad de postulantes y estudiantes históricos es Redes.
2. **Filtrar Aprobados:** Se aíslan únicamente a los postulantes que hayan obtenido nota $\ge 60$ en las 4 materias.
3. **Distribución por 1ra Opción:**
   * Para cada carrera, se listan los aprobados cuya 1ra Opción sea esa carrera.
   * Se ordenan de **mayor a menor** según su promedio general.
   * Se admiten a los postulantes con los mejores promedios hasta llenar el cupo de la carrera.
4. **Reasignación por 2da Opción (Derivación):**
   * Los aprobados que quedaron fuera del cupo de su 1ra Opción son derivados automáticamente a evaluar su **2da Opción** de carrera.
   * Entran a competir por los cupos restantes disponibles en esa 2da carrera (también ordenados de mayor a menor por promedio).
   * Si no logran ingresar en su segunda opción debido al cupo, quedan fuera del proceso de admisión (se reportan como no admitidos por falta de cupo).

---

## 📈 Reportes, Estadísticas e Indicadores (Business Intelligence)

El sistema debe permitir parametrizar y generar estadísticas gerenciales detalladas:
* **Generales:** Cantidad y porcentaje de postulantes aprobados y reprobados del CUP.
* **Rendimiento Académico por Grupos:** Identificar cuáles grupos obtuvieron la mayor cantidad de alumnos aprobados.
* **Rendimiento por Carreras:** Distribución de los aprobados admitidos por carrera y vacantes sobrantes.
* **Comparativa Multigestión (Histórico de 3 Semestres/Gestiones):**
  * Comparar estadísticas de aprobación entre 3 gestiones diferentes.
  * Analizar tendencias históricas para identificar en qué semestres hay mayor afluencia de postulantes y mejor rendimiento académico.
