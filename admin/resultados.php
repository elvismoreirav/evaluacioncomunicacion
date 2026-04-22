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
$period = $evaluation->getConfiguredPeriod($selectedPeriodId);
$periods = $evaluation->getAvailablePeriods();
$instruments = $evaluation->getInstrumentCatalog();
$filters = [
    'search' => $_GET['search'] ?? '',
    'instrumento' => $_GET['instrumento'] ?? '',
    'estado' => $_GET['estado'] ?? '',
    'audiencia' => $_GET['audiencia'] ?? '',
];
$results = $evaluation->getResultsForAdmin($selectedPeriodId, $filters);

$pageTitle = 'Reportes';
$activeNav = 'resultados';
$currentAdmin = Auth::getAdminUser();
include __DIR__ . '/../templates/admin_header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Reporte consolidado</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Resultados del diagnóstico</h1>
        <p class="mt-3 text-slate-600">Consulta las respuestas por instrumento, audiencia, estado y participante.</p>
    </div>
    <form method="get" class="rounded-2xl bg-white border border-slate-200 shadow-sm px-4 py-3">
        <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Período</label>
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

<section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6 mb-8">
    <form method="get" class="grid lg:grid-cols-[1fr,1fr,0.8fr,0.8fr,auto] gap-3 items-end">
        <input type="hidden" name="periodo" value="<?= $selectedPeriodId ?>">
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Buscar</label>
            <input type="text" name="search" value="<?= htmlspecialchars((string) $filters['search']) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Instrumento, participante, organización">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Instrumento</label>
            <select name="instrumento" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                <option value="">Todos</option>
                <?php foreach ($instruments as $instrument): ?>
                <?php $instrumentId = (int) ($instrument['serial_ins'] ?? 0); ?>
                <?php if ($instrumentId <= 0) { continue; } ?>
                <option value="<?= $instrumentId ?>" <?= (int) ($filters['instrumento'] ?? 0) === $instrumentId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($instrument['nombre_ins']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Estado</label>
            <select name="estado" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                <option value="">Todos</option>
                <?php foreach (['BORRADOR', 'ENVIADA', 'REVISADA', 'CERRADA'] as $status): ?>
                <option value="<?= $status ?>" <?= ($filters['estado'] ?? '') === $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Audiencia</label>
            <select name="audiencia" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                <option value="">Todas</option>
                <?php foreach (['INTERNA', 'EXTERNA', 'MIXTA', 'INSTITUCIONAL'] as $audience): ?>
                <option value="<?= $audience ?>" <?= ($filters['audiencia'] ?? '') === $audience ? 'selected' : '' ?>><?= htmlspecialchars($evaluation->audienceLabel($audience)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-white text-sm font-bold hover:bg-slate-800 transition">Filtrar</button>
    </form>
</section>

<section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100">
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Período seleccionado</p>
        <h2 class="mt-2 text-2xl font-extrabold text-slate-900"><?= htmlspecialchars($period['nombre_per'] ?? '') ?></h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Instrumento</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Participante</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Tipo</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Estado</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Puntaje</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Fecha</th>
                    <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                <?php foreach ($results as $row): ?>
                <?php
                $captureHref = 'captura.php?periodo=' . $selectedPeriodId . '&instrumento=' . (int) $row['serial_ins'];
                if (!empty($row['serial_par'])) {
                    $captureHref .= '&participante=' . (int) $row['serial_par'];
                }
                ?>
                <tr>
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-900"><?= htmlspecialchars($row['nombre_ins']) ?></p>
                        <p class="text-sm text-slate-500"><?= htmlspecialchars($evaluation->audienceLabel($row['audiencia_ins'] ?? '')) ?></p>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-700">
                        <p><?= htmlspecialchars($evaluation->participantDisplayName($row)) ?></p>
                        <?php if (!empty($row['organizacion_par'])): ?>
                        <p class="text-xs text-slate-500"><?= htmlspecialchars($row['organizacion_par']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-700">
                        <?= htmlspecialchars(!empty($row['serial_par']) ? $evaluation->participantTypeLabel($row['tipo_participante'] ?? '') : 'Institucional') ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold <?= ($row['estado_eva'] ?? '') === 'ENVIADA' ? 'bg-emerald-100 text-emerald-700' : (($row['estado_eva'] ?? '') === 'BORRADOR' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') ?>">
                            <?= htmlspecialchars($row['estado_eva']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-700"><?= format_score(isset($row['puntaje_final']) ? (float) $row['puntaje_final'] : null, 2) ?></td>
                    <td class="px-6 py-4 text-sm text-slate-700"><?= format_datetime($row['fecha_envio'] ?? $row['fecha_inicio'] ?? null) ?></td>
                    <td class="px-6 py-4">
                        <a href="<?= htmlspecialchars($captureHref) ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                            Abrir
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
