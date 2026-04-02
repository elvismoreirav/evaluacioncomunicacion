<?php
require_once __DIR__ . '/../bootstrap.php';

Auth::requireAdmin();

$evaluation = new CommunicationEvaluation();
$activeAcademicPeriod = $evaluation->getActiveAcademicPeriod();
$selectedPeriodId = (int) ($_REQUEST['periodo'] ?? ($activeAcademicPeriod['serial_per'] ?? 0));

if ($selectedPeriodId <= 0) {
    throw new RuntimeException('No existe un periodo disponible.');
}

if (request_method_is('POST')) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        throw new RuntimeException('Token CSRF invalido.');
    }

    try {
        $evaluation->savePeriodSettings($selectedPeriodId, [
            'fecha_inicio_diagnostico' => $_POST['fecha_inicio_diagnostico'] ?? '',
            'fecha_fin_diagnostico' => $_POST['fecha_fin_diagnostico'] ?? '',
            'fecha_inicio_revision' => $_POST['fecha_inicio_revision'] ?? '',
            'fecha_fin_revision' => $_POST['fecha_fin_revision'] ?? '',
            'estado_cfg' => $_POST['estado_cfg'] ?? 'BORRADOR',
            'observacion_cfg' => $_POST['observacion_cfg'] ?? '',
        ]);
        set_flash('success', 'Configuracion del periodo actualizada.');
    } catch (Throwable $throwable) {
        set_flash('error', $throwable->getMessage());
    }

    redirect('configuracion.php?periodo=' . $selectedPeriodId);
}

$evaluation->ensurePeriodConfigured($selectedPeriodId);
$period = $evaluation->getConfiguredPeriod($selectedPeriodId);
$periods = $evaluation->getAvailablePeriods();
$instruments = $evaluation->getInstrumentCatalog();

$pageTitle = 'Configuracion';
$activeNav = 'config';
$currentAdmin = Auth::getAdminUser();
include __DIR__ . '/../templates/admin_header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Configuracion base</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Periodo e instrumentos del diagnostico</h1>
        <p class="mt-3 text-slate-600">La estructura del instrumento se siembra desde la guia y aqui se administra la ventana operativa del periodo.</p>
    </div>
    <form method="get" class="rounded-2xl bg-white border border-slate-200 shadow-sm px-4 py-3">
        <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Periodo</label>
        <div class="flex items-center gap-3">
            <select name="periodo" class="rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                <?php foreach ($periods as $periodOption): ?>
                <option value="<?= (int) $periodOption['serial_per'] ?>" <?= (int) $periodOption['serial_per'] === $selectedPeriodId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($periodOption['nombre_per']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="rounded-xl bg-primary px-4 py-2 text-white text-sm font-bold">Cambiar</button>
        </div>
    </form>
</div>

<section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-8 mb-8">
    <form method="post" class="grid lg:grid-cols-5 gap-4 items-end">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="periodo" value="<?= $selectedPeriodId ?>">
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Inicio levantamiento</label>
            <input type="datetime-local" name="fecha_inicio_diagnostico" value="<?= htmlspecialchars(!empty($period['fecha_inicio_diagnostico']) ? date('Y-m-d\TH:i', strtotime((string) $period['fecha_inicio_diagnostico'])) : '') ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Fin levantamiento</label>
            <input type="datetime-local" name="fecha_fin_diagnostico" value="<?= htmlspecialchars(!empty($period['fecha_fin_diagnostico']) ? date('Y-m-d\TH:i', strtotime((string) $period['fecha_fin_diagnostico'])) : '') ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Inicio revision</label>
            <input type="datetime-local" name="fecha_inicio_revision" value="<?= htmlspecialchars(!empty($period['fecha_inicio_revision']) ? date('Y-m-d\TH:i', strtotime((string) $period['fecha_inicio_revision'])) : '') ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Fin revision</label>
            <input type="datetime-local" name="fecha_fin_revision" value="<?= htmlspecialchars(!empty($period['fecha_fin_revision']) ? date('Y-m-d\TH:i', strtotime((string) $period['fecha_fin_revision'])) : '') ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Estado</label>
            <select name="estado_cfg" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                <?php foreach (['BORRADOR', 'ACTIVO', 'CERRADO'] as $status): ?>
                <option value="<?= $status ?>" <?= ($period['estado_cfg'] ?? 'BORRADOR') === $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="lg:col-span-4">
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Observacion administrativa</label>
            <input type="text" name="observacion_cfg" value="<?= htmlspecialchars($period['observacion_cfg'] ?? '') ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Notas internas del periodo">
        </div>
        <div>
            <button type="submit" class="w-full rounded-2xl bg-primary px-5 py-3 text-white font-extrabold hover:bg-primary/90 transition">Guardar periodo</button>
        </div>
    </form>
</section>

<section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-100">
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Instrumentos sembrados</p>
        <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Mapa operativo del diagnostico</h2>
    </div>
    <div class="divide-y divide-slate-100">
        <?php foreach ($instruments as $instrument): ?>
        <div class="px-8 py-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-3xl">
                    <p class="text-xl font-extrabold text-slate-900"><?= htmlspecialchars($instrument['nombre_ins']) ?></p>
                    <p class="mt-2 text-sm text-slate-600"><?= htmlspecialchars($instrument['descripcion_ins'] ?? '') ?></p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-primary"><?= htmlspecialchars($evaluation->audienceLabel($instrument['audiencia_ins'] ?? '')) ?></span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700"><?= htmlspecialchars($instrument['tipo_ins']) ?></span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700"><?= (int) $instrument['total_preguntas'] ?> preguntas</span>
                    </div>
                    <?php if (!empty($instrument['procesos'])): ?>
                    <p class="mt-3 text-sm text-slate-500">
                        Procesos asociados:
                        <?php
                        $labels = array_map(static fn(array $process): string => $process['codigo_proceso'] . ' ' . $process['nombre_proceso'], $instrument['procesos']);
                        echo htmlspecialchars(implode(' | ', $labels));
                        ?>
                    </p>
                    <?php endif; ?>
                </div>
                <a href="captura.php?periodo=<?= $selectedPeriodId ?>&instrumento=<?= (int) $instrument['serial_ins'] ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                    Abrir captura
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
