<?php
require_once __DIR__ . '/../bootstrap.php';

if (Auth::isEmployeeLoggedIn()) {
    redirect('dashboard.php');
}

$evaluation = new CommunicationEvaluation();
$activePeriod = $evaluation->getActiveAcademicPeriod();
$error = '';

if (request_method_is('POST')) {
    $cedula = (string) ($_POST['cedula'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (Auth::loginEmployee($cedula, $password)) {
        redirect('dashboard.php');
    }

    $error = 'No fue posible iniciar sesion. Use su cedula como usuario y contrasena.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso interno - <?= APP_NAME ?></title>
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
    <main class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="grid w-full max-w-6xl items-center gap-8 lg:grid-cols-[1.1fr,0.9fr]">
            <section class="hidden text-gray-800 lg:block">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-3 rounded-full bg-primary/10 px-4 py-2 text-sm font-bold text-primary">
                        <img src="<?= INSTITUTION_LOGO ?>" alt="UECR" class="h-8 w-8 rounded-full">
                        <?= htmlspecialchars(INSTITUTION_NAME) ?>
                    </div>
                    <h1 class="mt-6 text-5xl font-extrabold leading-tight text-slate-900">Ingreso del personal interno al diagnostico.</h1>
                    <p class="mt-5 text-lg text-slate-600">
                        Este enlace queda reservado para directivos y personal propio. El sistema identifica automaticamente al colaborador activo y abre su instrumento interno.
                    </p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Acceso</p>
                            <p class="mt-3 text-sm text-slate-700">Usuario: cedula del colaborador. Contrasena: la misma cedula.</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary">Periodo</p>
                            <p class="mt-3 text-sm text-slate-700"><?= htmlspecialchars($activePeriod['nombre_per'] ?? 'Sin periodo activo') ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white/95 shadow-2xl backdrop-blur-sm">
                <div class="bg-primary px-8 py-8 text-center text-white">
                    <img src="<?= INSTITUTION_LOGO ?>" alt="UECR" class="mx-auto h-20 w-20 rounded-full bg-white p-2 shadow-lg">
                    <h2 class="mt-4 text-3xl font-extrabold"><?= APP_NAME ?></h2>
                    <p class="mt-2 text-sm text-white/80">Ingreso del personal interno</p>
                </div>
                <div class="p-8">
                    <?php if ($error): ?>
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" class="space-y-5">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Cedula</label>
                            <input type="text" name="cedula" maxlength="15" required class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:border-primary focus:outline-none" placeholder="Ingrese su cedula">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-gray-700">Contrasena</label>
                            <input type="password" name="password" maxlength="15" required class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:border-primary focus:outline-none" placeholder="Use su misma cedula">
                        </div>
                        <button type="submit" class="w-full rounded-2xl bg-primary py-3.5 font-extrabold text-white shadow-lg shadow-primary/30 transition hover:bg-primary/90">
                            Ingresar al sistema
                        </button>
                    </form>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-bold text-slate-700">Enlace de distribucion interna</p>
                        <p class="mt-2 text-sm text-slate-600"><?= htmlspecialchars(public_url('interno.php')) ?></p>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-4 text-sm">
                        <a href="index.php" class="font-bold text-gray-500 transition hover:text-primary">Volver al inicio</a>
                        <a href="../admin/login.php" class="font-bold text-gray-500 transition hover:text-primary">Panel administrativo</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
