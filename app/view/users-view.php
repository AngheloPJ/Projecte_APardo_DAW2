<?php
require_once BASE_PATH . '/app/view/header-view.php';

$errors = [];
if (!isset($error)) $error = null;
if ($error) $errors[] = $error;
?>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/users.css">
</head>
<body>

<main>
    <div class="admin-container">
        <h2>Gestión de Usuarios</h2>

        <table class="usuarios-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u->getUsername()) ?></td>
                    <td><?= htmlspecialchars($u->getEmail()) ?></td>
                    <td><?= htmlspecialchars($u->getRol()) ?></td>
                    <td class="acciones">
                        <a href="<?= BASE_URL ?>admin/users/edit/<?= $u->getId() ?>" class="btn-editar">Editar</a>
                        <a href="<?= BASE_URL ?>admin/users/delete/<?= $u->getId() ?>" class="btn-borrar" onclick="return confirm('¿Seguro que quieres eliminar este usuario?')">Borrar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= BASE_URL ?>admin/users?p=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
</main>

<?php
require_once BASE_PATH . '/app/view/footer-view.php';
?>
    
</body>
</html>