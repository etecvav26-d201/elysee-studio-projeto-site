<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Elysee Studio — beleza, cuidado e rituais contemporâneos em um espaço pensado para você.">
    <meta name="theme-color" content="#f5f0e8">
    <title>Elysee Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php
    $navbarFile = __DIR__ . '/includes/navbar.php';
    if (is_file($navbarFile)) {
        require $navbarFile;
    }
    ?>

    <main>
        
    <section class="hero" id="inicio">
        <div class="hero-media" role="img" aria-label="Ambiente sofisticado do Elysee Studio"></div>
        <div class="hero-overlay"></div>

        <div class="container hero-content">
            <h1 class="hero-title reveal">
                <span>Brilho atemporal.</span>
                <em>Seu momento.</em>
            </h1>
            <br><br>
            <div class="hero-actions reveal">
                <a class="btn btn-light" href="agendar.php">
                    <span>Agendar experiência</span>
                    <span class="btn-arrow">↗</span>
                </a>
            </div>
        </div>
    </section> 

    </main>



    <?php
    $footerFile = __DIR__ . '/includes/footer.php';
    if (is_file($footerFile)) {
        require $footerFile;
    }
    ?>

<script src="assets/js/script.js" defer></script>
</body>
</html>
