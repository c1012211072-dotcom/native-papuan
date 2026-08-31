<?php
include 'koneksi.php';

$query = "SELECT * FROM articles 
          WHERE status = 'published' 
          ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Native Papuan</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/webp" href="foto/logo.webp">

   
</head>

<body>

<nav>

    <div class="logo">
        <img src="foto/logo.webp" alt="Logo Native Papuan">
        <span>NATIVE <b>PAPUAN</b></span>
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

<!-- HERO -->

<section class="hero">
  
    <div class="hero-content">

        <small>Media & Cerita Papua</small>

        <h1>
            Cerita Papua, dari tanah dan suara masyarakatnya.
        </h1>

        <p>
            Native Papuan menghadirkan berita, cerita, budaya,
            dan berbagai informasi tentang Papua.
        </p>

    </div>

</section>


<!-- ARTICLES -->

<main class="container">

    <div class="section-title">

        <h2>Artikel Terbaru</h2>

        <span>
            Cerita terbaru Native Papuan
        </span>

    </div>


    <?php if (mysqli_num_rows($result) > 0): ?>

        <div class="articles">

            <?php while ($article = mysqli_fetch_assoc($result)): ?>

                <article class="article">

                    <?php if (!empty($article['thumbnail'])): ?>

                        <img
                            class="thumbnail"
                            src="<?php echo htmlspecialchars($article['thumbnail']); ?>"
                            alt="<?php echo htmlspecialchars($article['tittle']); ?>"
                        >

                    <?php else: ?>

                        <div class="thumbnail"></div>

                    <?php endif; ?>


                    <div class="article-content">

                        <div class="category">

                            <?php echo htmlspecialchars($article['category']); ?>

                        </div>


                        <h2>

                            <?php echo htmlspecialchars($article['tittle']); ?>

                        </h2>


                        <p>

                            <?php echo htmlspecialchars($article['excerpt']); ?>

                        </p>


                        <div class="meta">

                            Oleh
                            <?php echo htmlspecialchars($article['author']); ?>

                            ·

                            <?php
                            echo date(
                                "d M Y",
                                strtotime($article['created_at'])
                            );
                            ?>

                        </div>


                        <a
                            class="read-more"
                            href="artikel.php?slug=<?php echo urlencode($article['slug']); ?>"
                        >
                            Baca selengkapnya →
                        </a>

                    </div>

                </article>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <h3>Belum ada artikel</h3>

            <p>
                Artikel yang sudah dipublikasikan akan muncul di sini.
            </p>

        </div>

    <?php endif; ?>

</main>


<!-- FOOTER -->

<footer>

    <strong>Native Papuan</strong>

    <br><br>

    Media dan cerita dari Papua.

</footer>

</body>
</html>