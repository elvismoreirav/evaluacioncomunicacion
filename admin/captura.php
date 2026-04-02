<?php
require_once __DIR__ . '/../bootstrap.php';

Auth::requireAdmin();

$evaluation = new CommunicationEvaluation();
$activeAcademicPeriod = $evaluation->getActiveAcademicPeriod();
$selectedPeriodId = (int) ($_REQUEST['periodo'] ?? ($activeAcademicPeriod['serial_per'] ?? 0));

if ($selectedPeriodId <= 0) {
    throw new RuntimeException('No existe un periodo disponible.');
}

$evaluation->ensurePeriodConfigured($selectedPeriodId);
$period = $evaluation->getConfiguredPeriod($selectedPeriodId);
$periods = $evaluation->getAvailablePeriods();
$instruments = $evaluation->getInstrumentCatalog();
$selectedInstrumentId = (int) ($_REQUEST['instrumento'] ?? ($instruments[0]['serial_ins'] ?? 0));
$selectedParticipantRaw = (string) ($_REQUEST['participante'] ?? '');
$selectedParticipantId = $selectedParticipantRaw === '' ? null : (int) $selectedParticipantRaw;
$selectedInstrument = $selectedInstrumentId > 0 ? $evaluation->getInstrumentById($selectedInstrumentId) : null;
$participants = $selectedInstrument ? $evaluation->getParticipantsForInstrument($selectedInstrumentId) : [];
$context = null;

if (request_method_is('POST')) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        throw new RuntimeException('Token CSRF invalido.');
    }

    $selectedInstrumentId = (int) ($_POST['instrumento'] ?? 0);
    $selectedParticipantRaw = (string) ($_POST['participante'] ?? '');
    $selectedParticipantId = $selectedParticipantRaw === '' ? null : (int) $selectedParticipantRaw;

    try {
        $evaluation->saveEvaluationSubmission(
            $selectedPeriodId,
            $selectedInstrumentId,
            $selectedParticipantId,
            is_array($_POST['responses'] ?? null) ? $_POST['responses'] : [],
            is_array($_POST['extra'] ?? null) ? $_POST['extra'] : [],
            (string) ($_POST['observacion_general'] ?? ''),
            'ENVIADA'
        );
        set_flash('success', 'Instrumento guardado correctamente.');
    } catch (Throwable $throwable) {
        set_flash('error', $throwable->getMessage());
    }

    $redirect = 'captura.php?periodo=' . $selectedPeriodId . '&instrumento=' . $selectedInstrumentId;
    if ($selectedParticipantId) {
        $redirect .= '&participante=' . $selectedParticipantId;
    }
    redirect($redirect);
}

if ($selectedInstrument) {
    if (($selectedInstrument['requiere_participante'] ?? 'SI') === 'NO') {
        $context = $evaluation->getEvaluationFormContext($selectedPeriodId, $selectedInstrumentId, null);
    } elseif ($selectedParticipantId) {
        $context = $evaluation->getEvaluationFormContext($selectedPeriodId, $selectedInstrumentId, $selectedParticipantId);
    }
}

$pageTitle = 'Captura';
$activeNav = 'captura';
$currentAdmin = Auth::getAdminUser();
include __DIR__ . '/../templates/admin_header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Captura administrativa</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Levantamiento del diagnostico de comunicacion</h1>
        <p class="mt-3 text-slate-600">Seleccione instrumento y participante para registrar entrevistas, auditorias o matrices institucionales.</p>
    </div>
    <a href="resultados.php?periodo=<?= $selectedPeriodId ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
        Ver reportes
    </a>
</div>

<section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-8 mb-8">
    <form method="get" class="grid lg:grid-cols-[0.9fr,1.1fr,1.1fr,auto] gap-4 items-end">
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Periodo</label>
            <select name="periodo" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                <?php foreach ($periods as $periodOption): ?>
                <option value="<?= (int) $periodOption['serial_per'] ?>" <?= (int) $periodOption['serial_per'] === $selectedPeriodId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($periodOption['nombre_per']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Instrumento</label>
            <select name="instrumento" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" onchange="this.form.submit()">
                <?php foreach ($instruments as $instrument): ?>
                <option value="<?= (int) $instrument['serial_ins'] ?>" <?= (int) $instrument['serial_ins'] === $selectedInstrumentId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($instrument['nombre_ins']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Participante</label>
            <select name="participante" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" <?= ($selectedInstrument && ($selectedInstrument['requiere_participante'] ?? 'SI') === 'NO') ? 'disabled' : '' ?>>
                <option value="">Seleccione</option>
                <?php foreach ($participants as $participant): ?>
                <option value="<?= (int) $participant['serial_par'] ?>" <?= (int) ($selectedParticipantId ?? 0) === (int) $participant['serial_par'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($evaluation->participantDisplayName($participant)) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($selectedInstrument && ($selectedInstrument['requiere_participante'] ?? 'SI') === 'NO'): ?>
            <p class="mt-2 text-xs text-slate-500">Este instrumento es institucional y no requiere participante.</p>
            <?php endif; ?>
        </div>
        <button type="submit" class="rounded-2xl bg-primary px-5 py-3 text-white text-sm font-bold hover:bg-primary/90 transition">Abrir</button>
    </form>
</section>

<?php if ($selectedInstrument && ($selectedInstrument['requiere_participante'] ?? 'SI') === 'SI' && !$selectedParticipantId): ?>
<div class="rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-amber-800">
    Seleccione un participante para continuar con este instrumento.
</div>
<?php endif; ?>

<?php if ($context): ?>
<?php
$instrument = $context['instrumento'];
$participant = $context['participante'];
$evaluationRow = $context['evaluacion'];
$extraRows = $context['filas_extra'];
?>
<div class="mb-6 rounded-[2rem] bg-slate-900 text-white p-6 shadow-xl">
    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <p class="text-xs uppercase tracking-wide text-white/60 font-bold">Periodo</p>
            <p class="mt-1 text-lg font-extrabold"><?= htmlspecialchars($context['periodo']['nombre_per'] ?? '') ?></p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-white/60 font-bold">Instrumento</p>
            <p class="mt-1 text-sm text-white/90"><?= htmlspecialchars($instrument['nombre_ins']) ?></p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-white/60 font-bold">Estado actual</p>
            <p class="mt-1 text-lg font-extrabold"><?= htmlspecialchars($evaluationRow['estado_eva'] ?? 'BORRADOR') ?></p>
        </div>
    </div>
    <p class="mt-4 text-sm text-white/80">
        <?= htmlspecialchars($participant ? $evaluation->participantDisplayName($participant) : 'Registro institucional') ?>
        <?php if (!empty($instrument['procesos'])): ?>
        ·
        <?php
        $processNames = array_map(static fn(array $process): string => $process['codigo_proceso'], $instrument['procesos']);
        echo htmlspecialchars(implode(' | ', $processNames));
        ?>
        <?php endif; ?>
    </p>
</div>

<form method="post" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="periodo" value="<?= $selectedPeriodId ?>">
    <input type="hidden" name="instrumento" value="<?= (int) $instrument['serial_ins'] ?>">
    <input type="hidden" name="participante" value="<?= (int) ($participant['serial_par'] ?? 0) ?>">

    <?php foreach ($context['secciones'] as $index => $section): ?>
    <section class="rounded-[2rem] bg-white shadow-sm border border-slate-100 overflow-hidden">
        <div class="bg-primary px-6 py-5 text-white">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] font-bold text-white/70">Seccion <?= $index + 1 ?></p>
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

        <?php if (!empty($section['preguntas'])): ?>
        <div class="p-6 space-y-5">
            <?php foreach ($section['preguntas'] as $question): ?>
            <?php
            $response = $question['respuesta_actual'] ?? null;
            $questionId = (int) $question['serial_pre'];
            $inputName = "responses[{$questionId}][value]";
            $observationName = "responses[{$questionId}][observacion]";
            ?>
            <div class="rounded-3xl border border-slate-200 p-5">
                <p class="text-base font-bold text-slate-900"><?= htmlspecialchars($question['enunciado_pre']) ?></p>

                <?php if (($question['tipo_respuesta'] ?? '') === 'TEXTO'): ?>
                <textarea name="<?= htmlspecialchars($inputName) ?>" rows="4" class="mt-4 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>><?= htmlspecialchars($response['valor_texto'] ?? '') ?></textarea>

                <?php elseif (in_array($question['tipo_respuesta'] ?? '', ['NUMERICA_ESCALA', 'UNICA_OPCION'], true) && !empty($question['escala'])): ?>
                <div class="mt-4 grid sm:grid-cols-2 xl:grid-cols-5 gap-3">
                    <?php foreach ($question['escala'] as $option): ?>
                    <label class="relative">
                        <input type="radio" name="<?= htmlspecialchars($inputName) ?>" value="<?= (int) $option['serial_eco'] ?>" class="peer sr-only" <?= (int) ($response['serial_eco'] ?? 0) === (int) $option['serial_eco'] ? 'checked' : '' ?> <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>>
                        <span class="flex h-full items-center justify-between rounded-2xl border-2 border-slate-200 px-4 py-4 text-sm font-bold text-slate-700 transition peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                            <span><?= format_score(isset($option['valor_opc']) ? (float) $option['valor_opc'] : null, 2) ?></span>
                            <span class="text-right"><?= htmlspecialchars($option['etiqueta_opc']) ?></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <?php elseif (($question['tipo_respuesta'] ?? '') === 'UNICA_OPCION'): ?>
                <div class="mt-4 grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
                    <?php foreach ($question['opciones'] as $option): ?>
                    <label class="relative">
                        <input type="radio" name="<?= htmlspecialchars($inputName) ?>" value="<?= (int) $option['serial_pop'] ?>" class="peer sr-only" <?= (int) ($response['serial_pop'] ?? 0) === (int) $option['serial_pop'] ? 'checked' : '' ?> <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>>
                        <span class="flex h-full items-center rounded-2xl border-2 border-slate-200 px-4 py-4 text-sm font-bold text-slate-700 transition peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                            <?= htmlspecialchars($option['etiqueta_opc']) ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <?php elseif (($question['tipo_respuesta'] ?? '') === 'MULTIPLE_OPCION'): ?>
                <div class="mt-4 grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
                    <?php $selectedMulti = $response['multi'] ?? []; ?>
                    <?php foreach ($question['opciones'] as $option): ?>
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-4 text-sm font-bold text-slate-700">
                        <input type="checkbox" name="<?= htmlspecialchars($inputName) ?>[]" value="<?= (int) $option['serial_pop'] ?>" class="rounded border-slate-300 text-primary focus:ring-primary" <?= in_array((int) $option['serial_pop'], $selectedMulti, true) ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($option['etiqueta_opc']) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <?php elseif (($question['tipo_respuesta'] ?? '') === 'NUMERO'): ?>
                <input type="number" step="0.01" name="<?= htmlspecialchars($inputName) ?>" value="<?= htmlspecialchars((string) ($response['valor_numero'] ?? '')) ?>" class="mt-4 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>>

                <?php elseif (($question['tipo_respuesta'] ?? '') === 'FECHA'): ?>
                <input type="date" name="<?= htmlspecialchars($inputName) ?>" value="<?= htmlspecialchars((string) ($response['valor_fecha'] ?? '')) ?>" class="mt-4 w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" <?= ($question['es_obligatoria'] ?? 'SI') === 'SI' ? 'required' : '' ?>>
                <?php endif; ?>

                <?php if (($question['permite_observacion'] ?? 'NO') === 'SI'): ?>
                <textarea name="<?= htmlspecialchars($observationName) ?>" rows="3" class="mt-4 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Observaciones o comentarios"><?= htmlspecialchars($response['observacion'] ?? '') ?></textarea>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
    <?php endforeach; ?>

    <?php if (!empty($extraRows['mapeo'])): ?>
    <section class="rounded-[2rem] bg-white shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Matriz</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Mapeo y caracterizacion de publicos</h2>
            </div>
            <button type="button" data-add-row="mapeo" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">Agregar fila</button>
        </div>
        <div class="space-y-4" data-repeater-container="mapeo">
            <?php foreach ($extraRows['mapeo'] as $index => $row): ?>
            <div class="rounded-3xl border border-slate-200 p-5" data-repeater-row>
                <div class="grid lg:grid-cols-3 gap-3">
                    <select name="extra[mapeo][<?= $index ?>][tipo_publico]" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                        <?php foreach (['INTERNO', 'EXTERNO', 'MIXTO'] as $type): ?>
                        <option value="<?= $type ?>" <?= ($row['tipo_publico'] ?? 'EXTERNO') === $type ? 'selected' : '' ?>><?= htmlspecialchars($evaluation->participantTypeLabel($type)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="extra[mapeo][<?= $index ?>][nombre_publico]" value="<?= htmlspecialchars($row['nombre_publico'] ?? '') ?>" placeholder="Nombre del publico" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="extra[mapeo][<?= $index ?>][categoria_grupo]" value="<?= htmlspecialchars($row['categoria_grupo'] ?? '') ?>" placeholder="Categoria o grupo" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <select name="extra[mapeo][<?= $index ?>][situacion_publico]" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                        <option value="">Situacion</option>
                        <?php foreach (['ALIADO', 'INDECISO', 'A_CONVENCER', 'OTRO'] as $status): ?>
                        <option value="<?= $status ?>" <?= ($row['situacion_publico'] ?? '') === $status ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="extra[mapeo][<?= $index ?>][fuente_informacion]" value="<?= htmlspecialchars($row['fuente_informacion'] ?? '') ?>" placeholder="Fuente de informacion" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <select name="extra[mapeo][<?= $index ?>][influencia_directa]" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                        <option value="NO" <?= ($row['influencia_directa'] ?? 'NO') === 'NO' ? 'selected' : '' ?>>Sin influencia directa</option>
                        <option value="SI" <?= ($row['influencia_directa'] ?? '') === 'SI' ? 'selected' : '' ?>>Con influencia directa</option>
                    </select>
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mt-3">
                    <textarea name="extra[mapeo][<?= $index ?>][estrategia_influencia]" rows="2" placeholder="Como se puede influir" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['estrategia_influencia'] ?? '') ?></textarea>
                    <textarea name="extra[mapeo][<?= $index ?>][necesidades_comunicacion]" rows="2" placeholder="Necesidades de comunicacion" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['necesidades_comunicacion'] ?? '') ?></textarea>
                    <textarea name="extra[mapeo][<?= $index ?>][intereses_valores_creencias]" rows="2" placeholder="Intereses, valores y creencias" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['intereses_valores_creencias'] ?? '') ?></textarea>
                    <textarea name="extra[mapeo][<?= $index ?>][medios_preferenciales]" rows="2" placeholder="Medios preferenciales" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['medios_preferenciales'] ?? '') ?></textarea>
                    <textarea name="extra[mapeo][<?= $index ?>][cambio_buscado]" rows="2" placeholder="Cambio buscado" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['cambio_buscado'] ?? '') ?></textarea>
                    <textarea name="extra[mapeo][<?= $index ?>][tono_lenguaje]" rows="2" placeholder="Tono y lenguaje" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['tono_lenguaje'] ?? '') ?></textarea>
                    <textarea name="extra[mapeo][<?= $index ?>][respuesta_necesidades]" rows="2" placeholder="Como se responde a necesidades y preferencias" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['respuesta_necesidades'] ?? '') ?></textarea>
                    <textarea name="extra[mapeo][<?= $index ?>][mapa_empatia]" rows="2" placeholder="Mapa de empatia o buyer persona" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['mapa_empatia'] ?? '') ?></textarea>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($extraRows['herramientas'])): ?>
    <section class="rounded-[2rem] bg-white shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Inventario</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Herramientas y espacios de comunicacion</h2>
            </div>
            <button type="button" data-add-row="herramientas" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">Agregar fila</button>
        </div>
        <div class="space-y-4" data-repeater-container="herramientas">
            <?php foreach ($extraRows['herramientas'] as $index => $row): ?>
            <div class="rounded-3xl border border-slate-200 p-5" data-repeater-row>
                <div class="grid lg:grid-cols-3 gap-3">
                    <input type="text" name="extra[herramientas][<?= $index ?>][plataforma_herramienta]" value="<?= htmlspecialchars($row['plataforma_herramienta'] ?? '') ?>" placeholder="Plataforma o herramienta" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="extra[herramientas][<?= $index ?>][frecuencia_uso]" value="<?= htmlspecialchars($row['frecuencia_uso'] ?? '') ?>" placeholder="Frecuencia de uso" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="extra[herramientas][<?= $index ?>][personas_alcanzadas]" value="<?= htmlspecialchars($row['personas_alcanzadas'] ?? '') ?>" placeholder="Personas alcanzadas" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="extra[herramientas][<?= $index ?>][area_responsable]" value="<?= htmlspecialchars($row['area_responsable'] ?? '') ?>" placeholder="Area responsable" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <textarea name="extra[herramientas][<?= $index ?>][proposito_herramienta]" rows="2" placeholder="Proposito" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['proposito_herramienta'] ?? '') ?></textarea>
                    <textarea name="extra[herramientas][<?= $index ?>][observaciones_herramienta]" rows="2" placeholder="Observaciones" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['observaciones_herramienta'] ?? '') ?></textarea>
                </div>
                <textarea name="extra[herramientas][<?= $index ?>][recomendaciones_herramienta]" rows="2" placeholder="Recomendaciones" class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['recomendaciones_herramienta'] ?? '') ?></textarea>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($extraRows['medios'])): ?>
    <section class="rounded-[2rem] bg-white shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Archivo</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Archivos mediaticos</h2>
            </div>
            <button type="button" data-add-row="medios" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">Agregar fila</button>
        </div>
        <div class="space-y-4" data-repeater-container="medios">
            <?php foreach ($extraRows['medios'] as $index => $row): ?>
            <div class="rounded-3xl border border-slate-200 p-5" data-repeater-row>
                <div class="grid lg:grid-cols-3 gap-3">
                    <input type="date" name="extra[medios][<?= $index ?>][fecha_referencia]" value="<?= htmlspecialchars($row['fecha_referencia'] ?? '') ?>" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="extra[medios][<?= $index ?>][tipo_medio]" value="<?= htmlspecialchars($row['tipo_medio'] ?? '') ?>" placeholder="Tipo de medio" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="extra[medios][<?= $index ?>][nombre_medio]" value="<?= htmlspecialchars($row['nombre_medio'] ?? '') ?>" placeholder="Nombre del medio" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mt-3">
                    <input type="text" name="extra[medios][<?= $index ?>][titulo_referencia]" value="<?= htmlspecialchars($row['titulo_referencia'] ?? '') ?>" placeholder="Titulo o referencia" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="extra[medios][<?= $index ?>][url_referencia]" value="<?= htmlspecialchars($row['url_referencia'] ?? '') ?>" placeholder="URL o ubicacion" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <textarea name="extra[medios][<?= $index ?>][representacion_institucion]" rows="2" placeholder="Como se representa a la institucion" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['representacion_institucion'] ?? '') ?></textarea>
                    <textarea name="extra[medios][<?= $index ?>][ejes_tematicos]" rows="2" placeholder="Ejes tematicos" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['ejes_tematicos'] ?? '') ?></textarea>
                    <textarea name="extra[medios][<?= $index ?>][evaluacion_historica]" rows="2" placeholder="Evaluacion en el tiempo" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['evaluacion_historica'] ?? '') ?></textarea>
                    <textarea name="extra[medios][<?= $index ?>][mejora_relaciones_publicas]" rows="2" placeholder="Mejoras en relaciones publicas" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['mejora_relaciones_publicas'] ?? '') ?></textarea>
                </div>
                <textarea name="extra[medios][<?= $index ?>][observacion_vocerias]" rows="2" placeholder="Observaciones sobre vocerias" class="mt-3 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['observacion_vocerias'] ?? '') ?></textarea>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($extraRows['institucionales'])): ?>
    <section class="rounded-[2rem] bg-white shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Archivo</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Archivos de la propia institucion</h2>
            </div>
            <button type="button" data-add-row="institucionales" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">Agregar fila</button>
        </div>
        <div class="space-y-4" data-repeater-container="institucionales">
            <?php foreach ($extraRows['institucionales'] as $index => $row): ?>
            <div class="rounded-3xl border border-slate-200 p-5" data-repeater-row>
                <div class="grid lg:grid-cols-3 gap-3">
                    <input type="text" name="extra[institucionales][<?= $index ?>][tipo_recurso]" value="<?= htmlspecialchars($row['tipo_recurso'] ?? '') ?>" placeholder="Tipo de recurso" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="extra[institucionales][<?= $index ?>][titulo_recurso]" value="<?= htmlspecialchars($row['titulo_recurso'] ?? '') ?>" placeholder="Titulo del recurso" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="date" name="extra[institucionales][<?= $index ?>][fecha_recurso]" value="<?= htmlspecialchars($row['fecha_recurso'] ?? '') ?>" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mt-3">
                    <input type="text" name="extra[institucionales][<?= $index ?>][publico_objetivo]" value="<?= htmlspecialchars($row['publico_objetivo'] ?? '') ?>" placeholder="Publico objetivo" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="extra[institucionales][<?= $index ?>][url_recurso]" value="<?= htmlspecialchars($row['url_recurso'] ?? '') ?>" placeholder="URL o ubicacion" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <textarea name="extra[institucionales][<?= $index ?>][descripcion_recurso]" rows="2" placeholder="Descripcion" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['descripcion_recurso'] ?? '') ?></textarea>
                    <textarea name="extra[institucionales][<?= $index ?>][observaciones_recurso]" rows="2" placeholder="Observaciones" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none"><?= htmlspecialchars($row['observaciones_recurso'] ?? '') ?></textarea>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="rounded-[2rem] bg-white shadow-sm border border-slate-100 p-6">
        <label class="block text-sm font-bold text-slate-700 mb-2">Observacion general</label>
        <textarea name="observacion_general" rows="4" class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 focus:border-primary focus:outline-none" placeholder="Notas finales del levantamiento"><?= htmlspecialchars($evaluationRow['observacion_general'] ?? '') ?></textarea>
        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 text-white font-extrabold shadow-lg shadow-primary/30 hover:bg-primary/90 transition">
                Guardar instrumento
            </button>
            <a href="resultados.php?periodo=<?= $selectedPeriodId ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-6 py-3 font-bold text-slate-700 hover:bg-slate-100 transition">
                Volver
            </a>
        </div>
    </section>
</form>

<script>
document.querySelectorAll('[data-add-row]').forEach((button) => {
    button.addEventListener('click', () => {
        const key = button.dataset.addRow;
        const container = document.querySelector(`[data-repeater-container="${key}"]`);
        if (!container) {
            return;
        }

        const rows = container.querySelectorAll('[data-repeater-row]');
        if (rows.length === 0) {
            return;
        }

        const clone = rows[rows.length - 1].cloneNode(true);
        const newIndex = rows.length;

        clone.querySelectorAll('input, textarea, select').forEach((field) => {
            if (field.name) {
                field.name = field.name.replace(/\[\d+\]/, `[${newIndex}]`);
            }

            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        });

        container.appendChild(clone);
    });
});
</script>
<?php endif; ?>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
