<?php
require_once __DIR__ . '/../bootstrap.php';

Auth::requireAdmin();

$evaluation = new CommunicationEvaluation();
$activeAcademicPeriod = $evaluation->getActiveAcademicPeriod();
$selectedPeriodId = (int) ($_REQUEST['periodo'] ?? ($activeAcademicPeriod['serial_per'] ?? 0));

if ($selectedPeriodId <= 0) {
    throw new RuntimeException('No existe un período disponible.');
}

if (request_method_is('POST')) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        throw new RuntimeException('Token CSRF inválido.');
    }

    try {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'sync_personal') {
            $result = $evaluation->syncActiveEmployees($selectedPeriodId);
            set_flash('success', "Se sincronizaron {$result['creados']} participantes nuevos y {$result['actualizados']} existentes.");
        } elseif ($action === 'save_participant') {
            $participantId = $evaluation->saveParticipant($_POST);
            set_flash('success', "Participante guardado correctamente (#{$participantId}).");
        }
    } catch (Throwable $throwable) {
        set_flash('error', $throwable->getMessage());
    }

    redirect('personal.php?periodo=' . $selectedPeriodId);
}

$evaluation->ensurePeriodConfigured($selectedPeriodId);
$period = $evaluation->getConfiguredPeriod($selectedPeriodId);
$periods = $evaluation->getAvailablePeriods();
$filters = [
    'search' => $_GET['search'] ?? '',
    'tipo' => $_GET['tipo'] ?? '',
    'activo' => $_GET['activo'] ?? '',
];
$participants = $evaluation->getParticipantsForAdmin($selectedPeriodId, $filters);

$pageTitle = 'Participantes';
$activeNav = 'personal';
$currentAdmin = Auth::getAdminUser();
include __DIR__ . '/../templates/admin_header.php';
?>
<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Participantes</p>
        <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Gestión interna y externa del diagnóstico</h1>
        <p class="mt-3 text-slate-600">Los participantes internos se sincronizan desde nómina y los externos se registran manualmente.</p>
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
            <button type="submit" class="rounded-xl bg-primary px-4 py-2 text-white text-sm font-bold">Cambiar</button>
        </div>
    </form>
</div>

<div class="grid xl:grid-cols-[0.95fr,1.05fr] gap-6 mb-8">
    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
        <form method="post" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="periodo" value="<?= $selectedPeriodId ?>">
            <input type="hidden" name="action" value="sync_personal">
            <div class="flex-1 min-w-[220px]">
                <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Sincronización interna</p>
                <p class="mt-2 text-sm text-slate-600">Replica a los colaboradores activos como participantes internos usando la tabla <code>empleado</code>.</p>
            </div>
            <button type="submit" class="rounded-2xl bg-primary px-5 py-3 text-white font-extrabold shadow-lg shadow-primary/20 hover:bg-primary/90 transition">
                Sincronizar participantes internos
            </button>
        </form>
    </section>
    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
        <form method="get" class="grid md:grid-cols-[1fr,0.8fr,0.8fr,auto] gap-3 items-end">
            <input type="hidden" name="periodo" value="<?= $selectedPeriodId ?>">
            <div>
                <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Buscar</label>
                <input type="text" name="search" value="<?= htmlspecialchars((string) $filters['search']) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Nombre, correo, organización">
            </div>
            <div>
                <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Tipo</label>
                <select name="tipo" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <option value="">Todos</option>
                    <option value="INTERNO" <?= ($filters['tipo'] ?? '') === 'INTERNO' ? 'selected' : '' ?>>Interno</option>
                    <option value="EXTERNO" <?= ($filters['tipo'] ?? '') === 'EXTERNO' ? 'selected' : '' ?>>Externo</option>
                    <option value="MIXTO" <?= ($filters['tipo'] ?? '') === 'MIXTO' ? 'selected' : '' ?>>Mixto</option>
                </select>
            </div>
            <div>
                <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Estado</label>
                <select name="activo" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <option value="">Todos</option>
                    <option value="SI" <?= ($filters['activo'] ?? '') === 'SI' ? 'selected' : '' ?>>Activo</option>
                    <option value="NO" <?= ($filters['activo'] ?? '') === 'NO' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-white text-sm font-bold hover:bg-slate-800 transition">Filtrar</button>
        </form>
    </section>
</div>

<section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-8 mb-8">
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Nuevo participante</p>
        <h2 class="mt-2 text-2xl font-extrabold text-slate-900">Registro manual de actores externos o mixtos</h2>
    </div>
    <form method="post" class="grid lg:grid-cols-3 gap-4">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="periodo" value="<?= $selectedPeriodId ?>">
        <input type="hidden" name="action" value="save_participant">
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Tipo</label>
            <select name="tipo_participante" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                <option value="EXTERNO">Externo</option>
                <option value="MIXTO">Mixto</option>
                <option value="INTERNO">Interno</option>
            </select>
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Nombres</label>
            <input type="text" name="nombres_par" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Apellidos</label>
            <input type="text" name="apellidos_par" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Cargo</label>
            <input type="text" name="cargo_par" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Organización</label>
            <input type="text" name="organizacion_par" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Organización">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Público o grupo</label>
            <input type="text" name="publico_par" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Público o grupo">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Correo</label>
            <input type="email" name="email_par" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Teléfono</label>
            <input type="text" name="telefono_par" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Teléfono">
        </div>
        <div>
            <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Estado</label>
            <select name="activo_par" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                <option value="SI">SI</option>
                <option value="NO">NO</option>
            </select>
        </div>
        <div class="lg:col-span-3">
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 text-white font-extrabold hover:bg-primary/90 transition">
                Guardar participante
            </button>
        </div>
    </form>
</section>

<section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100">
        <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Listado</p>
        <h2 class="mt-2 text-2xl font-extrabold text-slate-900"><?= count($participants) ?> participantes</h2>
    </div>
    <div class="divide-y divide-slate-100">
        <?php foreach ($participants as $participant): ?>
        <div class="px-6 py-6">
            <form method="post" class="grid xl:grid-cols-[0.9fr,0.9fr,0.9fr,0.8fr,0.8fr,auto] gap-3 items-end">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="periodo" value="<?= $selectedPeriodId ?>">
                <input type="hidden" name="action" value="save_participant">
                <input type="hidden" name="serial_par" value="<?= (int) $participant['serial_par'] ?>">
                <input type="hidden" name="serial_epl" value="<?= htmlspecialchars((string) ($participant['serial_epl'] ?? '')) ?>">
                <div>
                    <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Participante</label>
                    <input type="text" name="nombres_par" value="<?= htmlspecialchars($participant['nombres_par']) ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <input type="text" name="apellidos_par" value="<?= htmlspecialchars($participant['apellidos_par'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Apellidos">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Tipo y estado</label>
                    <select name="tipo_participante" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                        <option value="INTERNO" <?= ($participant['tipo_participante'] ?? '') === 'INTERNO' ? 'selected' : '' ?>>Interno</option>
                        <option value="EXTERNO" <?= ($participant['tipo_participante'] ?? '') === 'EXTERNO' ? 'selected' : '' ?>>Externo</option>
                        <option value="MIXTO" <?= ($participant['tipo_participante'] ?? '') === 'MIXTO' ? 'selected' : '' ?>>Mixto</option>
                    </select>
                    <select name="activo_par" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                        <option value="SI" <?= ($participant['activo_par'] ?? 'SI') === 'SI' ? 'selected' : '' ?>>Activo</option>
                        <option value="NO" <?= ($participant['activo_par'] ?? '') === 'NO' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Organización y público</label>
                    <input type="text" name="organizacion_par" value="<?= htmlspecialchars($participant['organizacion_par'] ?? '') ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Organización">
                    <input type="text" name="publico_par" value="<?= htmlspecialchars($participant['publico_par'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Público o grupo">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Cargo</label>
                    <input type="text" name="cargo_par" value="<?= htmlspecialchars($participant['cargo_par'] ?? '') ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <p class="mt-2 text-xs text-slate-500">Evaluaciones: <?= (int) ($participant['total_evaluaciones'] ?? 0) ?></p>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide font-bold text-slate-500 mb-2">Contacto</label>
                    <input type="email" name="email_par" value="<?= htmlspecialchars($participant['email_par'] ?? '') ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Correo">
                    <input type="text" name="telefono_par" value="<?= htmlspecialchars($participant['telefono_par'] ?? '') ?>" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none" placeholder="Teléfono">
                </div>
                <div class="flex flex-col gap-3">
                    <button type="submit" class="rounded-2xl bg-slate-900 px-4 py-3 text-white text-sm font-extrabold hover:bg-slate-800 transition">
                        Guardar
                    </button>
                    <a href="captura.php?periodo=<?= $selectedPeriodId ?>&participante=<?= (int) $participant['serial_par'] ?>" class="rounded-2xl border border-slate-300 px-4 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                        Capturar
                    </a>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
