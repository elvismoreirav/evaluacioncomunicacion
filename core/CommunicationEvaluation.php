<?php
/**
 * Lógica del diagnóstico de comunicación.
 */

class CommunicationEvaluation
{
    private static bool $schemaEnsured = false;

    public const INSTRUMENT_INTERNAL = 'PERSONAL_INTERNO';
    public const INSTRUMENT_EXTERNAL = 'PUBLICOS_EXTERNOS';
    public const INSTRUMENT_STRUCTURE = 'ESTRUCTURA_ORGANIZACIONAL';
    public const INSTRUMENT_AUDIENCES = 'MAPEO_PUBLICOS';
    public const INSTRUMENT_STRATEGY = 'COMUNICACION_ESTRATEGICA';
    public const INSTRUMENT_TOOLS = 'AUDITORIA_HERRAMIENTAS';
    public const INSTRUMENT_MEDIA = 'ARCHIVOS_MEDIATICOS';
    public const INSTRUMENT_IMAGE = 'IMAGEN_POSICIONAMIENTO';
    public const INSTRUMENT_CONTENT = 'ARCHIVOS_INSTITUCIONALES';

    public function __construct()
    {
        $this->ensureSchemaInstalled();
    }

    public function getAvailablePeriods(): array
    {
        return Database::fetchAll(
            "SELECT p.serial_per,
                    p.nombre_per,
                    p.codigo_per,
                    p.activo_per,
                    p.fecini_per,
                    p.fecfin_per,
                    ep.serial_cfg,
                    ep.estado_cfg
             FROM periodo p
             LEFT JOIN evalcom_periodos ep ON ep.serial_per = p.serial_per
             ORDER BY p.serial_per DESC"
        );
    }

    public function getActiveAcademicPeriod(): ?array
    {
        return Database::fetchOne(
            "SELECT *
             FROM periodo
             WHERE activo_per IN ('SI', 'ACTIVO')
             ORDER BY serial_per DESC
             LIMIT 1"
        );
    }

    public function getConfiguredPeriod(int $serialPer): ?array
    {
        return Database::fetchOne(
            "SELECT p.*,
                    ep.serial_cfg,
                    ep.fecha_inicio_diagnostico,
                    ep.fecha_fin_diagnostico,
                    ep.fecha_inicio_revision,
                    ep.fecha_fin_revision,
                    ep.estado_cfg,
                    ep.observacion_cfg
             FROM periodo p
             LEFT JOIN evalcom_periodos ep ON ep.serial_per = p.serial_per
             WHERE p.serial_per = ?
             LIMIT 1",
            [$serialPer]
        );
    }

    public function ensurePeriodConfigured(int $serialPer): void
    {
        $period = Database::fetchOne(
            "SELECT *
             FROM periodo
             WHERE serial_per = ?
             LIMIT 1",
            [$serialPer]
        );

        if (!$period) {
            throw new InvalidArgumentException('Período no encontrado.');
        }

        $configured = Database::fetchOne(
            "SELECT serial_cfg
             FROM evalcom_periodos
             WHERE serial_per = ?
             LIMIT 1",
            [$serialPer]
        );

        if ($configured) {
            return;
        }

        Database::insert('evalcom_periodos', [
            'serial_per' => $serialPer,
            'fecha_inicio_diagnostico' => !empty($period['fecini_per']) ? $period['fecini_per'] . ' 00:00:00' : null,
            'fecha_fin_diagnostico' => !empty($period['fecfin_per']) ? $period['fecfin_per'] . ' 23:59:59' : null,
            'fecha_inicio_revision' => !empty($period['fecini_per']) ? $period['fecini_per'] . ' 00:00:00' : null,
            'fecha_fin_revision' => !empty($period['fecfin_per']) ? $period['fecfin_per'] . ' 23:59:59' : null,
            'estado_cfg' => 'BORRADOR',
            'observacion_cfg' => null,
        ]);
    }

    public function savePeriodSettings(int $serialPer, array $data): void
    {
        $this->ensurePeriodConfigured($serialPer);

        $allowedStates = ['BORRADOR', 'ACTIVO', 'CERRADO'];
        $state = strtoupper(trim((string) ($data['estado_cfg'] ?? 'BORRADOR')));
        if (!in_array($state, $allowedStates, true)) {
            throw new InvalidArgumentException('Estado de período inválido.');
        }

        Database::update(
            'evalcom_periodos',
            [
                'fecha_inicio_diagnostico' => $this->normalizeDateTimeInput($data['fecha_inicio_diagnostico'] ?? null),
                'fecha_fin_diagnostico' => $this->normalizeDateTimeInput($data['fecha_fin_diagnostico'] ?? null),
                'fecha_inicio_revision' => $this->normalizeDateTimeInput($data['fecha_inicio_revision'] ?? null),
                'fecha_fin_revision' => $this->normalizeDateTimeInput($data['fecha_fin_revision'] ?? null),
                'estado_cfg' => $state,
                'observacion_cfg' => $this->nullableText($data['observacion_cfg'] ?? null),
            ],
            'serial_per = ?',
            [$serialPer]
        );
    }

    public function getDashboardStats(int $serialPer): array
    {
        $participants = Database::fetchOne(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN tipo_participante = 'INTERNO' THEN 1 ELSE 0 END) AS internos,
                    SUM(CASE WHEN tipo_participante = 'EXTERNO' THEN 1 ELSE 0 END) AS externos,
                    SUM(CASE WHEN tipo_participante = 'MIXTO' THEN 1 ELSE 0 END) AS mixtos
             FROM evalcom_participantes
             WHERE activo_par = 'SI'"
        ) ?: [];

        $evaluations = Database::fetchOne(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN estado_eva = 'BORRADOR' THEN 1 ELSE 0 END) AS borrador,
                    SUM(CASE WHEN estado_eva = 'ENVIADA' THEN 1 ELSE 0 END) AS enviada,
                    SUM(CASE WHEN estado_eva = 'REVISADA' THEN 1 ELSE 0 END) AS revisada,
                    SUM(CASE WHEN tipo_registro = 'INSTITUCIONAL' THEN 1 ELSE 0 END) AS institucional
             FROM evalcom_evaluaciones
             WHERE serial_per = ?",
            [$serialPer]
        ) ?: [];

        $instrumentCount = (int) Database::fetchColumn(
            "SELECT COUNT(*)
             FROM evalcom_instrumentos
             WHERE activo_ins = 'SI'"
        );

        return [
            'participantes' => (int) ($participants['total'] ?? 0),
            'internos' => (int) ($participants['internos'] ?? 0),
            'externos' => (int) ($participants['externos'] ?? 0),
            'mixtos' => (int) ($participants['mixtos'] ?? 0),
            'instrumentos' => $instrumentCount,
            'evaluaciones' => (int) ($evaluations['total'] ?? 0),
            'borrador' => (int) ($evaluations['borrador'] ?? 0),
            'enviadas' => (int) ($evaluations['enviada'] ?? 0),
            'revisadas' => (int) ($evaluations['revisada'] ?? 0),
            'institucionales' => (int) ($evaluations['institucional'] ?? 0),
        ];
    }

    public function getRecentSubmissions(int $serialPer, int $limit = 8): array
    {
        $limit = max(1, $limit);

        return Database::fetchAll(
            "SELECT e.serial_eva,
                    e.serial_ins,
                    e.serial_par,
                    e.tipo_registro,
                    e.estado_eva,
                    e.puntaje_final,
                    e.fecha_envio,
                    i.codigo_ins,
                    i.nombre_ins,
                    i.audiencia_ins,
                    p.tipo_participante,
                    p.nombres_par,
                    p.apellidos_par
             FROM evalcom_evaluaciones e
             INNER JOIN evalcom_instrumentos i ON i.serial_ins = e.serial_ins
             LEFT JOIN evalcom_participantes p ON p.serial_par = e.serial_par
             WHERE e.serial_per = ?
             ORDER BY COALESCE(e.fecha_envio, e.fecha_inicio) DESC, e.serial_eva DESC
             LIMIT {$limit}",
            [$serialPer]
        );
    }

    public function getInstrumentCatalog(?string $audience = null): array
    {
        $sql = "SELECT i.*,
                       (
                           SELECT COUNT(*)
                           FROM evalcom_secciones s
                           WHERE s.serial_ins = i.serial_ins
                             AND s.activo_sec = 'SI'
                       ) AS total_secciones,
                       (
                           SELECT COUNT(*)
                           FROM evalcom_preguntas q
                           INNER JOIN evalcom_secciones s2 ON s2.serial_sec = q.serial_sec
                           WHERE s2.serial_ins = i.serial_ins
                             AND s2.activo_sec = 'SI'
                             AND q.activo_pre = 'SI'
                       ) AS total_preguntas
                FROM evalcom_instrumentos i
                WHERE i.activo_ins = 'SI'";
        $params = [];

        if ($audience !== null && $audience !== '') {
            $sql .= " AND i.audiencia_ins = ?";
            $params[] = $audience;
        }

        $sql .= " ORDER BY i.orden_ins ASC, i.serial_ins ASC";

        $catalog = Database::fetchAll($sql, $params);

        foreach ($catalog as &$instrument) {
            $instrument['procesos'] = $this->getInstrumentProcesses((int) $instrument['serial_ins']);
        }

        unset($instrument);

        return $catalog;
    }

    public function getInstrumentById(int $serialIns): ?array
    {
        $instrument = Database::fetchOne(
            "SELECT *
             FROM evalcom_instrumentos
             WHERE serial_ins = ?
             LIMIT 1",
            [$serialIns]
        );

        if (!$instrument) {
            return null;
        }

        $instrument['procesos'] = $this->getInstrumentProcesses($serialIns);
        return $instrument;
    }

    public function getInstrumentByCode(string $code): ?array
    {
        $instrument = Database::fetchOne(
            "SELECT *
             FROM evalcom_instrumentos
             WHERE codigo_ins = ?
             LIMIT 1",
            [trim($code)]
        );

        if (!$instrument) {
            return null;
        }

        $instrument['procesos'] = $this->getInstrumentProcesses((int) $instrument['serial_ins']);
        return $instrument;
    }

    public function getParticipantsForAdmin(int $serialPer, array $filters = []): array
    {
        $sql = "SELECT p.*,
                       (
                           SELECT COUNT(*)
                           FROM evalcom_evaluaciones e
                           WHERE e.serial_par = p.serial_par
                             AND e.serial_per = ?
                       ) AS total_evaluaciones,
                       (
                           SELECT MAX(e2.fecha_envio)
                           FROM evalcom_evaluaciones e2
                           WHERE e2.serial_par = p.serial_par
                             AND e2.serial_per = ?
                       ) AS ultima_entrega
                FROM evalcom_participantes p
                WHERE 1 = 1";
        $params = [$serialPer, $serialPer];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $sql .= " AND (
                p.nombres_par LIKE ?
                OR p.apellidos_par LIKE ?
                OR p.email_par LIKE ?
                OR p.organizacion_par LIKE ?
                OR p.publico_par LIKE ?
            )";
            $needle = '%' . $search . '%';
            array_push($params, $needle, $needle, $needle, $needle, $needle);
        }

        $type = trim((string) ($filters['tipo'] ?? ''));
        if ($type !== '') {
            $sql .= " AND p.tipo_participante = ?";
            $params[] = strtoupper($type);
        }

        $status = trim((string) ($filters['activo'] ?? ''));
        if ($status !== '') {
            $sql .= " AND p.activo_par = ?";
            $params[] = strtoupper($status);
        }

        $sql .= " ORDER BY p.tipo_participante ASC, p.apellidos_par ASC, p.nombres_par ASC";

        return Database::fetchAll($sql, $params);
    }

    public function getParticipantsForInstrument(int $serialIns): array
    {
        $instrument = $this->getInstrumentById($serialIns);
        if (!$instrument) {
            return [];
        }

        if (($instrument['requiere_participante'] ?? 'SI') !== 'SI') {
            return [];
        }

        $sql = "SELECT *
                FROM evalcom_participantes
                WHERE activo_par = 'SI'";
        $params = [];

        switch ($instrument['audiencia_ins']) {
            case 'INTERNA':
                $sql .= " AND tipo_participante = 'INTERNO'";
                break;
            case 'EXTERNA':
                $sql .= " AND tipo_participante = 'EXTERNO'";
                break;
            case 'MIXTA':
                $sql .= " AND tipo_participante IN ('INTERNO', 'EXTERNO', 'MIXTO')";
                break;
        }

        $sql .= " ORDER BY apellidos_par ASC, nombres_par ASC";

        return Database::fetchAll($sql, $params);
    }

    public function getParticipantById(int $serialPar): ?array
    {
        return Database::fetchOne(
            "SELECT *
             FROM evalcom_participantes
             WHERE serial_par = ?
             LIMIT 1",
            [$serialPar]
        );
    }

    public function getParticipantByEmployeeId(int $employeeId): ?array
    {
        return Database::fetchOne(
            "SELECT *
             FROM evalcom_participantes
             WHERE serial_epl = ?
             LIMIT 1",
            [$employeeId]
        );
    }

    public function syncActiveEmployees(int $serialPer): array
    {
        $this->ensurePeriodConfigured($serialPer);

        $employees = $this->getActiveEmployees();
        $created = 0;
        $updated = 0;

        foreach ($employees as $employee) {
            $employeeId = (int) $employee['SERIAL_EPL'];
            $existing = $this->getParticipantByEmployeeId($employeeId);
            $payload = [
                'serial_epl' => $employeeId,
                'tipo_participante' => 'INTERNO',
                'nombres_par' => trim((string) ($employee['NOMBRE_EPL'] ?? '')),
                'apellidos_par' => trim((string) ($employee['APELLIDO_EPL'] ?? '')),
                'cargo_par' => null,
                'organizacion_par' => INSTITUTION_NAME,
                'publico_par' => 'Comunidad educativa interna',
                'email_par' => $this->firstNonEmpty([
                    $employee['EMAIL_EPL'] ?? null,
                    $employee['mailPersonal_epl'] ?? null,
                    $employee['emailpersonal_epl'] ?? null,
                ]),
                'telefono_par' => trim((string) ($employee['CELULAR_EPL'] ?? '')),
                'activo_par' => 'SI',
            ];

            if ($existing) {
                Database::update(
                    'evalcom_participantes',
                    $payload,
                    'serial_par = ?',
                    [(int) $existing['serial_par']]
                );
                $updated++;
                continue;
            }

            Database::insert('evalcom_participantes', $payload);
            $created++;
        }

        return [
            'total_activos' => count($employees),
            'creados' => $created,
            'actualizados' => $updated,
        ];
    }

    public function saveParticipant(array $data): int
    {
        $participantId = (int) ($data['serial_par'] ?? 0);
        $participantType = strtoupper(trim((string) ($data['tipo_participante'] ?? 'INTERNO')));
        $allowedTypes = ['INTERNO', 'EXTERNO', 'MIXTO'];

        if (!in_array($participantType, $allowedTypes, true)) {
            throw new InvalidArgumentException('Tipo de participante inválido.');
        }

        $serialEpl = trim((string) ($data['serial_epl'] ?? ''));
        $serialEpl = $serialEpl === '' ? null : (int) $serialEpl;
        $payload = [
            'serial_epl' => $serialEpl,
            'tipo_participante' => $participantType,
            'nombres_par' => trim((string) ($data['nombres_par'] ?? '')),
            'apellidos_par' => $this->nullableText($data['apellidos_par'] ?? null),
            'genero_par' => $this->normalizeEnum($data['genero_par'] ?? null, ['MUJER', 'VARON', 'NO_BINARIO', 'OTRO', 'PREFIERE_NO_DECIR']),
            'cargo_par' => $this->nullableText($data['cargo_par'] ?? null),
            'organizacion_par' => $this->nullableText($data['organizacion_par'] ?? null),
            'publico_par' => $this->nullableText($data['publico_par'] ?? null),
            'email_par' => $this->nullableText($data['email_par'] ?? null),
            'telefono_par' => $this->nullableText($data['telefono_par'] ?? null),
            'activo_par' => strtoupper(trim((string) ($data['activo_par'] ?? 'SI'))) === 'NO' ? 'NO' : 'SI',
        ];

        if ($payload['nombres_par'] === '') {
            throw new InvalidArgumentException('El nombre del participante es obligatorio.');
        }

        if ($participantId > 0) {
            Database::update('evalcom_participantes', $payload, 'serial_par = ?', [$participantId]);
            return $participantId;
        }

        return Database::insert('evalcom_participantes', $payload);
    }

    public function getEmployeeDashboard(int $employeeId, int $serialPer): array
    {
        $this->ensurePeriodConfigured($serialPer);
        $this->syncActiveEmployees($serialPer);

        $employee = $this->getEmployeeById($employeeId);
        if (!$employee) {
            throw new RuntimeException('No fue posible identificar al empleado.');
        }

        $participant = $this->getParticipantByEmployeeId($employeeId);
        if (!$participant) {
            throw new RuntimeException('No existe un participante interno asociado.');
        }

        $period = $this->getConfiguredPeriod($serialPer);
        $instrument = $this->getInstrumentByCode(self::INSTRUMENT_INTERNAL);
        if (!$instrument) {
            throw new RuntimeException('No existe el instrumento interno configurado.');
        }

        $evaluation = $this->findExistingEvaluation($serialPer, (int) $instrument['serial_ins'], (int) $participant['serial_par'], 'INDIVIDUAL');
        $questions = $this->countInstrumentQuestions((int) $instrument['serial_ins']);
        $answered = $evaluation ? $this->countEvaluationResponses((int) $evaluation['serial_eva']) : 0;

        return [
            'periodo' => $period,
            'empleado' => $employee,
            'participante' => $participant,
            'instrumento' => $instrument,
            'evaluacion' => $evaluation,
            'total_preguntas' => $questions,
            'respondidas' => $answered,
            'ventana_abierta' => $this->isDiagnosticWindowOpen($period),
        ];
    }

    public function getEvaluationFormContext(int $serialPer, int $serialIns, ?int $serialPar = null): array
    {
        $this->ensurePeriodConfigured($serialPer);

        $period = $this->getConfiguredPeriod($serialPer);
        $instrument = $this->getInstrumentById($serialIns);
        if (!$instrument) {
            throw new RuntimeException('Instrumento no encontrado.');
        }

        $participant = $serialPar ? $this->getParticipantById($serialPar) : null;
        $evaluation = $this->findExistingEvaluation(
            $serialPer,
            $serialIns,
            $serialPar,
            $serialPar ? 'INDIVIDUAL' : 'INSTITUCIONAL'
        );

        $sections = $this->getSectionsForInstrument($serialIns);
        $responses = $evaluation ? $this->getEvaluationResponses((int) $evaluation['serial_eva']) : [];
        $extraRows = $evaluation
            ? $this->getExtraRowsByInstrumentCode((string) $instrument['codigo_ins'], (int) $evaluation['serial_eva'])
            : $this->blankExtraRows((string) $instrument['codigo_ins']);

        foreach ($sections as &$section) {
            foreach ($section['preguntas'] as &$question) {
                $response = $responses[(int) $question['serial_pre']] ?? null;
                $question['respuesta_actual'] = $response;
            }
            unset($question);
        }
        unset($section);

        return [
            'periodo' => $period,
            'instrumento' => $instrument,
            'participante' => $participant,
            'evaluacion' => $evaluation,
            'secciones' => $sections,
            'filas_extra' => $extraRows,
            'ventana_abierta' => $this->isDiagnosticWindowOpen($period),
        ];
    }

    public function saveEvaluationSubmission(
        int $serialPer,
        int $serialIns,
        ?int $serialPar,
        array $responses,
        array $extraRows,
        ?string $observacionGeneral = null,
        string $state = 'ENVIADA'
    ): int {
        $instrument = $this->getInstrumentById($serialIns);
        if (!$instrument) {
            throw new RuntimeException('Instrumento no encontrado.');
        }

        if (($instrument['requiere_participante'] ?? 'SI') === 'SI' && !$serialPar) {
            throw new InvalidArgumentException('Debe seleccionar un participante para este instrumento.');
        }

        $type = $serialPar ? 'INDIVIDUAL' : 'INSTITUCIONAL';
        $allowedStates = ['BORRADOR', 'ENVIADA', 'REVISADA', 'CERRADA'];
        $state = strtoupper(trim($state));
        if (!in_array($state, $allowedStates, true)) {
            throw new InvalidArgumentException('Estado de evaluación inválido.');
        }

        $context = $this->getEvaluationFormContext($serialPer, $serialIns, $serialPar);
        $sections = $context['secciones'];

        Database::beginTransaction();

        try {
            $evaluation = $this->findExistingEvaluation($serialPer, $serialIns, $serialPar, $type);
            if ($evaluation) {
                $evaluationId = (int) $evaluation['serial_eva'];
                Database::update(
                    'evalcom_evaluaciones',
                    [
                        'estado_eva' => $state,
                        'observacion_general' => $this->nullableText($observacionGeneral),
                        'fecha_envio' => in_array($state, ['ENVIADA', 'REVISADA', 'CERRADA'], true) ? date('Y-m-d H:i:s') : null,
                    ],
                    'serial_eva = ?',
                    [$evaluationId]
                );
            } else {
                $evaluationId = Database::insert('evalcom_evaluaciones', [
                    'serial_per' => $serialPer,
                    'serial_ins' => $serialIns,
                    'serial_par' => $serialPar,
                    'tipo_registro' => $type,
                    'estado_eva' => $state,
                    'titulo_referencia' => $this->buildReferenceTitle($instrument, $serialPar ? $context['participante'] : null),
                    'observacion_general' => $this->nullableText($observacionGeneral),
                    'fecha_envio' => in_array($state, ['ENVIADA', 'REVISADA', 'CERRADA'], true) ? date('Y-m-d H:i:s') : null,
                ]);
            }

            foreach ($sections as $section) {
                foreach ($section['preguntas'] as $question) {
                    $this->persistQuestionResponse($evaluationId, $question, $responses[(int) $question['serial_pre']] ?? null);
                }
            }

            $this->replaceExtraRows((string) $instrument['codigo_ins'], $evaluationId, $extraRows);

            $score = $this->calculateEvaluationScore($evaluationId);
            Database::update(
                'evalcom_evaluaciones',
                ['puntaje_final' => $score],
                'serial_eva = ?',
                [$evaluationId]
            );

            Database::commit();
            return $evaluationId;
        } catch (Throwable $throwable) {
            Database::rollBack();
            throw $throwable;
        }
    }

    public function getResultsForAdmin(int $serialPer, array $filters = []): array
    {
        $sql = "SELECT e.serial_eva,
                       e.estado_eva,
                       e.tipo_registro,
                       e.puntaje_final,
                       e.fecha_inicio,
                       e.fecha_envio,
                       e.fecha_revision,
                       e.titulo_referencia,
                       i.codigo_ins,
                       i.nombre_ins,
                       i.audiencia_ins,
                       p.serial_par,
                       p.tipo_participante,
                       p.nombres_par,
                       p.apellidos_par,
                       p.organizacion_par,
                       p.publico_par
                FROM evalcom_evaluaciones e
                INNER JOIN evalcom_instrumentos i ON i.serial_ins = e.serial_ins
                LEFT JOIN evalcom_participantes p ON p.serial_par = e.serial_par
                WHERE e.serial_per = ?";
        $params = [$serialPer];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $needle = '%' . $search . '%';
            $sql .= " AND (
                i.nombre_ins LIKE ?
                OR p.nombres_par LIKE ?
                OR p.apellidos_par LIKE ?
                OR p.organizacion_par LIKE ?
                OR e.titulo_referencia LIKE ?
            )";
            array_push($params, $needle, $needle, $needle, $needle, $needle);
        }

        $instrumentId = (int) ($filters['instrumento'] ?? 0);
        if ($instrumentId > 0) {
            $sql .= " AND e.serial_ins = ?";
            $params[] = $instrumentId;
        }

        $status = trim((string) ($filters['estado'] ?? ''));
        if ($status !== '') {
            $sql .= " AND e.estado_eva = ?";
            $params[] = strtoupper($status);
        }

        $audience = trim((string) ($filters['audiencia'] ?? ''));
        if ($audience !== '') {
            $sql .= " AND i.audiencia_ins = ?";
            $params[] = strtoupper($audience);
        }

        $sql .= " ORDER BY COALESCE(e.fecha_envio, e.fecha_inicio) DESC, e.serial_eva DESC";

        return Database::fetchAll($sql, $params);
    }

    public function getAdminUsers(): array
    {
        return Database::fetchAll(
            "SELECT *
             FROM evalcom_admins
             ORDER BY serial_admin DESC"
        );
    }

    public function countInstrumentQuestions(int $serialIns): int
    {
        return (int) Database::fetchColumn(
            "SELECT COUNT(*)
             FROM evalcom_preguntas q
             INNER JOIN evalcom_secciones s ON s.serial_sec = q.serial_sec
             WHERE s.serial_ins = ?
               AND q.activo_pre = 'SI'
               AND s.activo_sec = 'SI'",
            [$serialIns]
        );
    }

    public function countEvaluationResponses(int $serialEva): int
    {
        return (int) Database::fetchColumn(
            "SELECT COUNT(*)
             FROM evalcom_respuestas
             WHERE serial_eva = ?",
            [$serialEva]
        );
    }

    public function participantDisplayName(?array $participant): string
    {
        if (!$participant) {
            return 'Registro institucional';
        }

        $fullName = trim(
            trim((string) ($participant['apellidos_par'] ?? '')) . ' ' .
            trim((string) ($participant['nombres_par'] ?? ''))
        );

        if ($fullName !== '') {
            return $fullName;
        }

        return trim((string) ($participant['organizacion_par'] ?? 'Participante'));
    }

    public function audienceLabel(string $audience): string
    {
        return match (strtoupper($audience)) {
            'INTERNA' => 'Interna',
            'EXTERNA' => 'Externa',
            'MIXTA' => 'Mixta',
            'INSTITUCIONAL' => 'Institucional',
            default => $audience,
        };
    }

    public function participantTypeLabel(string $type): string
    {
        return match (strtoupper($type)) {
            'INTERNO' => 'Interno',
            'EXTERNO' => 'Externo',
            'MIXTO' => 'Mixto',
            default => $type,
        };
    }

    private function ensureSchemaInstalled(): void
    {
        if (self::$schemaEnsured) {
            return;
        }

        $schemaReady = Database::tableExists('evalcom_admins')
            && Database::tableExists('evalcom_instrumentos')
            && Database::tableExists('evalcom_seccion_procesos');

        if (!$schemaReady) {
            $this->runSqlFile(__DIR__ . '/../database/schema.sql');
            $this->runSqlFile(__DIR__ . '/../database/seed.sql');
        } else {
            $this->runSqlFile(__DIR__ . '/../database/seed.sql');
        }

        self::$schemaEnsured = true;
    }

    private function runSqlFile(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException('Archivo SQL no encontrado: ' . basename($path));
        }

        $content = (string) file_get_contents($path);
        $buffer = '';
        $lines = preg_split('/\R/', $content) ?: [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $buffer .= $line . "\n";

            if (str_ends_with(rtrim($line), ';')) {
                Database::getInstance()->exec($buffer);
                $buffer = '';
            }
        }

        if (trim($buffer) !== '') {
            Database::getInstance()->exec($buffer);
        }
    }

    private function getActiveEmployees(): array
    {
        return Database::fetchAll(
            "SELECT e.SERIAL_EPL,
                    e.DOCUMENTOIDENTIDAD_EPL,
                    e.NOMBRE_EPL,
                    e.APELLIDO_EPL,
                    e.EMAIL_EPL,
                    e.mailPersonal_epl,
                    e.emailpersonal_epl,
                    e.CELULAR_EPL,
                    d.DESCRIPCION_DEP
             FROM empleado e
             LEFT JOIN departamentos d ON d.SERIAL_DEP = e.serial_depc
             WHERE e.ESTADOEMPLEADO_EPL = 'ACTIVO'
               AND TRIM(COALESCE(e.DOCUMENTOIDENTIDAD_EPL, '')) <> ''
             ORDER BY e.APELLIDO_EPL, e.NOMBRE_EPL"
        );
    }

    private function getEmployeeById(int $employeeId): ?array
    {
        return Database::fetchOne(
            "SELECT e.*,
                    d.DESCRIPCION_DEP
             FROM empleado e
             LEFT JOIN departamentos d ON d.SERIAL_DEP = e.serial_depc
             WHERE e.SERIAL_EPL = ?
             LIMIT 1",
            [$employeeId]
        );
    }

    private function getSectionsForInstrument(int $serialIns): array
    {
        $sections = Database::fetchAll(
            "SELECT s.*,
                    es.nombre_esc AS nombre_escala_sec
             FROM evalcom_secciones s
             LEFT JOIN evalcom_escalas es ON es.serial_esc = s.serial_esc
             WHERE s.serial_ins = ?
               AND s.activo_sec = 'SI'
             ORDER BY s.orden_sec ASC, s.serial_sec ASC",
            [$serialIns]
        );

        $questions = Database::fetchAll(
            "SELECT q.*,
                    sec.serial_esc AS serial_escala_seccion
             FROM evalcom_preguntas q
             INNER JOIN evalcom_secciones sec ON sec.serial_sec = q.serial_sec
             WHERE sec.serial_ins = ?
               AND q.activo_pre = 'SI'
               AND sec.activo_sec = 'SI'
             ORDER BY sec.orden_sec ASC, q.orden_pre ASC, q.serial_pre ASC",
            [$serialIns]
        );

        $questionsBySection = [];
        foreach ($questions as $question) {
            $scaleId = (int) ($question['serial_esc'] ?: $question['serial_escala_seccion']);
            $question['escala'] = $scaleId > 0 ? $this->getScaleOptionsByScaleId($scaleId) : [];
            $question['opciones'] = $this->getQuestionOptions((int) $question['serial_pre']);
            $questionsBySection[(int) $question['serial_sec']][] = $question;
        }

        foreach ($sections as &$section) {
            $section['procesos'] = $this->getSectionProcesses((int) $section['serial_sec']);
            $section['preguntas'] = $questionsBySection[(int) $section['serial_sec']] ?? [];
        }

        unset($section);

        return $sections;
    }

    private function getScaleOptionsByScaleId(int $serialEsc): array
    {
        return Database::fetchAll(
            "SELECT *
             FROM evalcom_escala_opciones
             WHERE serial_esc = ?
               AND activo_opc = 'SI'
             ORDER BY orden_opc ASC, serial_eco ASC",
            [$serialEsc]
        );
    }

    private function getQuestionOptions(int $serialPre): array
    {
        return Database::fetchAll(
            "SELECT *
             FROM evalcom_pregunta_opciones
             WHERE serial_pre = ?
               AND activo_opc = 'SI'
             ORDER BY orden_opc ASC, serial_pop ASC",
            [$serialPre]
        );
    }

    private function getInstrumentProcesses(int $serialIns): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT p.codigo_proceso,
                             p.nombre_proceso,
                             p.orden_proceso,
                             sp.orden_relacion
             FROM evalcom_secciones s
             INNER JOIN evalcom_seccion_procesos sp ON sp.serial_sec = s.serial_sec
             INNER JOIN evalcom_procesos p ON p.codigo_proceso = sp.codigo_proceso
             WHERE s.serial_ins = ?
             ORDER BY sp.orden_relacion ASC, p.orden_proceso ASC",
            [$serialIns]
        );
    }

    private function getSectionProcesses(int $serialSec): array
    {
        return Database::fetchAll(
            "SELECT p.codigo_proceso,
                    p.nombre_proceso,
                    p.orden_proceso
             FROM evalcom_seccion_procesos sp
             INNER JOIN evalcom_procesos p ON p.codigo_proceso = sp.codigo_proceso
             WHERE sp.serial_sec = ?
             ORDER BY sp.orden_relacion ASC, p.orden_proceso ASC",
            [$serialSec]
        );
    }

    private function findExistingEvaluation(int $serialPer, int $serialIns, ?int $serialPar, string $type): ?array
    {
        if ($serialPar) {
            return Database::fetchOne(
                "SELECT *
                 FROM evalcom_evaluaciones
                 WHERE serial_per = ?
                   AND serial_ins = ?
                   AND serial_par = ?
                   AND tipo_registro = ?
                 ORDER BY serial_eva DESC
                 LIMIT 1",
                [$serialPer, $serialIns, $serialPar, $type]
            );
        }

        return Database::fetchOne(
            "SELECT *
             FROM evalcom_evaluaciones
             WHERE serial_per = ?
               AND serial_ins = ?
               AND serial_par IS NULL
               AND tipo_registro = ?
             ORDER BY serial_eva DESC
             LIMIT 1",
            [$serialPer, $serialIns, $type]
        );
    }

    private function getEvaluationResponses(int $serialEva): array
    {
        $rows = Database::fetchAll(
            "SELECT r.*,
                    eo.etiqueta_opc AS escala_etiqueta,
                    po.etiqueta_opc AS opcion_etiqueta
             FROM evalcom_respuestas r
             LEFT JOIN evalcom_escala_opciones eo ON eo.serial_eco = r.serial_eco
             LEFT JOIN evalcom_pregunta_opciones po ON po.serial_pop = r.serial_pop
             WHERE r.serial_eva = ?",
            [$serialEva]
        );

        $multiRows = Database::fetchAll(
            "SELECT ro.serial_res,
                    ro.serial_pop
             FROM evalcom_respuesta_opciones ro
             INNER JOIN evalcom_respuestas r ON r.serial_res = ro.serial_res
             WHERE r.serial_eva = ?",
            [$serialEva]
        );

        $multiByResponse = [];
        foreach ($multiRows as $row) {
            $multiByResponse[(int) $row['serial_res']][] = (int) $row['serial_pop'];
        }

        $responses = [];
        foreach ($rows as $row) {
            $row['multi'] = $multiByResponse[(int) $row['serial_res']] ?? [];
            $responses[(int) $row['serial_pre']] = $row;
        }

        return $responses;
    }

    private function getExtraRowsByInstrumentCode(string $instrumentCode, int $serialEva): array
    {
        return match ($instrumentCode) {
            self::INSTRUMENT_AUDIENCES => [
                'mapeo' => Database::fetchAll(
                    "SELECT *
                     FROM evalcom_publicos_mapeo
                     WHERE serial_eva = ?
                     ORDER BY orden_publico ASC, serial_pub ASC",
                    [$serialEva]
                ),
            ],
            self::INSTRUMENT_TOOLS => [
                'herramientas' => Database::fetchAll(
                    "SELECT *
                     FROM evalcom_herramientas_auditoria
                     WHERE serial_eva = ?
                     ORDER BY orden_herramienta ASC, serial_her ASC",
                    [$serialEva]
                ),
            ],
            self::INSTRUMENT_MEDIA => [
                'medios' => Database::fetchAll(
                    "SELECT *
                     FROM evalcom_archivos_mediaticos
                     WHERE serial_eva = ?
                     ORDER BY orden_archivo ASC, serial_arm ASC",
                    [$serialEva]
                ),
            ],
            self::INSTRUMENT_CONTENT => [
                'institucionales' => Database::fetchAll(
                    "SELECT *
                     FROM evalcom_archivos_institucionales
                     WHERE serial_eva = ?
                     ORDER BY orden_recurso ASC, serial_ari ASC",
                    [$serialEva]
                ),
            ],
            default => [],
        };
    }

    private function blankExtraRows(string $instrumentCode): array
    {
        return match ($instrumentCode) {
            self::INSTRUMENT_AUDIENCES => ['mapeo' => [[], [], []]],
            self::INSTRUMENT_TOOLS => ['herramientas' => [[], [], []]],
            self::INSTRUMENT_MEDIA => ['medios' => [[], [], []]],
            self::INSTRUMENT_CONTENT => ['institucionales' => [[], [], []]],
            default => [],
        };
    }

    private function replaceExtraRows(string $instrumentCode, int $serialEva, array $extraRows): void
    {
        switch ($instrumentCode) {
            case self::INSTRUMENT_AUDIENCES:
                Database::delete('evalcom_publicos_mapeo', 'serial_eva = ?', [$serialEva]);
                foreach (($extraRows['mapeo'] ?? []) as $index => $row) {
                    if ($this->rowIsEmpty($row, ['nombre_publico'])) {
                        continue;
                    }

                    Database::insert('evalcom_publicos_mapeo', [
                        'serial_eva' => $serialEva,
                        'tipo_publico' => $this->normalizeEnum($row['tipo_publico'] ?? 'EXTERNO', ['INTERNO', 'EXTERNO', 'MIXTO']) ?? 'EXTERNO',
                        'nombre_publico' => trim((string) ($row['nombre_publico'] ?? '')),
                        'categoria_grupo' => $this->nullableText($row['categoria_grupo'] ?? null),
                        'situacion_publico' => $this->normalizeEnum($row['situacion_publico'] ?? null, ['ALIADO', 'INDECISO', 'A_CONVENCER', 'OTRO']),
                        'fuente_informacion' => $this->nullableText($row['fuente_informacion'] ?? null),
                        'influencia_directa' => strtoupper(trim((string) ($row['influencia_directa'] ?? 'NO'))) === 'SI' ? 'SI' : 'NO',
                        'estrategia_influencia' => $this->nullableText($row['estrategia_influencia'] ?? null),
                        'necesidades_comunicacion' => $this->nullableText($row['necesidades_comunicacion'] ?? null),
                        'intereses_valores_creencias' => $this->nullableText($row['intereses_valores_creencias'] ?? null),
                        'medios_preferenciales' => $this->nullableText($row['medios_preferenciales'] ?? null),
                        'cambio_buscado' => $this->nullableText($row['cambio_buscado'] ?? null),
                        'tono_lenguaje' => $this->nullableText($row['tono_lenguaje'] ?? null),
                        'respuesta_necesidades' => $this->nullableText($row['respuesta_necesidades'] ?? null),
                        'mapa_empatia' => $this->nullableText($row['mapa_empatia'] ?? null),
                        'orden_publico' => $index + 1,
                    ]);
                }
                break;

            case self::INSTRUMENT_TOOLS:
                Database::delete('evalcom_herramientas_auditoria', 'serial_eva = ?', [$serialEva]);
                foreach (($extraRows['herramientas'] ?? []) as $index => $row) {
                    if ($this->rowIsEmpty($row, ['plataforma_herramienta'])) {
                        continue;
                    }

                    Database::insert('evalcom_herramientas_auditoria', [
                        'serial_eva' => $serialEva,
                        'plataforma_herramienta' => trim((string) ($row['plataforma_herramienta'] ?? '')),
                        'proposito_herramienta' => $this->nullableText($row['proposito_herramienta'] ?? null),
                        'frecuencia_uso' => $this->nullableText($row['frecuencia_uso'] ?? null),
                        'personas_alcanzadas' => $this->nullableText($row['personas_alcanzadas'] ?? null),
                        'area_responsable' => $this->nullableText($row['area_responsable'] ?? null),
                        'observaciones_herramienta' => $this->nullableText($row['observaciones_herramienta'] ?? null),
                        'recomendaciones_herramienta' => $this->nullableText($row['recomendaciones_herramienta'] ?? null),
                        'orden_herramienta' => $index + 1,
                    ]);
                }
                break;

            case self::INSTRUMENT_MEDIA:
                Database::delete('evalcom_archivos_mediaticos', 'serial_eva = ?', [$serialEva]);
                foreach (($extraRows['medios'] ?? []) as $index => $row) {
                    if ($this->rowIsEmpty($row, ['titulo_referencia'])) {
                        continue;
                    }

                    Database::insert('evalcom_archivos_mediaticos', [
                        'serial_eva' => $serialEva,
                        'fecha_referencia' => $this->normalizeDateInput($row['fecha_referencia'] ?? null),
                        'tipo_medio' => $this->nullableText($row['tipo_medio'] ?? null),
                        'nombre_medio' => $this->nullableText($row['nombre_medio'] ?? null),
                        'titulo_referencia' => trim((string) ($row['titulo_referencia'] ?? '')),
                        'url_referencia' => $this->nullableText($row['url_referencia'] ?? null),
                        'representacion_institucion' => $this->nullableText($row['representacion_institucion'] ?? null),
                        'ejes_tematicos' => $this->nullableText($row['ejes_tematicos'] ?? null),
                        'evaluacion_historica' => $this->nullableText($row['evaluacion_historica'] ?? null),
                        'mejora_relaciones_publicas' => $this->nullableText($row['mejora_relaciones_publicas'] ?? null),
                        'observacion_vocerias' => $this->nullableText($row['observacion_vocerias'] ?? null),
                        'orden_archivo' => $index + 1,
                    ]);
                }
                break;

            case self::INSTRUMENT_CONTENT:
                Database::delete('evalcom_archivos_institucionales', 'serial_eva = ?', [$serialEva]);
                foreach (($extraRows['institucionales'] ?? []) as $index => $row) {
                    if ($this->rowIsEmpty($row, ['tipo_recurso', 'titulo_recurso'])) {
                        continue;
                    }

                    Database::insert('evalcom_archivos_institucionales', [
                        'serial_eva' => $serialEva,
                        'tipo_recurso' => trim((string) ($row['tipo_recurso'] ?? '')),
                        'titulo_recurso' => trim((string) ($row['titulo_recurso'] ?? '')),
                        'fecha_recurso' => $this->normalizeDateInput($row['fecha_recurso'] ?? null),
                        'publico_objetivo' => $this->nullableText($row['publico_objetivo'] ?? null),
                        'descripcion_recurso' => $this->nullableText($row['descripcion_recurso'] ?? null),
                        'url_recurso' => $this->nullableText($row['url_recurso'] ?? null),
                        'observaciones_recurso' => $this->nullableText($row['observaciones_recurso'] ?? null),
                        'orden_recurso' => $index + 1,
                    ]);
                }
                break;
        }
    }

    private function persistQuestionResponse(int $evaluationId, array $question, $responseData): void
    {
        $questionId = (int) $question['serial_pre'];
        $existing = Database::fetchOne(
            "SELECT *
             FROM evalcom_respuestas
             WHERE serial_eva = ?
               AND serial_pre = ?
             LIMIT 1",
            [$evaluationId, $questionId]
        );

        $payload = $this->buildResponsePayload($question, $responseData);
        if ($payload === null) {
            if ($existing) {
                Database::delete('evalcom_respuestas', 'serial_res = ?', [(int) $existing['serial_res']]);
            }
            return;
        }

        $payload['serial_eva'] = $evaluationId;
        $payload['serial_pre'] = $questionId;

        if ($existing) {
            $responseId = (int) $existing['serial_res'];
            $multi = $payload['_multi'];
            unset($payload['_multi']);
            unset($payload['serial_eva'], $payload['serial_pre']);
            Database::update('evalcom_respuestas', $payload, 'serial_res = ?', [$responseId]);
        } else {
            $multi = $payload['_multi'];
            unset($payload['_multi']);
            $responseId = Database::insert('evalcom_respuestas', $payload);
        }

        Database::delete('evalcom_respuesta_opciones', 'serial_res = ?', [$responseId]);

        if (!empty($multi)) {
            foreach ($multi as $optionId) {
                Database::insert('evalcom_respuesta_opciones', [
                    'serial_res' => $responseId,
                    'serial_pop' => $optionId,
                ]);
            }
        }
    }

    private function buildResponsePayload(array $question, $responseData): ?array
    {
        $type = (string) ($question['tipo_respuesta'] ?? 'TEXTO');
        $isRequired = ($question['es_obligatoria'] ?? 'SI') === 'SI';
        $observation = is_array($responseData) ? $this->nullableText($responseData['observacion'] ?? null) : null;
        $value = is_array($responseData) ? ($responseData['value'] ?? null) : $responseData;

        $base = [
            'serial_eco' => null,
            'serial_pop' => null,
            'valor_texto' => null,
            'valor_numero' => null,
            'valor_booleano' => null,
            'valor_fecha' => null,
            'observacion' => $observation,
            '_multi' => [],
        ];

        switch ($type) {
            case 'TEXTO':
                $text = $this->nullableText($value);
                if ($text === null && !$isRequired && $observation === null) {
                    return null;
                }
                if ($text === null) {
                    throw new InvalidArgumentException('Complete las preguntas obligatorias antes de enviar la evaluación.');
                }
                $base['valor_texto'] = $text;
                return $base;

            case 'NUMERICA_ESCALA':
                $optionId = (int) $value;
                if ($optionId <= 0 && !$isRequired && $observation === null) {
                    return null;
                }
                if ($optionId <= 0) {
                    throw new InvalidArgumentException('Seleccione una opción válida en la escala antes de continuar.');
                }
                $option = $this->getScaleOption($optionId);
                if (!$option) {
                    throw new InvalidArgumentException('Seleccione una opción válida en la escala antes de continuar.');
                }
                $base['serial_eco'] = $optionId;
                $base['valor_numero'] = $option['valor_opc'] !== null ? (float) $option['valor_opc'] : null;
                $base['valor_texto'] = $option['etiqueta_opc'];
                return $base;

            case 'UNICA_OPCION':
                $selectedId = (int) $value;
                if ($selectedId <= 0 && !$isRequired && $observation === null) {
                    return null;
                }
                if ($selectedId <= 0) {
                    throw new InvalidArgumentException('Seleccione una opción válida antes de continuar.');
                }

                if (!empty($question['escala'])) {
                    $option = $this->getScaleOption($selectedId);
                    if (!$option) {
                        throw new InvalidArgumentException('Seleccione una opción válida antes de continuar.');
                    }
                    $base['serial_eco'] = $selectedId;
                    $base['valor_numero'] = $option['valor_opc'] !== null ? (float) $option['valor_opc'] : null;
                    $base['valor_texto'] = $option['etiqueta_opc'];
                    return $base;
                }

                $option = $this->getQuestionOption($selectedId);
                if (!$option) {
                    throw new InvalidArgumentException('Seleccione una opción válida antes de continuar.');
                }
                $base['serial_pop'] = $selectedId;
                $base['valor_texto'] = $option['etiqueta_opc'];
                return $base;

            case 'MULTIPLE_OPCION':
                $selected = is_array($value) ? array_filter(array_map('intval', $value)) : [];
                if (empty($selected) && !$isRequired && $observation === null) {
                    return null;
                }
                if (empty($selected)) {
                    throw new InvalidArgumentException('Complete las preguntas obligatorias antes de enviar la evaluación.');
                }
                $labels = [];
                foreach ($selected as $optionId) {
                    $option = $this->getQuestionOption($optionId);
                    if ($option) {
                        $labels[] = $option['etiqueta_opc'];
                    }
                }
                $base['valor_texto'] = implode(', ', $labels);
                $base['_multi'] = $selected;
                return $base;

            case 'BOOLEANO':
                $boolValue = strtoupper(trim((string) $value));
                if ($boolValue === '' && !$isRequired && $observation === null) {
                    return null;
                }
                if (!in_array($boolValue, ['SI', 'NO'], true)) {
                    throw new InvalidArgumentException('Seleccione Sí o No para continuar.');
                }
                $base['valor_booleano'] = $boolValue;
                $base['valor_texto'] = $boolValue;
                return $base;

            case 'NUMERO':
                if ($value === null || $value === '') {
                    if (!$isRequired && $observation === null) {
                        return null;
                    }
                    throw new InvalidArgumentException('Complete las preguntas obligatorias antes de enviar la evaluación.');
                }
                if (!is_numeric((string) $value)) {
                    throw new InvalidArgumentException('Ingrese un valor numérico válido.');
                }
                $base['valor_numero'] = (float) $value;
                $base['valor_texto'] = (string) $value;
                return $base;

            case 'FECHA':
                $date = $this->normalizeDateInput($value);
                if ($date === null && !$isRequired && $observation === null) {
                    return null;
                }
                if ($date === null) {
                    throw new InvalidArgumentException('Complete las preguntas obligatorias antes de enviar la evaluación.');
                }
                $base['valor_fecha'] = $date;
                $base['valor_texto'] = $date;
                return $base;
        }

        return null;
    }

    private function getScaleOption(int $optionId): ?array
    {
        return Database::fetchOne(
            "SELECT *
             FROM evalcom_escala_opciones
             WHERE serial_eco = ?
             LIMIT 1",
            [$optionId]
        );
    }

    private function getQuestionOption(int $optionId): ?array
    {
        return Database::fetchOne(
            "SELECT *
             FROM evalcom_pregunta_opciones
             WHERE serial_pop = ?
             LIMIT 1",
            [$optionId]
        );
    }

    private function calculateEvaluationScore(int $serialEva): ?float
    {
        $value = Database::fetchColumn(
            "SELECT AVG(valor_numero)
             FROM evalcom_respuestas
             WHERE serial_eva = ?
               AND valor_numero IS NOT NULL",
            [$serialEva]
        );

        return $value === null ? null : (float) $value;
    }

    private function isDiagnosticWindowOpen(?array $period): bool
    {
        if (!$period) {
            return false;
        }

        if (($period['estado_cfg'] ?? '') !== 'ACTIVO') {
            return false;
        }

        $now = time();
        $start = !empty($period['fecha_inicio_diagnostico']) ? strtotime((string) $period['fecha_inicio_diagnostico']) : false;
        $end = !empty($period['fecha_fin_diagnostico']) ? strtotime((string) $period['fecha_fin_diagnostico']) : false;

        if ($start !== false && $start > $now) {
            return false;
        }

        if ($end !== false && $end < $now) {
            return false;
        }

        return true;
    }

    private function buildReferenceTitle(array $instrument, ?array $participant): string
    {
        if (!$participant) {
            return (string) ($instrument['nombre_ins'] ?? 'Registro institucional');
        }

        return trim((string) ($instrument['nombre_ins'] ?? 'Instrumento')) . ' - ' . $this->participantDisplayName($participant);
    }

    private function rowIsEmpty(array $row, array $requiredKeys): bool
    {
        foreach ($requiredKeys as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeDateTimeInput($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }

    private function normalizeDateInput($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    private function normalizeEnum($value, array $allowed): ?string
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '') {
            return null;
        }

        return in_array($value, $allowed, true) ? $value : null;
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function firstNonEmpty(array $values): ?string
    {
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
