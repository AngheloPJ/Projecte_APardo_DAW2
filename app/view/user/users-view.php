<?php
require_once BASE_PATH . '/app/view/layout/header-view.php';
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
                            <img src="<?= htmlspecialchars($u['avatarUrl']) ?>" 
                                alt="Avatar de <?= htmlspecialchars($u['displayName']) ?>" 
                                class="user-avatar-small">
                        </td>

                        <td><?= $u['id'] ?></td>
                        <td>@<?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['displayName']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="role-badge role-<?= $u['roleValue'] ?>">
                                <?= $u['roleLabel'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($u['createdAtFormatted']) ?></td>
                        <td class="acciones">
                            <a href="<?= BASE_URL ?>admin/users/edit/<?= $u['id'] ?>" class="btn-editar">
                                Editar
                            </a>
                            <?php if ($u['canDelete']): ?>
                                <form 
                                    action="<?= BASE_URL ?>admin/users/delete/<?= $u['id'] ?>" 
                                    method="post" 
                                    onsubmit="return confirm('¿Seguro que quieres eliminar a <?= htmlspecialchars($u['displayName']) ?>?')"
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
