<?php
$pageTitle = $pageTitle ?? APP_NAME;
$currentEmployee = $currentEmployee ?? Auth::getEmployeeUser();
$flash = pull_flash();
$publicNavigationMode = $publicNavigationMode ?? ($currentEmployee ? 'employee' : 'brand');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= APP_NAME ?></title>
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
<body class="bg-gradient-to-br from-slate-50 via-white to-blue-50 min-h-screen font-sans">
    <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/90 shadow-sm backdrop-blur-sm">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-4">
                <img src="<?= INSTITUTION_LOGO ?>" alt="UECR" class="h-11 w-11 rounded-full bg-white shadow">
                <div class="min-w-0">
                    <p class="truncate text-lg font-extrabold text-primary"><?= APP_NAME ?></p>
                    <p class="truncate text-xs text-gray-500">Diagnóstico de comunicación institucional</p>
                </div>
            </div>
            <?php if ($publicNavigationMode !== 'none'): ?>
            <div class="flex items-center gap-3">
                <?php if ($publicNavigationMode === 'employee' && $currentEmployee): ?>
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($currentEmployee['name'] ?? '') ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($currentEmployee['cedula'] ?? '') ?></p>
                </div>
                <a href="dashboard.php" class="rounded-lg px-4 py-2 text-sm font-bold text-primary transition hover:bg-primary/5">Panel</a>
                <a href="logout.php" class="rounded-lg px-4 py-2 text-sm font-bold text-red-600 transition hover:bg-red-50">Salir</a>
                <?php elseif ($publicNavigationMode === 'brand'): ?>
                <a href="index.php" class="rounded-lg px-4 py-2 text-sm font-bold text-primary transition hover:bg-primary/5">Inicio</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <?php if ($flash): ?>
        <?php
        $flashIsSuccess = ($flash['type'] ?? '') === 'success';
        $flashTitle = $flashIsSuccess ? 'Operación completada' : 'Revise la información';
        $flashClasses = $flashIsSuccess
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
            : 'border-red-200 bg-red-50 text-red-800';
        ?>
        <div class="mb-6 rounded-[2rem] border px-6 py-5 <?= $flashClasses ?>">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em]"><?= $flashTitle ?></p>
            <p class="mt-2 text-sm leading-6"><?= htmlspecialchars($flash['message']) ?></p>
        </div>
        <?php endif; ?>
