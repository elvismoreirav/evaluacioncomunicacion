<?php
require_once __DIR__ . '/../bootstrap.php';

$evaluation = new CommunicationEvaluation();
$activePeriod = $evaluation->getActiveAcademicPeriod();
$configuredPeriod = null;
$windowOpen = false;
$flash = pull_flash();
$employeeUser = Auth::getEmployeeUser();

if ($activePeriod) {
    $evaluation->ensurePeriodConfigured((int) $activePeriod['serial_per']);
    $configuredPeriod = $evaluation->getConfiguredPeriod((int) $activePeriod['serial_per']);

    if ($configuredPeriod && ($configuredPeriod['estado_cfg'] ?? '') === 'ACTIVO') {
        $now = time();
        $start = !empty($configuredPeriod['fecha_inicio_diagnostico']) ? strtotime((string) $configuredPeriod['fecha_inicio_diagnostico']) : false;
        $end = !empty($configuredPeriod['fecha_fin_diagnostico']) ? strtotime((string) $configuredPeriod['fecha_fin_diagnostico']) : false;

        $windowOpen = ($start === false || $start <= $now) && ($end === false || $end >= $now);
    }
}

$instrumentCodes = [
    CommunicationEvaluation::INSTRUMENT_INTERNAL,
    CommunicationEvaluation::INSTRUMENT_EXTERNAL,
];
$instruments = [];

foreach ($instrumentCodes as $instrumentCode) {
    $instrument = $evaluation->getInstrumentByCode($instrumentCode);
    if ($instrument) {
        $instruments[$instrumentCode] = $instrument;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="icon" href="<?= INSTITUTION_LOGO ?>" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '<?= PRIMARY_COLOR ?>',
                        accent: '<?= ACCENT_COLOR ?>'
                    },
                    fontFamily: {
                        sans: ['Nunito', 'ui-sans-serif', 'system-ui']
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen font-sans bg-[radial-gradient(circle_at_top_left,_rgba(255,204,0,0.18),_transparent_32%),linear-gradient(145deg,_#eff6ff_0%,_#ffffff_40%,_#f8fafc_100%)]">
    <main class="px-4 py-8 lg:py-12">
        <div class="mx-auto max-w-6xl">
            <section class="overflow-hidden rounded-[2.5rem] border border-slate-100 bg-white/90 shadow-2xl backdrop-blur-sm">
                <div class="grid gap-8 lg:grid-cols-[1.15fr,0.85fr]">
                    <div class="px-8 py-10 lg:px-10 lg:py-12">
                        <div class="inline-flex items-center gap-3 rounded-full bg-primary/10 px-4 py-2 text-sm font-bold text-primary">
                            <img src="<?= INSTITUTION_LOGO ?>" alt="UECR" class="h-8 w-8 rounded-full">
                            <?= htmlspecialchars(INSTITUTION_NAME) ?>
                        </div>
                        <h1 class="mt-6 max-w-3xl text-4xl font-extrabold leading-tight text-slate-900 lg:text-5xl">
                            Acceso al diagnostico de comunicacion por categoria de participante.
                        </h1>
                        <p class="mt-5 max-w-2xl text-lg text-slate-600">
                            El esquema queda separado por tipo de actor: personal propio con ingreso autenticado, publicos externos o mixtos con formulario abierto, y levantamientos institucionales desde administracion.
                        </p>
                        <div class="mt-8 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Periodo activo</p>
                                <p class="mt-3 text-sm text-slate-700"><?= htmlspecialchars($activePeriod['nombre_per'] ?? 'Sin periodo activo') ?></p>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Estado</p>
                                <p class="mt-3 text-sm text-slate-700">
                                    <?= $windowOpen ? 'Captura habilitada' : (($configuredPeriod['estado_cfg'] ?? '') === 'ACTIVO' ? 'Fuera de ventana' : 'Pendiente de habilitacion') ?>
                                </p>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Criterio</p>
                                <p class="mt-3 text-sm text-slate-700">Se incluyen perspectivas internas, externas y participacion equilibrada.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-primary px-8 py-10 text-white lg:px-10 lg:py-12">
                        <p class="text-xs font-bold uppercase tracking-[0.26em] text-white/70">Modelo de acceso</p>
                        <h2 class="mt-4 text-3xl font-extrabold">Como se distribuyen los enlaces</h2>
                        <ul class="mt-6 space-y-4 text-sm leading-6 text-white/85">
                            <li><strong>Personal interno:</strong> enlace propio con usuario y contrasena.</li>
                            <li><strong>Organizaciones aliadas, actores externos o mixtos:</strong> enlace abierto sin credenciales.</li>
                            <li><strong>Auditorias institucionales:</strong> captura exclusiva desde administracion.</li>
                        </ul>
                        <div class="mt-8 rounded-3xl border border-white/15 bg-white/10 p-5">
                            <p class="text-sm font-bold">Panel administrativo</p>
                            <p class="mt-2 text-sm text-white/80">Desde administracion se mantienen configuracion de periodo, participantes y los instrumentos institucionales de auditoria.</p>
                            <a href="../admin/login.php" class="mt-4 inline-flex rounded-2xl bg-white px-5 py-3 text-sm font-extrabold text-primary transition hover:bg-white/90">
                                Ir al panel administrativo
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($flash): ?>
            <?php
            $flashIsSuccess = ($flash['type'] ?? '') === 'success';
            $flashTitle = $flashIsSuccess ? 'Operacion completada' : 'Revise la informacion';
            $flashClasses = $flashIsSuccess
                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                : 'border-red-200 bg-red-50 text-red-800';
            ?>
            <div class="mt-8 rounded-[2rem] border px-6 py-5 <?= $flashClasses ?>">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em]"><?= $flashTitle ?></p>
                <p class="mt-2 text-sm leading-6"><?= htmlspecialchars($flash['message']) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!$windowOpen): ?>
            <div class="mt-8 rounded-[2rem] border border-amber-200 bg-amber-50 px-6 py-5 text-amber-900">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em]">Captura no disponible</p>
                <p class="mt-2 text-sm leading-6">
                    El diagnostico no se encuentra habilitado para envios en este momento. Los accesos permanecen visibles, pero la captura depende de la ventana configurada en el periodo activo.
                </p>
            </div>
            <?php endif; ?>

            <section class="mt-8 grid gap-6 lg:grid-cols-3">
                <article class="flex h-full flex-col rounded-[2rem] border border-slate-100 bg-white p-8 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.24em] text-primary">Acceso interno</p>
                            <h2 class="mt-3 text-2xl font-extrabold text-slate-900">Directivos y personal</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Login</span>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        Utiliza el instrumento interno y reconoce al colaborador desde nomina. Usuario y contrasena: la cedula del colaborador activo.
                    </p>
                    <?php if (isset($instruments[CommunicationEvaluation::INSTRUMENT_INTERNAL])): ?>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Secciones</p>
                            <p class="mt-2 text-lg font-extrabold text-slate-900"><?= (int) ($instruments[CommunicationEvaluation::INSTRUMENT_INTERNAL]['total_secciones'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Preguntas</p>
                            <p class="mt-2 text-lg font-extrabold text-slate-900"><?= (int) ($instruments[CommunicationEvaluation::INSTRUMENT_INTERNAL]['total_preguntas'] ?? 0) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="mt-8">
                        <a href="<?= Auth::isEmployeeLoggedIn() ? 'dashboard.php' : 'interno.php' ?>" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 text-white font-extrabold shadow-lg shadow-primary/20 transition hover:bg-primary/90">
                            <?= $employeeUser ? 'Ir a mi panel' : 'Ingresar como personal interno' ?>
                        </a>
                    </div>
                </article>

                <article class="flex h-full flex-col rounded-[2rem] border border-slate-100 bg-white p-8 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.24em] text-primary">Acceso externo</p>
                            <h2 class="mt-3 text-2xl font-extrabold text-slate-900">Aliados, externos y mixtos</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Abierto</span>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        Este enlace sirve para organizaciones aliadas, publicos externos y actores mixtos. El formulario solicita la categoria del participante y sus datos de representacion.
                    </p>
                    <?php if (isset($instruments[CommunicationEvaluation::INSTRUMENT_EXTERNAL])): ?>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Secciones</p>
                            <p class="mt-2 text-lg font-extrabold text-slate-900"><?= (int) ($instruments[CommunicationEvaluation::INSTRUMENT_EXTERNAL]['total_secciones'] ?? 0) ?></p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Preguntas</p>
                            <p class="mt-2 text-lg font-extrabold text-slate-900"><?= (int) ($instruments[CommunicationEvaluation::INSTRUMENT_EXTERNAL]['total_preguntas'] ?? 0) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="mt-8">
                        <?php if ($windowOpen): ?>
                        <a href="externo.php" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 text-white font-extrabold shadow-lg shadow-primary/20 transition hover:bg-primary/90">
                            Abrir formulario externo
                        </a>
                        <?php else: ?>
                        <span class="inline-flex items-center justify-center rounded-2xl bg-slate-200 px-6 py-3 font-extrabold text-slate-500">
                            No disponible
                        </span>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="flex h-full flex-col rounded-[2rem] border border-slate-100 bg-white p-8 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.24em] text-primary">Acceso institucional</p>
                            <h2 class="mt-3 text-2xl font-extrabold text-slate-900">Auditorias y matrices</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Admin</span>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        Estructura organizacional, mapeo de publicos, auditoria de herramientas, archivos mediaticos e instrumentos institucionales se levantan desde el panel administrativo.
                    </p>
                    <div class="mt-6 rounded-2xl bg-slate-50 px-4 py-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Uso recomendado</p>
                        <p class="mt-2 text-sm text-slate-700">Equipo de comunicacion, rectorado o personal designado para consolidar evidencias institucionales.</p>
                    </div>
                    <div class="mt-8">
                        <a href="../admin/login.php" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-6 py-3 font-extrabold text-slate-700 transition hover:bg-slate-100">
                            Ingresar a administracion
                        </a>
                    </div>
                </article>
            </section>
        </div>
    </main>
</body>
</html>
