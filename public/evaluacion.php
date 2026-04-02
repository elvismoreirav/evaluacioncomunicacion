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
            set_flash('success', 'El instrumento interno fue guardado correctamente.');
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

            set_flash('success', 'La respuesta externa fue registrada correctamente.');
            redirect('index.php');
        } catch (Throwable $throwable) {
            $error = $throwable->getMessage();
        }
    }

    $pageTitle = 'Formulario externo';
}

include __DIR__ . '/../templates/public_header.php';
?>
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-primary"><?= $isInternalAccess ? 'Instrumento interno' : 'Formulario externo' ?></p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900"><?= htmlspecialchars($instrument['nombre_ins']) ?></h1>
        <p class="mt-3 text-slate-600"><?= htmlspecialchars($instrument['descripcion_ins'] ?? '') ?></p>
    </div>
    <a href="<?= $isInternalAccess ? 'dashboard.php' : 'index.php' ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
        <?= $isInternalAccess ? 'Volver al panel' : 'Volver al inicio' ?>
    </a>
</div>

<?php if ($error): ?>
<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if (empty($context['ventana_abierta'])): ?>
<div class="mb-6 rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-amber-800">
    La ventana del diagnostico se encuentra cerrada para este periodo. Puede revisar el instrumento, pero no se habilitara el envio.
</div>
<?php endif; ?>

<div class="mb-6 rounded-[2rem] bg-slate-900 p-6 text-white shadow-xl">
    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-white/60">Periodo</p>
            <p class="mt-1 text-lg font-extrabold"><?= htmlspecialchars($period['nombre_per'] ?? '') ?></p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-white/60"><?= $isInternalAccess ? 'Participante' : 'Audiencia' ?></p>
            <p class="mt-1 text-sm text-white/90">
                <?= $isInternalAccess
                    ? htmlspecialchars($evaluation->participantDisplayName($participant))
                    : htmlspecialchars($evaluation->audienceLabel($instrument['audiencia_ins'] ?? '')) ?>
            </p>
        </div>
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-white/60"><?= $isInternalAccess ? 'Estado actual' : 'Ventana' ?></p>
            <p class="mt-1 text-lg font-extrabold">
                <?= $isInternalAccess
                    ? htmlspecialchars($context['evaluacion']['estado_eva'] ?? 'BORRADOR')
                    : (!empty($context['ventana_abierta']) ? 'Habilitada' : 'No disponible') ?>
            </p>
        </div>
    </div>
    <?php if (!empty($instrument['procesos'])): ?>
    <div class="mt-4 flex flex-wrap gap-2">
        <?php foreach ($instrument['procesos'] as $process): ?>
        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold"><?= htmlspecialchars($process['codigo_proceso']) ?></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<form method="post" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="instrumento" value="<?= htmlspecialchars($instrumentCode) ?>">

    <?php if ($isInternalAccess): ?>
    <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm">
        <div class="bg-primary px-6 py-5 text-white">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-white/70">Identificacion interna</p>
            <h2 class="mt-1 text-2xl font-extrabold">Datos del colaborador autenticado</h2>
        </div>
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
            <?php if (!empty($participant['cargo_par'])): ?>
            <div class="rounded-2xl bg-slate-50 px-4 py-4">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Cargo o area</p>
                <p class="mt-2 text-sm font-extrabold text-slate-900"><?= htmlspecialchars($participant['cargo_par']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php else: ?>
    <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm">
        <div class="bg-primary px-6 py-5 text-white">
            <p class="text-xs font-bold uppercase tracking-[0.24em] text-white/70">Identificacion</p>
            <h2 class="mt-1 text-2xl font-extrabold">Datos del participante externo</h2>
            <p class="mt-3 text-sm text-white/80">
                Este enlace se utiliza para organizaciones aliadas, actores externos y participantes mixtos.
            </p>
        </div>
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
                <input type="text" name="nombres_par" value="<?= htmlspecialchars($participantData['nombres_par']) ?>" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" required>
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Apellidos</label>
                <input type="text" name="apellidos_par" value="<?= htmlspecialchars($participantData['apellidos_par']) ?>" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" required>
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
                <input type="email" name="email_par" value="<?= htmlspecialchars($participantData['email_par']) ?>" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none">
            </div>
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Telefono</label>
                <input type="text" name="telefono_par" value="<?= htmlspecialchars($participantData['telefono_par']) ?>" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none">
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php foreach ($context['secciones'] as $index => $section): ?>
    <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm">
        <div class="bg-primary px-6 py-5 text-white">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.24em] text-white/70">Seccion <?= $index + 1 ?></p>
                    <h2 class="mt-1 text-2xl font-extrabold"><?= htmlspecialchars($section['titulo_sec']) ?></h2>
                </div>
                <?php if (!empty($section['procesos'])): ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($section['procesos'] as $process): ?>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold"><?= htmlspecialchars($process['codigo_proceso']) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($section['descripcion_sec'])): ?>
            <p class="mt-3 text-sm text-white/80"><?= htmlspecialchars($section['descripcion_sec']) ?></p>
            <?php endif; ?>
        </div>
        <div class="space-y-5 p-6">
            <?php foreach ($section['preguntas'] as $question): ?>
            <?php
            $response = $question['respuesta_actual'] ?? null;
            $questionId = (int) $question['serial_pre'];
            $inputName = "responses[{$questionId}][value]";
            $observationName = "responses[{$questionId}][observacion]";
            $submittedResponse = is_array($_POST['responses'][$questionId] ?? null) ? $_POST['responses'][$questionId] : [];
            $submittedValue = $submittedResponse['value'] ?? null;
            $submittedObservation = $submittedResponse['observacion'] ?? null;
            ?>
            <div class="rounded-3xl border border-slate-200 p-5">
                <p class="text-base font-bold text-slate-900"><?= htmlspecialchars($question['enunciado_pre']) ?></p>

                <?php if (($question['tipo_respuesta'] ?? '') === 'TEXTO'): ?>
                <textarea name="<?= htmlspecialchars($inputName) ?>" rows="4" class="mt-4 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>><?= htmlspecialchars((string) ($submittedValue ?? $response['valor_texto'] ?? '')) ?></textarea>

                <?php elseif (in_array($question['tipo_respuesta'] ?? '', ['NUMERICA_ESCALA', 'UNICA_OPCION'], true) && !empty($question['escala'])): ?>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <?php foreach ($question['escala'] as $option): ?>
                    <label class="relative">
                        <input type="radio" name="<?= htmlspecialchars($inputName) ?>" value="<?= (int) $option['serial_eco'] ?>" class="peer sr-only" <?= (int) ($submittedValue ?? $response['serial_eco'] ?? 0) === (int) $option['serial_eco'] ? 'checked' : '' ?> <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>>
                        <span class="flex h-full items-center justify-between rounded-2xl border-2 border-slate-200 px-4 py-4 text-sm font-bold text-slate-700 transition peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                            <span><?= format_score(isset($option['valor_opc']) ? (float) $option['valor_opc'] : null, 2) ?></span>
                            <span class="text-right"><?= htmlspecialchars($option['etiqueta_opc']) ?></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <?php elseif (($question['tipo_respuesta'] ?? '') === 'UNICA_OPCION'): ?>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <?php foreach ($question['opciones'] as $option): ?>
                    <label class="relative">
                        <input type="radio" name="<?= htmlspecialchars($inputName) ?>" value="<?= (int) $option['serial_pop'] ?>" class="peer sr-only" <?= (int) ($submittedValue ?? $response['serial_pop'] ?? 0) === (int) $option['serial_pop'] ? 'checked' : '' ?> <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>>
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
                        <input type="checkbox" name="<?= htmlspecialchars($inputName) ?>[]" value="<?= (int) $option['serial_pop'] ?>" class="rounded border-slate-300 text-primary focus:ring-primary" <?= in_array((int) $option['serial_pop'], $selectedMulti, true) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($option['etiqueta_opc']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <?php elseif (($question['tipo_respuesta'] ?? '') === 'NUMERO'): ?>
                <input type="number" step="0.01" name="<?= htmlspecialchars($inputName) ?>" value="<?= htmlspecialchars((string) ($submittedValue ?? $response['valor_numero'] ?? '')) ?>" class="mt-4 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>>

                <?php elseif (($question['tipo_respuesta'] ?? '') === 'FECHA'): ?>
                <input type="date" name="<?= htmlspecialchars($inputName) ?>" value="<?= htmlspecialchars((string) ($submittedValue ?? $response['valor_fecha'] ?? '')) ?>" class="mt-4 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>>

                <?php elseif (($question['tipo_respuesta'] ?? '') === 'BOOLEANO'): ?>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <?php foreach (['SI' => 'Si', 'NO' => 'No'] as $value => $label): ?>
                    <label class="relative">
                        <input type="radio" name="<?= htmlspecialchars($inputName) ?>" value="<?= $value ?>" class="peer sr-only" <?= (string) ($submittedValue ?? $response['valor_booleano'] ?? '') === $value ? 'checked' : '' ?> <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>>
                        <span class="flex h-full items-center rounded-2xl border-2 border-slate-200 px-4 py-4 text-sm font-bold text-slate-700 transition peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                            <?= htmlspecialchars($label) ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (($question['permite_observacion'] ?? 'NO') === 'SI'): ?>
                <textarea name="<?= htmlspecialchars($observationName) ?>" rows="3" class="mt-4 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Observaciones o comentarios"><?= htmlspecialchars((string) ($submittedObservation ?? $response['observacion'] ?? '')) ?></textarea>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>

    <section class="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm">
        <label class="mb-2 block text-sm font-bold text-slate-700">Observacion general</label>
        <textarea name="observacion_general" rows="4" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" placeholder="Ingrese observaciones generales si corresponde"><?= htmlspecialchars((string) ($_POST['observacion_general'] ?? $context['evaluacion']['observacion_general'] ?? '')) ?></textarea>
        <div class="mt-6 flex flex-wrap gap-3">
            <?php if (!empty($context['ventana_abierta'])): ?>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 font-extrabold text-white shadow-lg shadow-primary/30 transition hover:bg-primary/90">
                <?= $isInternalAccess ? 'Guardar instrumento' : 'Enviar formulario' ?>
            </button>
            <?php else: ?>
            <span class="inline-flex items-center justify-center rounded-2xl bg-slate-200 px-6 py-3 font-extrabold text-slate-500">
                Captura no habilitada
            </span>
            <?php endif; ?>
            <a href="<?= $isInternalAccess ? 'dashboard.php' : 'index.php' ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-6 py-3 font-bold text-slate-700 transition hover:bg-slate-100">
                Cancelar
            </a>
        </div>
    </section>
</form>
<?php include __DIR__ . '/../templates/public_footer.php'; ?>
