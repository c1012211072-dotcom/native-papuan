<?php

include 'koneksi.php';

$category = $_GET['category'] ?? '';

if ($category === '') {
    die('Segmen tidak ditemukan.');
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM articles
     WHERE category = ?
     AND status = 'published'
     ORDER BY created_at DESC"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $category
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="id">


<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($category); ?>
        - Native Papuan
    </title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/webp" href="foto/logo.webp">

</head>


<body>


<nav>

    <div class="logo">

        <img
            src="foto/logo.webp"
            alt="Logo Native Papuan"
        >

        <span>
            NATIVE <b>PAPUAN</b>
        </span>

    </div>


    <div class="nav-menu">

        <a href="segmen.php?category=Native%20File">
            Native File
        </a>

        <a href="segmen.php?category=Suara%20Tanah">
            Suara Tanah
        </a>

        <a href="segmen.php?category=Report">
            Report
        </a>

    </div>

</nav>


<main class="container">

    <div class="section-title">

        <h2>
            <?php echo htmlspecialchars($category); ?>
        </h2>

        <span>
            Artikel dalam segmen
            <?php echo htmlspecialchars($category); ?>
        </span>

    </div>


    <?php if (mysqli_num_rows($result) > 0): ?>


        <div class="articles">


            <?php while ($article = mysqli_fetch_assoc($result)): ?>


                <article class="article">


                    <?php if (!empty($article['thumbnail'])): ?>

                        <img
                            class="thumbnail"
                            src="<?php echo htmlspecialchars(
                                $article['thumbnail']
                            ); ?>"
                            alt="<?php echo htmlspecialchars(
                                $article['tittle']
                            ); ?>"
                        >

                    <?php else: ?>

                        <div class="thumbnail"></div>

                    <?php endif; ?>


                    <div class="article-content">


                        <div class="category">

                            <?php echo htmlspecialchars(
                                $article['category']
                            ); ?>

                        </div>


                        <h2>

                            <?php echo htmlspecialchars(
                                $article['tittle']
                            ); ?>

                        </h2>


                        <p>

                            <?php echo htmlspecialchars(
                                $article['excerpt']
                            ); ?>

                        </p>


                        <div class="meta">

                            Oleh

                            <?php echo htmlspecialchars(
                                $article['author']
                            ); ?>

                            ·

                            <?php echo date(
                                "d M Y",
                                strtotime(
                                    $article['created_at']
                                )
                            ); ?>

                        </div>


                        <a
                            class="read-more"
                            href="artikel.php?slug=<?php echo urlencode(
                                $article['slug']
                            ); ?>"
                        >

                            Baca selengkapnya →

                        </a>


                    </div>

                </article>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="empty">

            <h3>
                Belum ada artikel
            </h3>

            <p>
                Belum ada artikel dalam segmen ini.
            </p>

        </div>


    <?php endif; ?>


</main>


<footer>

    <strong>
        Native Papuan
    </strong>

    <br><br>

    Media dan cerita dari Papua.

</footer>


</body>

</html>