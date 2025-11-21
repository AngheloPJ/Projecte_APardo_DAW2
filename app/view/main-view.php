<!DOCTYPE html>
<html lang="cat">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prj 1 | APardo</title>

    <!-- CSS PRINCIPAL -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/main.css">

    <!-- Componentes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>resources/css/footer.css">
</head>
<body>

<header class="header">
    <div class="header-left">
        <a href="<?= BASE_URL ?>home"><h1>Prj 1 | APardo</h1></a>
    </div>

    <div class="header-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php 
                $userId = $_SESSION['user_id'];
                $user = UserDAO::getById($userId);
            ?>
            <span>Hola, <?= htmlspecialchars($user['nom']) ?>!</span>
            <a href="<?= BASE_URL ?>logout">
                <button>Cerrar sesión</button>
            </a>
        <?php else: ?>
            <!-- Desplegable de Login/Registro -->
            <div class="dropdown">
                <button class="dropbtn">Cuenta</button>
                <div class="dropdown-content">
                    <a href="<?= BASE_URL ?>login">Iniciar sesión</a>
                    <a href="<?= BASE_URL ?>register">Registrarse</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

</header>

<main>
    <div class="contenidor">

        <?php if (count($articles) > 0): ?>
            <div class="articles">
                <?php foreach ($articles as $index => $article): ?>
                    <div class="article">
                        
                        <p><b>Títol:</b> <?= htmlspecialchars($article['titol']) ?></p>
                        <p><b>Cos:</b> <?= htmlspecialchars($article['cos']) ?></p>

                        <?php if (!empty($article['imatge_url'])): ?>
                            <img src="<?= BASE_URL . $article['imatge_url'] ?>" alt="Imatge de l'article" width="150">
                        <?php endif; ?>

                        <p><b>Autor:</b> <?= htmlspecialchars($article['autor_nom']) ?></p>
                        <p><b>Data creació:</b> <?= $article['data_creacio'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No hi ha articles</p>
        <?php endif; ?>

    </div>

    <?php if ($totalPages > 1): ?>
        <div class="botons">

            <!-- Botón anterior -->
            <?php if ($page > 1): ?>
                <a href="<?= BASE_URL ?>?p=<?= $page - 1 ?>&total=<?= $perPage ?>">
                    <button>←</button>
                </a>
            <?php endif; ?>

            <!-- Botones de número -->
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p == $page): ?>
                    <button class="actual" disabled><?= $p ?></button>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>?p=<?= $p ?>&total=<?= $perPage ?>">
                        <button><?= $p ?></button>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- Botón siguiente -->
            <?php if ($page < $totalPages): ?>
                <a href="<?= BASE_URL ?>?p=<?= $page + 1 ?>&total=<?= $perPage ?>">
                    <button>→</button>
                </a>
            <?php endif; ?>

            <!-- Select “artículos por página” -->
            <form method="get" class="articles-per-page">
                <input type="hidden" name="page" value="home">
                <select name="total" id="total" onchange="this.form.submit()">

                    <?php foreach ($options as $num): ?>
                        <option value="<?= $num ?>" <?= ($num == $perPage) ? 'selected' : '' ?>>
                            <?= $num ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </form>

        </div>
    <?php endif; ?>

</main>

<footer>
    <p>El Iker es gay</p>
    <div class="footer-buttons">
        <button>Privacitat</button>
        <button>Terminos</button>
    </div>
</footer>

</body>
</html>
