<?php
require_once __DIR__ . '/../bootstrap.php';

if (Auth::isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';
if (request_method_is('POST')) {
    $username = trim((string) ($_POST['usuario'] ?? ''));
    $password = trim((string) ($_POST['password'] ?? ''));

    if (Auth::loginAdmin($username, $password)) {
        redirect('index.php');
    }

    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - <?= APP_NAME ?></title>
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
<body class="min-h-screen font-sans bg-[radial-gradient(circle_at_top,_rgba(255,204,0,0.16),_transparent_28%),linear-gradient(135deg,_#003399_0%,_#001a4d_55%,_#0b2f88_100%)]">
    <main class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md bg-white/95 backdrop-blur-sm rounded-3xl overflow-hidden shadow-2xl">
            <div class="bg-primary px-8 py-8 text-center text-white">
                <img src="<?= INSTITUTION_LOGO ?>" alt="UECR" class="mx-auto h-20 w-20 rounded-full bg-white p-2 shadow-lg">
                <h1 class="mt-4 text-2xl font-extrabold"><?= APP_NAME ?></h1>
                <p class="mt-2 text-sm text-white/80">Panel de administración</p>
            </div>
            <div class="p-8">
                <?php if ($error): ?>
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                <form method="post" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Usuario</label>
                        <input type="text" name="usuario" required class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Contraseña</label>
                        <input type="password" name="password" required class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:border-primary focus:outline-none">
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-primary py-3.5 text-white font-extrabold shadow-lg shadow-primary/30 hover:bg-primary/90 transition">
                        Ingresar
                    </button>
                </form>
                <div class="mt-6 text-center">
                    <a href="../public/index.php" class="text-sm text-gray-500 hover:text-primary transition">Ir al formulario público</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
