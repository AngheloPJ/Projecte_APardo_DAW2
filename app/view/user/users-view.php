<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';

$errors = [];
if (!isset($error)) $error = null;
if ($error) $errors[] = $error;

$successMsg = null;
$errorMsg = null;

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'deleted') {
        $successMsg = 'Usuario eliminado correctamente.';
    }
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'self_delete') {
        $errorMsg = 'No puedes eliminarte a ti mismo.';
    } elseif ($_GET['error'] === 'delete') {
        $errorMsg = 'Error al eliminar el usuario.';
    }
}
?>

<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios | APardo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/users.css">
</head>
<body>

<main>
    <div class="admin-container">
        <div class="article-head">
            <a id="btn-volver" href="<?= BASE_URL ?>home"><span>🠠</span></a>
            <h2>Gestión de Usuarios</h2>
        </div>

        <?php if ($successMsg): ?>
            <div class="success-message">
                <p><?= htmlspecialchars($successMsg) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="error-message">
                <p><?= htmlspecialchars($errorMsg) ?></p>
            </div>
        <?php endif; ?>

        <table class="usuarios-table" role="table">
            <thead>
                <tr>
                    <th role="columnheader">Avatar</th>
                    <th role="columnheader">ID</th>
                    <th role="columnheader">Usuario</th>
                    <th role="columnheader">Nombre</th>
                    <th role="columnheader">Email</th>
                    <th role="columnheader">Rol</th>
                    <th role="columnheader">Fecha Registro</th>
                    <th role="columnheader">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="no-users">No hay usuarios registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <?php 
                                $rawAvatar = $u->getAvatarUrl();
                                if ($rawAvatar && (str_starts_with($rawAvatar, 'http://') || str_starts_with($rawAvatar, 'https://'))) {
                                    $avatarUrl = $rawAvatar;
                                } else {
                                    $avatarUrl = $rawAvatar 
                                        ? BASE_URL . $rawAvatar 
                                        : BASE_URL . 'public/uploads/avatars/default.webp';
                                }
                            ?>   
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" 
                                alt="Avatar de <?= htmlspecialchars($u->getDisplayName()) ?>" 
                                class="user-avatar-small">
                        </td>

                        <td><?= $u->getId() ?></td>
                        <td>@<?= htmlspecialchars($u->getUsername()) ?></td>
                        <td><?= htmlspecialchars($u->getDisplayName()) ?></td>
                        <td><?= htmlspecialchars($u->getEmail()) ?></td>
                        <td>
                            <span class="role-badge role-<?= $u->getRole()->value ?>">
                                <?= $u->getRole() === Role::ADMIN ? 'Admin' : 'Usuario' ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($u->getCreatedAt())) ?></td>
                        <td class="acciones">
                            <a href="<?= BASE_URL ?>admin/users/edit/<?= $u->getId() ?>" class="btn-editar">
                                Editar
                            </a>
                            <?php if ($currentUser->getId() !== $u->getId()): ?>
                                <form 
                                    action="<?= BASE_URL ?>admin/users/delete/<?= $u->getId() ?>" 
                                    method="post" 
                                    onsubmit="return confirm('¿Seguro que quieres eliminar a <?= htmlspecialchars($u->getDisplayName()) ?>?')"
                                >
                                    <button type="submit" class="btn-borrar">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <!-- Botón anterior -->
                <?php if ($page > 1): ?>
                    <a href="<?= BASE_URL ?>admin/users?p=<?= $page - 1 ?>" class="page-link">
                        &laquo; Anterior
                    </a>
                <?php endif; ?>

                <!-- Números de página -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a 
                        href="<?= BASE_URL ?>admin/users?p=<?= $i ?>" 
                        class="page-link <?= $i === $page ? 'active' : '' ?>"
                    >
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <!-- Botón siguiente -->
                <?php if ($page < $totalPages): ?>
                    <a href="<?= BASE_URL ?>admin/users?p=<?= $page + 1 ?>" class="page-link">
                        Siguiente &raquo;
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once BASE_PATH . '/app/view/layout/footer-view.php'; ?>
    
</body>
</html>
