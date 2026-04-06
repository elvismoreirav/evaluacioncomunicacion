<?php
require_once __DIR__ . '/../bootstrap.php';

$evaluation = new CommunicationEvaluation();
$activePeriod = $evaluation->getActiveAcademicPeriod();

if (!$activePeriod) {
    throw new RuntimeException('No existe un periodo lectivo activo.');
}

$evaluation->ensurePeriodConfigured((int) $activePeriod['serial_per']);
$period = $evaluation->getConfiguredPeriod((int) $activePeriod['serial_per']);
$allowedInstruments = [
    CommunicationEvaluation::INSTRUMENT_INTERNAL,
    CommunicationEvaluation::INSTRUMENT_EXTERNAL,
];
$instrumentCode = trim((string) ($_REQUEST['instrumento'] ?? CommunicationEvaluation::INSTRUMENT_INTERNAL));

if (!in_array($instrumentCode, $allowedInstruments, true)) {
    throw new RuntimeException('Instrumento no permitido para este acceso.');
}

$instrument = $evaluation->getInstrumentByCode($instrumentCode);
if (!$instrument) {
    throw new RuntimeException('Instrumento no encontrado.');
}

$isInternalAccess = $instrumentCode === CommunicationEvaluation::INSTRUMENT_INTERNAL;
$error = '';
$currentEmployee = null;
$participant = null;
$participantType = 'EXTERNO';
$participantData = [
    'tipo_participante' => 'EXTERNO',
    'nombres_par' => trim((string) ($_POST['nombres_par'] ?? '')),
    'apellidos_par' => trim((string) ($_POST['apellidos_par'] ?? '')),
    'genero_par' => trim((string) ($_POST['genero_par'] ?? '')),
    'cargo_par' => trim((string) ($_POST['cargo_par'] ?? '')),
    'organizacion_par' => trim((string) ($_POST['organizacion_par'] ?? '')),
    'publico_par' => trim((string) ($_POST['publico_par'] ?? '')),
    'email_par' => trim((string) ($_POST['email_par'] ?? '')),
    'telefono_par' => trim((string) ($_POST['telefono_par'] ?? '')),
];

if ($isInternalAccess) {
    Auth::requireEmployee();
    $currentEmployee = Auth::getEmployeeUser();
    $participant = $evaluation->getParticipantByEmployeeId((int) $currentEmployee['serial_epl']);

    if (!$participant) {
        $evaluation->syncActiveEmployees((int) $activePeriod['serial_per']);
        $participant = $evaluation->getParticipantByEmployeeId((int) $currentEmployee['serial_epl']);
    }

    if (!$participant) {
        throw new RuntimeException('No se pudo identificar su registro interno.');
    }

    $context = $evaluation->getEvaluationFormContext((int) $activePeriod['serial_per'], (int) $instrument['serial_ins'], (int) $participant['serial_par']);

    if (request_method_is('POST')) {
        if (!verify_csrf($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('Token CSRF invalido.');
        }

        if (empty($context['ventana_abierta'])) {
            throw new RuntimeException('La ventana del diagnostico no se encuentra habilitada.');
        }

        try {
            $evaluation->saveEvaluationSubmission(
                (int) $activePeriod['serial_per'],
                (int) $instrument['serial_ins'],
                (int) $participant['serial_par'],
                is_array($_POST['responses'] ?? null) ? $_POST['responses'] : [],
                [],
                (string) ($_POST['observacion_general'] ?? ''),
                'ENVIADA'
            );
            set_flash('success', 'Tus respuestas internas se guardaron correctamente.');
            redirect('dashboard.php');
        } catch (Throwable $throwable) {
            $error = $throwable->getMessage();
        }
    }

    $pageTitle = 'Instrumento interno';
} else {
    $participantType = strtoupper(trim((string) ($_POST['tipo_participante'] ?? 'EXTERNO')));
    if (!in_array($participantType, ['EXTERNO', 'MIXTO'], true)) {
        $participantType = 'EXTERNO';
    }
    $participantData['tipo_participante'] = $participantType;

    $context = $evaluation->getEvaluationFormContext((int) $activePeriod['serial_per'], (int) $instrument['serial_ins'], null);

    if (request_method_is('POST')) {
        if (!verify_csrf($_POST['csrf_token'] ?? null)) {
            throw new RuntimeException('Token CSRF invalido.');
        }

        if (empty($context['ventana_abierta'])) {
            throw new RuntimeException('La ventana del diagnostico no se encuentra habilitada.');
        }

        try {
            if ($participantData['nombres_par'] === '' || $participantData['apellidos_par'] === '') {
                throw new InvalidArgumentException('Ingrese nombres y apellidos del participante.');
            }

            if ($participantData['genero_par'] === '') {
                throw new InvalidArgumentException('Seleccione la identificacion de genero del participante.');
            }

            if ($participantData['cargo_par'] === '') {
                throw new InvalidArgumentException('Ingrese el cargo, funcion o representacion del participante.');
            }

            if ($participantData['organizacion_par'] === '') {
                throw new InvalidArgumentException('Ingrese la organizacion, institucion o colectivo del participante.');
            }

            if ($participantData['publico_par'] === '') {
                throw new InvalidArgumentException('Ingrese el publico o sector que representa.');
            }

            $createdParticipantId = 0;

            try {
                $createdParticipantId = $evaluation->saveParticipant([
                    'tipo_participante' => $participantType,
                    'nombres_par' => $participantData['nombres_par'],
                    'apellidos_par' => $participantData['apellidos_par'],
                    'genero_par' => $participantData['genero_par'],
                    'cargo_par' => $participantData['cargo_par'],
                    'organizacion_par' => $participantData['organizacion_par'],
                    'publico_par' => $participantData['publico_par'],
                    'email_par' => $participantData['email_par'],
                    'telefono_par' => $participantData['telefono_par'],
                    'activo_par' => 'SI',
                ]);

                $evaluation->saveEvaluationSubmission(
                    (int) $activePeriod['serial_per'],
                    (int) $instrument['serial_ins'],
                    $createdParticipantId,
                    is_array($_POST['responses'] ?? null) ? $_POST['responses'] : [],
                    [],
                    (string) ($_POST['observacion_general'] ?? ''),
                    'ENVIADA'
                );
            } catch (Throwable $throwable) {
                if ($createdParticipantId > 0) {
                    Database::delete('evalcom_participantes', 'serial_par = ?', [$createdParticipantId]);
                }

                throw $throwable;
            }

            set_flash('success', 'Tu formulario externo fue registrado correctamente.');
            redirect('index.php');
        } catch (Throwable $throwable) {
            $error = $throwable->getMessage();
        }
    }

    $pageTitle = 'Formulario externo';
}

$wizardStep = max(0, (int) ($_POST['wizard_step'] ?? 0));
$evaluationState = strtoupper((string) ($context['evaluacion']['estado_eva'] ?? 'PENDIENTE'));
$evaluationStateLabel = match ($evaluationState) {
    'BORRADOR' => 'Borrador',
    'ENVIADA' => 'Enviada',
    'REVISADA' => 'Revisada',
    'CERRADA' => 'Cerrada',
    default => 'Pendiente',
};
$windowStatusLabel = !empty($context['ventana_abierta']) ? 'Ventana abierta' : 'Solo lectura';
$windowStateClasses = !empty($context['ventana_abierta'])
    ? 'bg-emerald-100 text-emerald-800'
    : 'bg-amber-100 text-amber-800';
$submitLabel = !empty($context['evaluacion']['serial_eva'])
    ? ($isInternalAccess ? 'Actualizar y enviar' : 'Actualizar envio')
    : ($isInternalAccess ? 'Guardar y enviar' : 'Registrar formulario');

$questionHasAnswer = static function (array $question): bool {
    $questionId = (int) $question['serial_pre'];
    $submittedResponse = is_array($_POST['responses'][$questionId] ?? null) ? $_POST['responses'][$questionId] : [];
    $submittedValue = $submittedResponse['value'] ?? null;
    $storedResponse = $question['respuesta_actual'] ?? null;
    $type = (string) ($question['tipo_respuesta'] ?? 'TEXTO');

    return match ($type) {
        'TEXTO' => trim((string) ($submittedValue ?? $storedResponse['valor_texto'] ?? '')) !== '',
        'NUMERICA_ESCALA' => (int) ($submittedValue ?? $storedResponse['serial_eco'] ?? 0) > 0,
        'UNICA_OPCION' => (int) ($submittedValue ?? $storedResponse['serial_eco'] ?? $storedResponse['serial_pop'] ?? 0) > 0,
        'MULTIPLE_OPCION' => !empty(
            is_array($submittedValue)
                ? array_filter(array_map('intval', $submittedValue))
                : ($storedResponse['multi'] ?? [])
        ),
        'NUMERO' => $submittedValue !== null
            ? trim((string) $submittedValue) !== ''
            : trim((string) ($storedResponse['valor_numero'] ?? '')) !== '',
        'FECHA' => trim((string) ($submittedValue ?? $storedResponse['valor_fecha'] ?? '')) !== '',
        'BOOLEANO' => in_array(
            strtoupper(trim((string) ($submittedValue ?? $storedResponse['valor_booleano'] ?? ''))),
            ['SI', 'NO'],
            true
        ),
        default => trim((string) ($submittedValue ?? $storedResponse['valor_texto'] ?? '')) !== '',
    };
};

$steps = [];
$totalQuestions = 0;
$answeredQuestions = 0;

if ($isInternalAccess) {
    $steps[] = [
        'kind' => 'identity',
        'title' => 'Verifique sus datos',
        'description' => 'Confirme que el sistema reconoce correctamente su registro antes de responder el instrumento.',
        'meta' => 'Datos bloqueados',
    ];
} else {
    $participantRequiredFields = ['tipo_participante', 'nombres_par', 'apellidos_par', 'genero_par', 'cargo_par', 'organizacion_par', 'publico_par'];
    $participantCompletedFields = 0;
    foreach ($participantRequiredFields as $requiredField) {
        if (trim((string) ($participantData[$requiredField] ?? '')) !== '') {
            $participantCompletedFields++;
        }
    }

    $steps[] = [
        'kind' => 'participant',
        'title' => 'Identificacion del participante',
        'description' => 'Necesitamos estos datos para contextualizar la respuesta y clasificar la participacion.',
        'meta' => $participantCompletedFields . '/' . count($participantRequiredFields) . ' campos listos',
    ];
}

foreach ($context['secciones'] as $sectionIndex => $section) {
    $sectionQuestionTotal = count($section['preguntas']);
    $sectionAnsweredTotal = 0;

    foreach ($section['preguntas'] as $question) {
        $totalQuestions++;
        if ($questionHasAnswer($question)) {
            $answeredQuestions++;
            $sectionAnsweredTotal++;
        }
    }

    $steps[] = [
        'kind' => 'section',
        'title' => (string) ($section['titulo_sec'] ?? 'Seccion'),
        'description' => (string) ($section['descripcion_sec'] ?? 'Responda las preguntas de esta etapa.'),
        'meta' => $sectionAnsweredTotal . '/' . $sectionQuestionTotal . ' respondidas',
        'section' => $section,
        'section_number' => $sectionIndex + 1,
        'question_total' => $sectionQuestionTotal,
        'answered_total' => $sectionAnsweredTotal,
    ];
}

$steps[] = [
    'kind' => 'review',
    'title' => 'Revision final',
    'description' => 'Agregue observaciones generales y confirme el envio cuando haya terminado.',
    'meta' => 'Resumen y envio',
];

$stepCount = count($steps);
$wizardStep = min($wizardStep, max(0, $stepCount - 1));
$completionPercent = $totalQuestions > 0 ? (int) round(($answeredQuestions / $totalQuestions) * 100) : 0;
$stepProgressPercent = $stepCount > 1 ? (int) round(($wizardStep / ($stepCount - 1)) * 100) : 100;
$publicNavigationMode = $isInternalAccess ? 'employee' : 'none';

include __DIR__ . '/../templates/public_header.php';
?>
<div class="mb-8 flex flex-wrap items-center justify-between gap-4">
    <div class="max-w-3xl">
        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-primary"><?= $isInternalAccess ? 'Instrumento interno' : 'Formulario externo' ?></p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900"><?= htmlspecialchars($instrument['nombre_ins']) ?></h1>
        <p class="mt-3 text-slate-600"><?= htmlspecialchars($instrument['descripcion_ins'] ?? '') ?></p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <span class="inline-flex rounded-full px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] <?= $windowStateClasses ?>">
            <?= $windowStatusLabel ?>
        </span>
        <a href="<?= $isInternalAccess ? 'dashboard.php' : 'index.php' ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
            <?= $isInternalAccess ? 'Volver al panel' : 'Volver al inicio' ?>
        </a>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-[20rem,minmax(0,1fr)]">
    <aside class="space-y-6 xl:sticky xl:top-24 xl:self-start">
        <section class="rounded-[2rem] bg-slate-900 p-6 text-white shadow-xl">
            <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-accent">Progreso</p>
            <div class="mt-4 flex items-end justify-between gap-4">
                <div>
                    <p class="text-4xl font-extrabold"><?= $completionPercent ?>%</p>
                    <p class="mt-1 text-sm text-white/70">Preguntas respondidas</p>
                </div>
                <div class="rounded-2xl bg-white/10 px-4 py-3 text-right">
                    <p class="text-xs font-bold uppercase tracking-wide text-white/60">Paso actual</p>
                    <p class="mt-1 text-lg font-extrabold"><span data-current-step-number><?= $wizardStep + 1 ?></span>/<?= $stepCount ?></p>
                </div>
            </div>
            <div class="mt-5 h-2 overflow-hidden rounded-full bg-white/10">
                <div data-step-progressbar class="h-full rounded-full bg-accent transition-all duration-300" style="width: <?= $stepProgressPercent ?>%"></div>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                <div class="rounded-2xl bg-white/10 px-4 py-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-white/60">Periodo</p>
                    <p class="mt-2 text-sm font-extrabold"><?= htmlspecialchars($period['nombre_per'] ?? '') ?></p>
                </div>
                <div class="rounded-2xl bg-white/10 px-4 py-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-white/60">Estado</p>
                    <p class="mt-2 text-sm font-extrabold"><?= $isInternalAccess ? $evaluationStateLabel : $windowStatusLabel ?></p>
                </div>
                <div class="rounded-2xl bg-white/10 px-4 py-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-white/60">Respuestas</p>
                    <p class="mt-2 text-sm font-extrabold"><?= $answeredQuestions ?> de <?= $totalQuestions ?></p>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-slate-100 bg-white p-4 shadow-sm">
            <p class="px-2 text-xs font-extrabold uppercase tracking-[0.24em] text-primary">Ruta de trabajo</p>
            <div class="mt-4 space-y-3">
                <?php foreach ($steps as $stepIndex => $step): ?>
                <?php $isActiveStep = $stepIndex === $wizardStep; ?>
                <button
                    type="button"
                    data-step-target="<?= $stepIndex ?>"
                    class="flex w-full items-center gap-3 rounded-2xl border px-3 py-3 text-left transition <?= $isActiveStep ? 'border-primary bg-primary/5 shadow-sm' : 'border-slate-200 bg-white hover:border-primary/40 hover:bg-slate-50' ?>"
                    aria-current="<?= $isActiveStep ? 'step' : 'false' ?>"
                >
                    <span data-step-indicator class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl text-sm font-extrabold <?= $isActiveStep ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700' ?>">
                        <?= $stepIndex + 1 ?>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-extrabold text-slate-900"><?= htmlspecialchars($step['title']) ?></span>
                        <span class="mt-1 block text-xs text-slate-500"><?= htmlspecialchars($step['meta']) ?></span>
                    </span>
                </button>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="rounded-[2rem] border border-slate-200 bg-slate-50 p-5">
            <p class="text-sm font-extrabold text-slate-900">Antes de enviar</p>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Responda cada paso en orden. Si una pregunta requiere contexto adicional, use el comentario complementario para aclararla.
            </p>
        </section>
    </aside>

    <div class="space-y-6">
        <?php if ($error): ?>
        <div class="rounded-[2rem] border border-red-200 bg-red-50 px-6 py-5 text-red-800">
            <p class="text-sm font-extrabold uppercase tracking-[0.18em]">No pudimos guardar el formulario</p>
            <p class="mt-2 text-sm leading-6"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <?php if (empty($context['ventana_abierta'])): ?>
        <div class="rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-amber-900">
            <p class="text-sm font-extrabold uppercase tracking-[0.18em]">Formulario en modo consulta</p>
            <p class="mt-2 text-sm leading-6">
                La ventana del diagnostico esta cerrada para este periodo. Puede revisar las preguntas, pero el envio permanecera bloqueado hasta que administracion vuelva a habilitarlo.
            </p>
        </div>
        <?php endif; ?>

        <form method="post" class="space-y-6" data-wizard-form novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="instrumento" value="<?= htmlspecialchars($instrumentCode) ?>">
            <input type="hidden" name="wizard_step" value="<?= $wizardStep ?>">

            <?php foreach ($steps as $stepIndex => $step): ?>
            <section data-step-panel data-step-index="<?= $stepIndex ?>" class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-slate-900 via-primary to-slate-900 px-6 py-6 text-white">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="max-w-3xl">
                            <p class="text-xs font-bold uppercase tracking-[0.24em] text-white/70">Paso <?= $stepIndex + 1 ?> de <?= $stepCount ?></p>
                            <h2 data-step-title class="mt-2 text-2xl font-extrabold"><?= htmlspecialchars($step['title']) ?></h2>
                            <p class="mt-3 text-sm leading-6 text-white/80"><?= htmlspecialchars($step['description']) ?></p>
                        </div>
                        <span class="inline-flex rounded-full bg-white/10 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-white">
                            <?= htmlspecialchars($step['meta']) ?>
                        </span>
                    </div>
                </div>

                <?php if ($step['kind'] === 'identity'): ?>
                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Nombre</p>
                        <p class="mt-2 text-sm font-extrabold text-slate-900"><?= htmlspecialchars($evaluation->participantDisplayName($participant)) ?></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Cedula</p>
                        <p class="mt-2 text-sm font-extrabold text-slate-900"><?= htmlspecialchars($currentEmployee['cedula'] ?? '') ?></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Correo</p>
                        <p class="mt-2 text-sm font-extrabold text-slate-900"><?= htmlspecialchars($participant['email_par'] ?? '') ?></p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Estado actual</p>
                        <p class="mt-2 text-sm font-extrabold text-slate-900"><?= htmlspecialchars($evaluationStateLabel) ?></p>
                    </div>
                    <?php if (!empty($participant['cargo_par'])): ?>
                    <div class="rounded-2xl bg-slate-50 px-4 py-4 md:col-span-2 xl:col-span-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Cargo o area</p>
                        <p class="mt-2 text-sm font-extrabold text-slate-900"><?= htmlspecialchars($participant['cargo_par']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php elseif ($step['kind'] === 'participant'): ?>
                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Categoria</label>
                        <select name="tipo_participante" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" required>
                            <option value="EXTERNO" <?= $participantType === 'EXTERNO' ? 'selected' : '' ?>>Externo</option>
                            <option value="MIXTO" <?= $participantType === 'MIXTO' ? 'selected' : '' ?>>Mixto</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Nombres</label>
                        <input type="text" name="nombres_par" value="<?= htmlspecialchars($participantData['nombres_par']) ?>" autocomplete="given-name" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Apellidos</label>
                        <input type="text" name="apellidos_par" value="<?= htmlspecialchars($participantData['apellidos_par']) ?>" autocomplete="family-name" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Genero</label>
                        <select name="genero_par" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" required>
                            <option value="">Seleccione</option>
                            <?php foreach (['MUJER' => 'Mujer', 'VARON' => 'Varon', 'NO_BINARIO' => 'No binario', 'OTRO' => 'Otro', 'PREFIERE_NO_DECIR' => 'Prefiere no decir'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $participantData['genero_par'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Cargo o representacion</label>
                        <input type="text" name="cargo_par" value="<?= htmlspecialchars($participantData['cargo_par']) ?>" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Organizacion o colectivo</label>
                        <input type="text" name="organizacion_par" value="<?= htmlspecialchars($participantData['organizacion_par']) ?>" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Publico o sector representado</label>
                        <input type="text" name="publico_par" value="<?= htmlspecialchars($participantData['publico_par']) ?>" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" required>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Correo</label>
                        <input type="email" name="email_par" value="<?= htmlspecialchars($participantData['email_par']) ?>" autocomplete="email" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Telefono</label>
                        <input type="text" name="telefono_par" value="<?= htmlspecialchars($participantData['telefono_par']) ?>" autocomplete="tel" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none">
                    </div>
                </div>
                <div class="px-6 pb-6">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-600">
                        Estos datos solo se usan para clasificar la respuesta dentro del diagnostico y contextualizar el analisis final.
                    </div>
                </div>
                <?php elseif ($step['kind'] === 'section'): ?>
                <?php $section = $step['section']; ?>
                <div class="space-y-5 p-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-primary/10 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-primary">
                            Seccion <?= $step['section_number'] ?>
                        </span>
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-slate-600">
                            <?= $step['answered_total'] ?>/<?= $step['question_total'] ?> respondidas
                        </span>
                        <?php if (!empty($section['procesos'])): ?>
                        <?php foreach ($section['procesos'] as $process): ?>
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-600"><?= htmlspecialchars($process['codigo_proceso']) ?></span>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php foreach ($section['preguntas'] as $questionIndex => $question): ?>
                    <?php
                    $response = $question['respuesta_actual'] ?? null;
                    $questionId = (int) $question['serial_pre'];
                    $inputName = "responses[{$questionId}][value]";
                    $observationName = "responses[{$questionId}][observacion]";
                    $submittedResponse = is_array($_POST['responses'][$questionId] ?? null) ? $_POST['responses'][$questionId] : [];
                    $submittedValue = $submittedResponse['value'] ?? null;
                    $submittedObservation = $submittedResponse['observacion'] ?? null;
                    $isRequiredQuestion = ($question['es_obligatoria'] ?? 'SI') === 'SI';
                    $questionAnswered = $questionHasAnswer($question);
                    ?>
                    <div class="rounded-3xl border border-slate-200 p-5 shadow-sm shadow-slate-100/70">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="max-w-3xl">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em] text-slate-600">
                                        Pregunta <?= $questionIndex + 1 ?>
                                    </span>
                                    <span class="rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em] <?= $isRequiredQuestion ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-700' ?>">
                                        <?= $isRequiredQuestion ? 'Obligatoria' : 'Opcional' ?>
                                    </span>
                                    <?php if (!empty($question['codigo_pre'])): ?>
                                    <span class="rounded-full bg-primary/10 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em] text-primary">
                                        <?= htmlspecialchars($question['codigo_pre']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <p class="mt-3 text-base font-bold leading-7 text-slate-900"><?= htmlspecialchars($question['enunciado_pre']) ?></p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-extrabold <?= $questionAnswered ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' ?>">
                                <?= $questionAnswered ? 'Respondida' : 'Pendiente' ?>
                            </span>
                        </div>

                        <?php if (($question['tipo_respuesta'] ?? '') === 'TEXTO'): ?>
                        <textarea name="<?= htmlspecialchars($inputName) ?>" rows="4" class="mt-4 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 leading-6 focus:border-primary focus:outline-none" placeholder="Escriba su respuesta aqui" <?= $isRequiredQuestion ? 'required' : '' ?>><?= htmlspecialchars((string) ($submittedValue ?? $response['valor_texto'] ?? '')) ?></textarea>

                        <?php elseif (in_array($question['tipo_respuesta'] ?? '', ['NUMERICA_ESCALA', 'UNICA_OPCION'], true) && !empty($question['escala'])): ?>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                            <?php foreach ($question['escala'] as $option): ?>
                            <label class="relative">
                                <input type="radio" name="<?= htmlspecialchars($inputName) ?>" value="<?= (int) $option['serial_eco'] ?>" class="peer sr-only" <?= (int) ($submittedValue ?? $response['serial_eco'] ?? 0) === (int) $option['serial_eco'] ? 'checked' : '' ?> <?= $isRequiredQuestion ? 'required' : '' ?>>
                                <span class="flex h-full flex-col justify-between gap-3 rounded-2xl border-2 border-slate-200 px-4 py-4 text-sm font-bold text-slate-700 transition peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                    <span class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-600 peer-checked:bg-primary/10"><?= format_score(isset($option['valor_opc']) ? (float) $option['valor_opc'] : null, 2) ?></span>
                                    <span><?= htmlspecialchars($option['etiqueta_opc']) ?></span>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <?php elseif (($question['tipo_respuesta'] ?? '') === 'UNICA_OPCION'): ?>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <?php foreach ($question['opciones'] as $option): ?>
                            <label class="relative">
                                <input type="radio" name="<?= htmlspecialchars($inputName) ?>" value="<?= (int) $option['serial_pop'] ?>" class="peer sr-only" <?= (int) ($submittedValue ?? $response['serial_pop'] ?? 0) === (int) $option['serial_pop'] ? 'checked' : '' ?> <?= $isRequiredQuestion ? 'required' : '' ?>>
                                <span class="flex h-full items-center rounded-2xl border-2 border-slate-200 px-4 py-4 text-sm font-bold text-slate-700 transition peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                    <?= htmlspecialchars($option['etiqueta_opc']) ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <?php elseif (($question['tipo_respuesta'] ?? '') === 'MULTIPLE_OPCION'): ?>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <?php $selectedMulti = is_array($submittedValue) ? array_map('intval', $submittedValue) : ($response['multi'] ?? []); ?>
                            <?php foreach ($question['opciones'] as $option): ?>
                            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-4 text-sm font-bold text-slate-700">
                                <input type="checkbox" name="<?= htmlspecialchars($inputName) ?>[]" value="<?= (int) $option['serial_pop'] ?>" data-required-multi="<?= $isRequiredQuestion ? '1' : '0' ?>" class="rounded border-slate-300 text-primary focus:ring-primary" <?= in_array((int) $option['serial_pop'], $selectedMulti, true) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($option['etiqueta_opc']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <?php elseif (($question['tipo_respuesta'] ?? '') === 'NUMERO'): ?>
                        <input type="number" step="0.01" name="<?= htmlspecialchars($inputName) ?>" value="<?= htmlspecialchars((string) ($submittedValue ?? $response['valor_numero'] ?? '')) ?>" class="mt-4 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" placeholder="Ingrese un valor numerico" <?= $isRequiredQuestion ? 'required' : '' ?>>

                        <?php elseif (($question['tipo_respuesta'] ?? '') === 'FECHA'): ?>
                        <input type="date" name="<?= htmlspecialchars($inputName) ?>" value="<?= htmlspecialchars((string) ($submittedValue ?? $response['valor_fecha'] ?? '')) ?>" class="mt-4 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" <?= $isRequiredQuestion ? 'required' : '' ?>>

                        <?php elseif (($question['tipo_respuesta'] ?? '') === 'BOOLEANO'): ?>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <?php foreach (['SI' => 'Si', 'NO' => 'No'] as $value => $label): ?>
                            <label class="relative">
                                <input type="radio" name="<?= htmlspecialchars($inputName) ?>" value="<?= $value ?>" class="peer sr-only" <?= (string) ($submittedValue ?? $response['valor_booleano'] ?? '') === $value ? 'checked' : '' ?> <?= $isRequiredQuestion ? 'required' : '' ?>>
                                <span class="flex h-full items-center rounded-2xl border-2 border-slate-200 px-4 py-4 text-sm font-bold text-slate-700 transition peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                    <?= htmlspecialchars($label) ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (($question['permite_observacion'] ?? 'NO') === 'SI'): ?>
                        <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Comentario complementario</label>
                            <textarea name="<?= htmlspecialchars($observationName) ?>" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm leading-6 focus:border-primary focus:outline-none" placeholder="Agregue contexto solo si ayuda a interpretar la respuesta"><?= htmlspecialchars((string) ($submittedObservation ?? $response['observacion'] ?? '')) ?></textarea>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1.5fr),minmax(18rem,0.9fr)]">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Observacion general</label>
                        <textarea name="observacion_general" rows="6" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 leading-6 focus:border-primary focus:outline-none" placeholder="Cierre con hallazgos, contexto o aclaraciones utiles si corresponde"><?= htmlspecialchars((string) ($_POST['observacion_general'] ?? $context['evaluacion']['observacion_general'] ?? '')) ?></textarea>
                    </div>
                    <div class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Resumen</p>
                            <dl class="mt-4 space-y-3 text-sm text-slate-600">
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Preguntas</dt>
                                    <dd class="font-extrabold text-slate-900"><?= $totalQuestions ?></dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Respondidas</dt>
                                    <dd class="font-extrabold text-slate-900"><?= $answeredQuestions ?></dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt>Estado</dt>
                                    <dd class="font-extrabold text-slate-900"><?= $isInternalAccess ? htmlspecialchars($evaluationStateLabel) : htmlspecialchars($windowStatusLabel) ?></dd>
                                </div>
                            </dl>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm leading-6 text-slate-600">
                            Revise que todas las preguntas obligatorias queden respondidas. Si el periodo esta fuera de ventana, el formulario se mantiene visible pero no permitira el envio.
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-6 py-5">
                    <div class="text-sm text-slate-500">
                        <?= $stepIndex === 0 ? 'Comience aqui y avance paso a paso.' : 'Puede volver al paso anterior si necesita corregir algo.' ?>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <?php if ($stepIndex > 0): ?>
                        <button type="button" data-step-prev class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                            Paso anterior
                        </button>
                        <?php endif; ?>

                        <?php if ($stepIndex < $stepCount - 1): ?>
                        <button type="button" data-step-next class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-primary/20 transition hover:bg-primary/90">
                            <?= $stepIndex === $stepCount - 2 ? 'Ir a revision final' : 'Continuar' ?>
                        </button>
                        <?php else: ?>
                            <?php if (!empty($context['ventana_abierta'])): ?>
                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 font-extrabold text-white shadow-lg shadow-primary/30 transition hover:bg-primary/90">
                                <?= $submitLabel ?>
                            </button>
                            <?php else: ?>
                            <span class="inline-flex items-center justify-center rounded-2xl bg-slate-200 px-6 py-3 font-extrabold text-slate-500">
                                Envio no habilitado
                            </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <?php endforeach; ?>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-wizard-form]');
    if (!form) {
        return;
    }

    const panels = Array.from(form.querySelectorAll('[data-step-panel]'));
    const navButtons = Array.from(document.querySelectorAll('[data-step-target]'));
    const stepProgressBars = Array.from(document.querySelectorAll('[data-step-progressbar]'));
    const currentStepNumbers = Array.from(document.querySelectorAll('[data-current-step-number]'));
    const stepInput = form.querySelector('input[name="wizard_step"]');

    let currentStep = Number.parseInt(stepInput ? stepInput.value : '0', 10);
    if (Number.isNaN(currentStep) || currentStep < 0 || currentStep >= panels.length) {
        currentStep = 0;
    }

    function findFirstInvalidControl(panel) {
        const fields = Array.from(panel.querySelectorAll('input, select, textarea')).filter(function (field) {
            return field.type !== 'hidden' && !field.disabled;
        });
        const processedMultiGroups = new Set();

        for (const field of fields) {
            if (field.dataset.requiredMulti === '1') {
                if (processedMultiGroups.has(field.name)) {
                    continue;
                }

                processedMultiGroups.add(field.name);
                const group = fields.filter(function (candidate) {
                    return candidate.name === field.name;
                });

                if (!group.some(function (candidate) { return candidate.checked; })) {
                    return group[0];
                }
            }

            if (!field.checkValidity()) {
                return field;
            }
        }

        return null;
    }

    function updateProgress() {
        const percent = panels.length > 1
            ? Math.round((currentStep / (panels.length - 1)) * 100)
            : 100;

        stepProgressBars.forEach(function (bar) {
            bar.style.width = percent + '%';
        });

        currentStepNumbers.forEach(function (label) {
            label.textContent = String(currentStep + 1);
        });
    }

    function applyNavState(button, active) {
        const indicator = button.querySelector('[data-step-indicator]');

        button.classList.toggle('border-primary', active);
        button.classList.toggle('bg-primary/5', active);
        button.classList.toggle('shadow-sm', active);
        button.classList.toggle('border-slate-200', !active);
        button.classList.toggle('bg-white', !active);

        if (indicator) {
            indicator.classList.toggle('bg-primary', active);
            indicator.classList.toggle('text-white', active);
            indicator.classList.toggle('bg-slate-100', !active);
            indicator.classList.toggle('text-slate-700', !active);
        }
    }

    function showStep(targetStep) {
        currentStep = Math.max(0, Math.min(targetStep, panels.length - 1));

        if (stepInput) {
            stepInput.value = String(currentStep);
        }

        panels.forEach(function (panel, index) {
            panel.hidden = index !== currentStep;
        });

        navButtons.forEach(function (button, index) {
            const active = index === currentStep;
            button.setAttribute('aria-current', active ? 'step' : 'false');
            applyNavState(button, active);
        });

        updateProgress();
    }

    navButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const targetStep = Number.parseInt(button.dataset.stepTarget || '0', 10);

            if (targetStep > currentStep) {
                const invalidControl = findFirstInvalidControl(panels[currentStep]);
                if (invalidControl) {
                    invalidControl.reportValidity();
                    return;
                }
            }

            showStep(targetStep);
        });
    });

    form.querySelectorAll('[data-step-next]').forEach(function (button) {
        button.addEventListener('click', function () {
            const invalidControl = findFirstInvalidControl(panels[currentStep]);
            if (invalidControl) {
                invalidControl.reportValidity();
                return;
            }

            showStep(currentStep + 1);
        });
    });

    form.querySelectorAll('[data-step-prev]').forEach(function (button) {
        button.addEventListener('click', function () {
            showStep(currentStep - 1);
        });
    });

    form.addEventListener('submit', function (event) {
        for (let index = 0; index < panels.length; index += 1) {
            const invalidControl = findFirstInvalidControl(panels[index]);
            if (invalidControl) {
                event.preventDefault();
                showStep(index);
                requestAnimationFrame(function () {
                    invalidControl.reportValidity();
                });
                return;
            }
        }

        if (stepInput) {
            stepInput.value = String(currentStep);
        }
    });

    showStep(currentStep);
});
</script>
<?php include __DIR__ . '/../templates/public_footer.php'; ?>
