<?php
require_once __DIR__ . '/../bootstrap.php';

if (Auth::isEmployeeLoggedIn()) {
    redirect('dashboard.php');
}

$evaluation = new CommunicationEvaluation();
$activePeriod = $evaluation->getActiveAcademicPeriod();
$configuredPeriod = null;
$windowOpen = false;
$flash = pull_flash();
$externalInstrument = $evaluation->getInstrumentByCode(CommunicationEvaluation::INSTRUMENT_EXTERNAL);

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

$windowLabel = $windowOpen ? 'Formulario habilitado' : 'Fuera de ventana';
$windowClasses = $windowOpen
    ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
    : 'border-amber-200 bg-amber-50 text-amber-900';
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
<body class="min-h-screen font-sans bg-[radial-gradient(circle_at_top_left,_rgba(255,204,0,0.16),_transparent_28%),linear-gradient(145deg,_#eff6ff_0%,_#ffffff_42%,_#f8fafc_100%)]">
    <main class="px-4 py-8 lg:py-12">
        <div class="mx-auto max-w-5xl">
            <section class="overflow-hidden rounded-[2.5rem] border border-slate-100 bg-white/95 shadow-2xl backdrop-blur-sm">
                <div class="grid gap-8 lg:grid-cols-[1.1fr,0.9fr]">
                    <div class="px-8 py-10 lg:px-10 lg:py-12">
                        <div class="inline-flex items-center gap-3 rounded-full bg-primary/10 px-4 py-2 text-sm font-bold text-primary">
                            <img src="<?= INSTITUTION_LOGO ?>" alt="UECR" class="h-8 w-8 rounded-full">
                            <?= htmlspecialchars(INSTITUTION_NAME) ?>
                        </div>
                        <p class="mt-6 text-xs font-extrabold uppercase tracking-[0.24em] text-primary">Acceso externo</p>
                        <h1 class="mt-3 max-w-3xl text-4xl font-extrabold leading-tight text-slate-900 lg:text-5xl">
                            Formulario externo del diagnóstico de comunicación.
                        </h1>
                        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                            Este enlace está destinado a organizaciones aliadas, actores externos y participantes mixtos. Desde aquí se accede únicamente al formulario externo.
                        </p>

                        <div class="mt-8 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Periodo</p>
                                <p class="mt-3 text-sm text-slate-700"><?= htmlspecialchars($activePeriod['nombre_per'] ?? 'Sin periodo activo') ?></p>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Estado</p>
                                <p class="mt-3 text-sm text-slate-700"><?= htmlspecialchars($configuredPeriod['estado_cfg'] ?? 'BORRADOR') ?></p>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Modalidad</p>
                                <p class="mt-3 text-sm text-slate-700">Participación externa y mixta</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-primary px-8 py-10 text-white lg:px-10 lg:py-12">
                        <p class="text-xs font-bold uppercase tracking-[0.26em] text-white/70">Ingreso</p>
                        <h2 class="mt-4 text-3xl font-extrabold">Antes de comenzar</h2>
                        <ul class="mt-6 space-y-4 text-sm leading-6 text-white/85">
                            <li>Complete el formulario en un solo momento si es posible.</li>
                            <li>Tenga a mano sus datos de representación, cargo y organización.</li>
                            <li>Use este acceso solo si fue invitado como participante externo o mixto.</li>
                        </ul>

                        <div class="mt-8 rounded-3xl border border-white/15 bg-white/10 p-5">
                            <p class="text-sm font-bold">Instrumento disponible</p>
                            <p class="mt-2 text-sm text-white/80">
                                <?= htmlspecialchars($externalInstrument['nombre_ins'] ?? 'Formulario externo') ?>
                            </p>
                            <?php if (!empty($externalInstrument['descripcion_ins'])): ?>
                            <p class="mt-2 text-sm text-white/70"><?= htmlspecialchars($externalInstrument['descripcion_ins']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <?php if ($flash): ?>
            <?php
            $flashIsSuccess = ($flash['type'] ?? '') === 'success';
            $flashTitle = $flashIsSuccess ? 'Operación completada' : 'Revise la información';
            $flashClasses = $flashIsSuccess
                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                : 'border-red-200 bg-red-50 text-red-800';
            ?>
            <div class="mt-8 rounded-[2rem] border px-6 py-5 <?= $flashClasses ?>">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em]"><?= $flashTitle ?></p>
                <p class="mt-2 text-sm leading-6"><?= htmlspecialchars($flash['message']) ?></p>
            </div>
            <?php endif; ?>

            <section class="mt-8 grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
                <article class="rounded-[2rem] border border-slate-100 bg-white p-8 shadow-sm">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-primary">Participación externa</p>
                    <h2 class="mt-3 text-2xl font-extrabold text-slate-900">Acceda al formulario correcto</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600">
                        El enlace principal ya no muestra otros accesos. Desde esta página solo se habilita la ruta del formulario externo para evitar confusiones durante la invitación y la respuesta.
                    </p>

                    <div class="mt-6 rounded-3xl border px-5 py-5 <?= $windowClasses ?>">
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em]">Disponibilidad</p>
                        <p class="mt-2 text-lg font-extrabold"><?= $windowLabel ?></p>
                        <p class="mt-2 text-sm leading-6">
                            <?php if ($windowOpen): ?>
                            El periodo se encuentra activo y dentro de la ventana configurada para respuestas.
                            <?php else: ?>
                            La captura permanece cerrada. Si necesita responder hoy, administración debe reabrir la ventana del periodo.
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <?php if ($windowOpen): ?>
                        <a href="externo.php" class="inline-flex items-center justify-center rounded-2xl bg-primary px-6 py-3 font-extrabold text-white shadow-lg shadow-primary/20 transition hover:bg-primary/90">
                            Iniciar formulario externo
                        </a>
                        <?php else: ?>
                        <span class="inline-flex items-center justify-center rounded-2xl bg-slate-200 px-6 py-3 font-extrabold text-slate-500">
                            Formulario no disponible
                        </span>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="rounded-[2rem] border border-slate-100 bg-white p-8 shadow-sm">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-primary">Indicaciones</p>
                    <h2 class="mt-3 text-2xl font-extrabold text-slate-900">Qué esperar del proceso</h2>
                    <div class="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                        <p>El formulario se responde por pasos y muestra su avance por secciones para que la experiencia sea más clara.</p>
                        <p>Al final podrá revisar la información antes del envío y agregar observaciones generales si aportan contexto.</p>
                        <p>Si usted pertenece al personal interno o requiere acceso administrativo, utilice el enlace específico que le hayan compartido por separado.</p>
                    </div>
                </article>
            </section>
        </div>
    </main>
</body>
</html>
