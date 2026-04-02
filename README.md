# Sistema de Diagnostico de Comunicacion - UECR

Modulo basado en la estructura de `evaluacion180`, adaptado para levantar el diagnostico de comunicacion institucional.

## Componentes principales

- `database/schema.sql`: tablas `evalcom_*`.
- `database/seed.sql`: procesos, instrumentos, escalas y preguntas base.
- `core/CommunicationEvaluation.php`: logica central del modulo.
- `admin/`: panel administrativo para periodo, participantes, captura y reportes.
- `public/`: acceso del personal interno para el instrumento interno.

## Flujos habilitados

- Sincronizacion de participantes internos desde `empleado`.
- Registro manual de participantes externos y mixtos.
- Captura administrativa de instrumentos internos, externos e institucionales.
- Captura publica del instrumento interno para personal activo.
- Reporte consolidado por periodo e instrumento.

## Base de datos

Por defecto usa la misma base `admision2627` del entorno local, con prefijo `evalcom_`.

Si necesita otro entorno, configure:

- `EVALCOM_DB_HOST`
- `EVALCOM_DB_NAME`
- `EVALCOM_DB_USER`
- `EVALCOM_DB_PASS`
- `EVALCOM_APP_URL`
