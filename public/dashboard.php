<?php
require_once __DIR__ . '/../bootstrap.php';

Auth::requireEmployee();

$evaluation = new CommunicationEvaluation();
$employeeUser = Auth::getEmployeeUser();
$activePeriod = $evaluation->getActiveAcademicPeriod();

if (!$activePeriod) {
    throw new RuntimeException('No existe un periodo lectivo activo.');
}

$dashboard = $evaluation->getEmployeeDashboard((int) $employeeUser['serial_epl'], (int) $activePeriod['serial_per']);
$period = $dashboard['periodo'];
$instrument = $dashboard['instrumento'];
$formHref = 'evaluacion.php?instrumento=' . urlencode((string) $instrument['codigo_ins']);

$pageTitle = 'Panel del personal';
$currentEmployee = $employeeUser;
include __DIR__ . '/../templates/public_header.php';
?>
<div class="mb-8 grid gap-6 items-start lg:grid-cols-[1.2fr,0.8fr]">
    <section class="rounded-[2rem] border border-slate-100 bg-white p-8 shadow-sm">
        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-primary">Panel personal</p>
        <h1 class="mt-3 text-3xl font-extrabold text-slate-900"><?= htmlspecialchars(employee_full_name($dashboard['empleado'])) ?></h1>
        <p class="mt-3 text-slate-600">
            Periodo configurado: <strong><?= htmlspecialchars($period['nombre_per'] ?? '') ?></strong>.
            El instrumento habilitado para este acceso es <strong><?= htmlspecialchars($instrument['nombre_ins']) ?></strong>.
        </p>
        <?php if (($period['estado_cfg'] ?? '') !== 'ACTIVO'): ?>
        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            El periodo está en estado <?= htmlspecialchars($period['estado_cfg'] ?? 'BORRADOR') ?>. La captura no se encuentra habilitada.
        </div>
        <?php endif; ?>
    </section>
    <section class="rounded-[2rem] bg-slate-900 p-8 text-white shadow-xl">
        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-accent">Estado del instrumento</p>
        <div class="mt-5 grid grid-cols-2 gap-3">
            <div class="rounded-2xl bg-white/10 p-4">
                <p class="text-xs text-white/70">Preguntas</p>
                <p class="mt-2 text-2xl font-extrabold"><?= (int) $dashboard['total_preguntas'] ?></p>
            </div>
            <div class="rounded-2xl bg-white/10 p-4">
                <p class="text-xs text-white/70">Respondidas</p>
                <p class="mt-2 text-2xl font-extrabold"><?= (int) $dashboard['respondidas'] ?></p>
            </div>
        </div>
        <p class="mt-4 text-sm text-white/80">
            Estado actual:
            <strong><?= htmlspecialchars($dashboard['evaluacion']['estado_eva'] ?? 'PENDIENTE') ?></strong>
        </p>
        <p class="mt-2 text-sm text-white/80">
            Puntaje parcial:
            <strong><?= format_score(isset($dashboard['evaluacion']['puntaje_final']) ? (float) $dashboard['evaluacion']['puntaje_final'] : null, 2) ?></strong>
        </p>
    </section>
</div>

<section class="rounded-[2rem] border border-slate-100 bg-white p-8 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-primary">Instrumento interno</p>
            <h2 class="mt-2 text-2xl font-extrabold text-slate-900"><?= htmlspecialchars($instrument['nombre_ins']) ?></h2>
            <p class="mt-3 text-slate-600"><?= htmlspecialchars($instrument['descripcion_ins'] ?? '') ?></p>
        </div>
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold <?= !empty($dashboard['evaluacion']['fecha_envio']) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' ?>">
            <?= !empty($dashboard['evaluacion']['fecha_envio']) ? 'Enviado' : 'Pendiente' ?>
        </span>
    </div>

    <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5">
        <p class="text-sm text-slate-600">Periodo de levantamiento</p>
        <p class="mt-1 text-lg font-extrabold text-slate-900"><?= format_datetime($period['fecha_inicio_diagnostico'] ?? null) ?> a <?= format_datetime($period['fecha_fin_diagnostico'] ?? null) ?></p>
    </div>

    <div class="mt-6">
        <?php if (!empty($dashboard['ventana_abierta'])): ?>
        <a href="<?= htmlspecialchars($formHref) ?>" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 font-extrabold text-white shadow-lg shadow-primary/30 transition hover:bg-primary/90">
            <?= !empty($dashboard['evaluacion']['serial_eva']) ? 'Editar respuestas' : 'Iniciar cuestionario' ?>
        </a>
        <?php else: ?>
        <span class="inline-flex items-center justify-center rounded-2xl bg-gray-200 px-6 py-3 font-extrabold text-gray-500">
            Ventana no disponible
        </span>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/../templates/public_footer.php'; ?>
