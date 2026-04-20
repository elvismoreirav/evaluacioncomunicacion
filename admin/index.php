<?php
require_once __DIR__ . '/../bootstrap.php';

Auth::requireAdmin();

$evaluation = new CommunicationEvaluation();
$activeAcademicPeriod = $evaluation->getActiveAcademicPeriod();
$selectedPeriodId = (int) ($_GET['periodo'] ?? ($activeAcademicPeriod['serial_per'] ?? 0));

if ($selectedPeriodId <= 0) {
    throw new RuntimeException('No existe un período disponible.');
}

$evaluation->ensurePeriodConfigured($selectedPeriodId);
$sync = $evaluation->syncActiveEmployees($selectedPeriodId);
$period = $evaluation->getConfiguredPeriod($selectedPeriodId);
$periods = $evaluation->getAvailablePeriods();
$stats = $evaluation->getDashboardStats($selectedPeriodId);
$recent = $evaluation->getRecentSubmissions($selectedPeriodId, 8);
$instruments = $evaluation->getInstrumentCatalog();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$currentAdmin = Auth::getAdminUser();
include __DIR__ . '/../templates/admin_header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Resumen institucional</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Dashboard del diagnóstico de comunicación</h1>
        <p class="mt-3 text-slate-600">Control del período, participantes, instrumentos cargados y registros recientes.</p>
    </div>
    <form method="get" class="rounded-2xl bg-white border border-slate-200 shadow-sm px-4 py-3">
        <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Cambiar período</label>
        <div class="flex items-center gap-3">
            <select name="periodo" class="rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                <?php foreach ($periods as $periodOption): ?>
                <option value="<?= (int) $periodOption['serial_per'] ?>" <?= (int) $periodOption['serial_per'] === $selectedPeriodId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($periodOption['nombre_per']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="rounded-xl bg-primary px-4 py-2 text-white text-sm font-bold">Ver</button>
        </div>
    </form>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-6 gap-4 mb-8">
    <?php
    $cards = [
        ['label' => 'Participantes', 'value' => $stats['participantes'], 'class' => 'text-slate-900'],
        ['label' => 'Internos', 'value' => $stats['internos'], 'class' => 'text-blue-700'],
        ['label' => 'Externos', 'value' => $stats['externos'], 'class' => 'text-violet-700'],
        ['label' => 'Instrumentos', 'value' => $stats['instrumentos'], 'class' => 'text-emerald-700'],
        ['label' => 'Evaluaciones', 'value' => $stats['evaluaciones'], 'class' => 'text-amber-700'],
        ['label' => 'Enviadas', 'value' => $stats['enviadas'], 'class' => 'text-primary'],
    ];
    foreach ($cards as $card):
    ?>
    <div class="rounded-[1.75rem] bg-white border border-slate-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-slate-500"><?= $card['label'] ?></p>
        <p class="mt-3 text-3xl font-extrabold <?= $card['class'] ?>"><?= $card['value'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-[1.05fr,0.95fr] gap-6 mb-8">
    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Período configurado</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900"><?= htmlspecialchars($period['nombre_per'] ?? '') ?></h2>
            </div>
            <span class="inline-flex rounded-full px-4 py-2 text-xs font-extrabold <?= ($period['estado_cfg'] ?? 'BORRADOR') === 'ACTIVO' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                <?= htmlspecialchars($period['estado_cfg'] ?? 'BORRADOR') ?>
            </span>
        </div>
        <div class="mt-6 grid md:grid-cols-2 gap-4">
            <div class="rounded-3xl bg-slate-50 border border-slate-200 p-5">
                <p class="text-xs uppercase tracking-wide font-bold text-slate-500">Levantamiento</p>
                <p class="mt-3 text-sm text-slate-700"><?= format_datetime($period['fecha_inicio_diagnostico'] ?? null) ?> a <?= format_datetime($period['fecha_fin_diagnostico'] ?? null) ?></p>
            </div>
            <div class="rounded-3xl bg-slate-50 border border-slate-200 p-5">
                <p class="text-xs uppercase tracking-wide font-bold text-slate-500">Revisión</p>
                <p class="mt-3 text-sm text-slate-700"><?= format_datetime($period['fecha_inicio_revision'] ?? null) ?> a <?= format_datetime($period['fecha_fin_revision'] ?? null) ?></p>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="personal.php?periodo=<?= $selectedPeriodId ?>" class="inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-white font-extrabold shadow-lg shadow-primary/20 hover:bg-primary/90 transition">Gestionar participantes</a>
            <a href="captura.php?periodo=<?= $selectedPeriodId ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 font-bold text-slate-700 hover:bg-slate-100 transition">Capturar instrumentos</a>
        </div>
    </section>

    <section class="rounded-[2rem] bg-slate-900 text-white shadow-xl p-8">
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-accent">Sincronización interna</p>
        <h2 class="mt-2 text-2xl font-extrabold">Participantes internos actualizados</h2>
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="rounded-3xl bg-white/10 p-5">
                <p class="text-xs uppercase tracking-wide font-bold text-white/60">Activos leídos</p>
                <p class="mt-3 text-4xl font-extrabold"><?= (int) $sync['total_activos'] ?></p>
            </div>
            <div class="rounded-3xl bg-white/10 p-5">
                <p class="text-xs uppercase tracking-wide font-bold text-white/60">Creados</p>
                <p class="mt-3 text-4xl font-extrabold"><?= (int) $sync['creados'] ?></p>
            </div>
        </div>
        <p class="mt-6 text-sm text-white/80">Los participantes internos se sincronizan contra la tabla <code>empleado</code> y pueden convivir con participantes externos e institucionales.</p>
    </section>
</div>

<div class="grid lg:grid-cols-[0.95fr,1.05fr] gap-6">
    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100">
            <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Instrumentos base</p>
            <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Catálogo cargado desde la guía</h2>
        </div>
        <div class="divide-y divide-slate-100">
            <?php foreach ($instruments as $instrument): ?>
            <div class="px-8 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-lg font-extrabold text-slate-900"><?= htmlspecialchars($instrument['nombre_ins']) ?></p>
                        <p class="mt-1 text-sm text-slate-500"><?= htmlspecialchars($instrument['descripcion_ins'] ?? '') ?></p>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                            <span class="rounded-full bg-primary/10 px-3 py-1 text-primary"><?= htmlspecialchars($evaluation->audienceLabel($instrument['audiencia_ins'] ?? '')) ?></span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700"><?= (int) $instrument['total_secciones'] ?> secciones</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700"><?= (int) $instrument['total_preguntas'] ?> preguntas</span>
                        </div>
                    </div>
                    <a href="captura.php?periodo=<?= $selectedPeriodId ?>&instrumento=<?= (int) $instrument['serial_ins'] ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                        Abrir
                    </a>
                </div>
                <?php if (!empty($instrument['procesos'])): ?>
                <p class="mt-3 text-xs text-slate-500">
                    Procesos:
                    <?php
                    $labels = array_map(static fn(array $process): string => $process['codigo_proceso'] . ' ' . $process['nombre_proceso'], $instrument['procesos']);
                    echo htmlspecialchars(implode(' | ', $labels));
                    ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Actividad reciente</p>
                <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Últimos registros</h2>
            </div>
            <a href="resultados.php?periodo=<?= $selectedPeriodId ?>" class="text-sm font-bold text-primary hover:underline">Ver reporte completo</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Instrumento</th>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Participante</th>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Estado</th>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Puntaje</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($recent as $row): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-900"><?= htmlspecialchars($row['nombre_ins']) ?></p>
                            <p class="text-sm text-slate-500"><?= htmlspecialchars($evaluation->audienceLabel($row['audiencia_ins'] ?? '')) ?></p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            <?= htmlspecialchars($evaluation->participantDisplayName($row)) ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold <?= ($row['estado_eva'] ?? '') === 'ENVIADA' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                                <?= htmlspecialchars($row['estado_eva']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700"><?= format_score(isset($row['puntaje_final']) ? (float) $row['puntaje_final'] : null, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
