<?php
$pageTitle = $pageTitle ?? APP_NAME;
$activeNav = $activeNav ?? '';
$currentAdmin = $currentAdmin ?? Auth::getAdminUser();
$period = $period ?? null;
$flash = pull_flash();
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
<body class="bg-gray-50 min-h-screen font-sans">
    <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-6">
                <div class="flex items-center gap-4 min-w-0">
                    <img src="<?= INSTITUTION_LOGO ?>" alt="UECR" class="h-10 w-10 rounded-full bg-white shadow">
                    <div class="min-w-0">
                        <p class="text-lg font-extrabold text-primary truncate"><?= APP_SHORT_NAME ?></p>
                        <p class="text-xs text-gray-500 truncate">Panel Administrativo</p>
                    </div>
                </div>
                <div class="hidden lg:flex items-center gap-1">
                    <?php
                    $links = [
                        'dashboard' => ['label' => 'Dashboard', 'href' => 'index.php'],
                        'personal' => ['label' => 'Participantes', 'href' => 'personal.php'],
                        'captura' => ['label' => 'Captura', 'href' => 'captura.php'],
                        'config' => ['label' => 'Configuracion', 'href' => 'configuracion.php'],
                        'resultados' => ['label' => 'Reportes', 'href' => 'resultados.php'],
                    ];
                    foreach ($links as $key => $link):
                        $isActive = $activeNav === $key;
                    ?>
                    <a href="<?= $link['href'] ?>" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $isActive ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-100' ?>">
                        <?= $link['label'] ?>
                    </a>
                    <?php endforeach; ?>
                    <?php if (($currentAdmin['role'] ?? '') === 'SUPERADMIN'): ?>
                    <a href="usuarios.php" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $activeNav === 'usuarios' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-100' ?>">
                        Usuarios
                    </a>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3">
                    <?php if ($period): ?>
                    <div class="hidden md:block px-3 py-2 bg-primary/5 rounded-xl border border-primary/10">
                        <p class="text-[11px] uppercase tracking-wide font-bold text-primary">Período</p>
                        <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($period['nombre_per'] ?? '') ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($currentAdmin['name'] ?? '') ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($currentAdmin['role'] ?? '') ?></p>
                    </div>
                    <a href="logout.php" class="inline-flex items-center justify-center h-10 w-10 rounded-full border border-gray-200 text-gray-500 hover:text-red-600 hover:border-red-200 transition" title="Cerrar sesión">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($flash): ?>
        <div class="mb-6 rounded-2xl border px-5 py-4 <?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-red-50 border-red-200 text-red-700' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>
