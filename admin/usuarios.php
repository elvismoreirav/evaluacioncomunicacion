<?php
require_once __DIR__ . '/../bootstrap.php';

Auth::requireAdmin();

$currentAdmin = Auth::getAdminUser();
if (($currentAdmin['role'] ?? '') !== 'SUPERADMIN') {
    redirect('index.php');
}

$evaluation = new CommunicationEvaluation();

if (request_method_is('POST')) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        throw new RuntimeException('Token CSRF inválido.');
    }

    try {
        $password = (string) ($_POST['password'] ?? '');
        $adminId = Auth::createAdmin([
            'nombre' => (string) ($_POST['nombre'] ?? ''),
            'usuario' => (string) ($_POST['usuario'] ?? ''),
            'email' => (string) ($_POST['email'] ?? ''),
            'password' => $password,
            'rol' => (string) ($_POST['rol'] ?? 'ADMIN'),
            'activo' => (string) ($_POST['activo'] ?? 'SI'),
        ]);

        $email = trim((string) ($_POST['email'] ?? ''));
        if ($email !== '') {
            Mailer::sendAdminCredentials($email, [
                'nombre' => (string) ($_POST['nombre'] ?? ''),
                'usuario' => (string) ($_POST['usuario'] ?? ''),
                'password' => $password,
                'rol' => (string) ($_POST['rol'] ?? 'ADMIN'),
            ]);
        }

        set_flash('success', "Usuario administrativo creado correctamente (#{$adminId}).");
    } catch (Throwable $throwable) {
        set_flash('error', $throwable->getMessage());
    }

    redirect('usuarios.php');
}

$users = $evaluation->getAdminUsers();

$pageTitle = 'Usuarios';
$activeNav = 'usuarios';
include __DIR__ . '/../templates/admin_header.php';
?>
<div class="mb-8">
    <p class="text-xs uppercase tracking-[0.24em] font-extrabold text-primary">Accesos administrativos</p>
    <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Gestión de usuarios del panel</h1>
    <p class="mt-3 text-slate-600">Administración de cuentas con acceso al módulo de diagnóstico de comunicación.</p>
</div>

<div class="grid xl:grid-cols-[0.9fr,1.1fr] gap-6">
    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-8">
        <h2 class="text-2xl font-extrabold text-slate-900">Nuevo usuario</h2>
        <form method="post" class="mt-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nombre completo</label>
                <input type="text" name="nombre" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Usuario</label>
                    <input type="text" name="usuario" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Contraseña</label>
                    <input type="text" name="password" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                    <input type="email" name="email" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Rol</label>
                    <select name="rol" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                        <option value="ADMIN">ADMIN</option>
                        <option value="REVISOR">REVISOR</option>
                        <option value="SUPERADMIN">SUPERADMIN</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Estado</label>
                <select name="activo" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                    <option value="SI">SI</option>
                    <option value="NO">NO</option>
                </select>
            </div>
            <button type="submit" class="w-full rounded-2xl bg-primary px-5 py-3 text-white font-extrabold hover:bg-primary/90 transition">Crear usuario</button>
        </form>
    </section>

    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <h2 class="text-2xl font-extrabold text-slate-900">Usuarios existentes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Nombre</th>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Usuario</th>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Rol</th>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Estado</th>
                        <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">Último acceso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-900"><?= htmlspecialchars($user['nombre_completo']) ?></p>
                            <p class="text-sm text-slate-500"><?= htmlspecialchars($user['email']) ?></p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700"><?= htmlspecialchars($user['usuario']) ?></td>
                        <td class="px-6 py-4 text-sm text-slate-700"><?= htmlspecialchars($user['rol']) ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold <?= ($user['activo'] ?? 'SI') === 'SI' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                                <?= htmlspecialchars($user['activo']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700"><?= format_datetime($user['ultimo_acceso'] ?? null) ?: 'Sin registros' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../templates/admin_footer.php'; ?>
