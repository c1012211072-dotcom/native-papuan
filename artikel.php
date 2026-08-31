<?php

include 'koneksi.php';

/*
=================================
AMBIL SLUG
=================================
*/

$slug = $_GET['slug'] ?? '';

/*
=================================
AMBIL ARTIKEL
=================================
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM articles
     WHERE slug = ?
     AND status = 'published'
     LIMIT 1"
);

if (!$stmt) {
    die("Gagal menyiapkan database.");
}

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $slug
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$article = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/*
=================================
CEK ARTIKEL
=================================
*/

if (!$article) {
    die("Artikel tidak ditemukan.");
}

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
        <?php
        echo htmlspecialchars($article['tittle']);
        ?>
        - Native Papuan
    </title>


    <style>

        /*
        =================================
        RESET
        =================================
        */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        /*
        =================================
        BODY
        =================================
        */

        body {

            font-family:
                'Segoe UI',
                Arial,
                sans-serif;

            color: #f5f5f5;

            background: #111;

            line-height: 1.8;

            overflow-x: hidden;

        }


        /*
        =================================
        BACKGROUND
        =================================
        */

        .bg-image {

            position: fixed;

            inset: 0;

            width: 100%;
            height: 100%;

            background-image:
                url("foto/Background 1 (1).webp");

            background-size: cover;

            background-position: center;

            background-repeat: no-repeat;

            z-index: -3;

        }


        /*
        =================================
        OVERLAY
        =================================
        */

        .overlay {

            position: fixed;

            inset: 0;

            width: 100%;
            height: 100%;

            background:
                rgba(0, 0, 0, 0.62);

            z-index: -2;

        }


        /*
        =================================
        KONTROL MUSIK
        =================================
        */

        .controls {

            position: fixed;

            top: 20px;
            right: 20px;

            display: flex;

            align-items: center;

            gap: 10px;

            padding: 10px 14px;

            background:
                rgba(0, 0, 0, 0.65);

            backdrop-filter: blur(12px);

            border:
                1px solid
                rgba(255,255,255,0.15);

            border-radius: 50px;

            z-index: 1000;

        }


        /*
        =================================
        MUSIC BUTTON
        =================================
        */

        .music-btn {

            border: none;

            background:
                rgba(255,255,255,0.12);

            color: white;

            padding: 9px 16px;

            border-radius: 30px;

            cursor: pointer;

            font-size: 14px;

            transition: 0.25s;

        }


        .music-btn:hover {

            background:
                rgba(255,255,255,0.25);

        }


        .music-btn.active {

            background: #4ade80;

            color: #000;

            font-weight: bold;

        }


        /*
        =================================
        VOLUME
        =================================
        */

        .volume {

            display: flex;

            align-items: center;

            gap: 7px;

        }


        .volume input {

            width: 80px;

            cursor: pointer;

            accent-color: #4ade80;

        }


        /*
        =================================
        CONTAINER
        =================================
        */

        .container {

            width: 100%;

            max-width: 850px;

            margin: auto;

            padding:
                110px 25px 80px;

        }


        /*
        =================================
        KEMBALI
        =================================
        */

        .back {

            display: inline-block;

            margin-bottom: 35px;

            color: #fff;

            text-decoration: none;

            padding: 8px 16px;

            border-radius: 25px;

            background:
                rgba(255,255,255,0.1);

            backdrop-filter: blur(8px);

            transition: 0.25s;

        }


        .back:hover {

            background:
                rgba(255,255,255,0.2);

        }


        /*
        =================================
        JUDUL
        =================================
        */

        h1 {

            font-size: 46px;

            line-height: 1.2;

            margin-bottom: 15px;

            text-shadow:
                0 3px 15px
                rgba(0,0,0,0.6);

        }


        /*
        =================================
        META
        =================================
        */

        .meta {

            color: #d0d0d0;

            margin-bottom: 35px;

            font-size: 15px;

        }


        /*
        =================================
        THUMBNAIL
        =================================
        */

        .thumbnail {

            width: 100%;

            max-height: 500px;

            object-fit: cover;

            border-radius: 16px;

            margin-bottom: 30px;

            box-shadow:
                0 15px 40px
                rgba(0,0,0,0.45);

            display: block;

        }


        /*
        =================================
        EXCERPT
        =================================
        */

        .excerpt {

            font-size: 20px;

            color: #e5e5e5;

            margin-bottom: 30px;

            font-weight: 500;

        }


        /*
        =================================
        CONTENT ARTIKEL
        =================================
        */

        .content {

            font-size: 18px;

            color: #f0f0f0;

            background:
                rgba(0,0,0,0.25);

            padding: 25px;

            border-radius: 15px;

            backdrop-filter: blur(4px);

        }


        /*
        =================================
        PARAGRAF
        =================================
        */

        .content p {

            margin-bottom: 22px;

        }


        /*
        =================================
        HEADING
        =================================
        */

        .content h1 {

            font-size: 32px;

            margin-top: 35px;

            margin-bottom: 18px;

            line-height: 1.3;

        }


        .content h2 {

            font-size: 28px;

            margin-top: 30px;

            margin-bottom: 15px;

            line-height: 1.3;

        }


        .content h3 {

            font-size: 24px;

            margin-top: 25px;

            margin-bottom: 12px;

            line-height: 1.3;

        }


        /*
        =================================
        BOLD
        =================================
        */

        .content strong {

            font-weight: 700;

        }


        /*
        =================================
        ITALIC
        =================================
        */

        .content em {

            font-style: italic;

        }


        /*
        =================================
        UNDERLINE
        =================================
        */

        .content u {

            text-decoration:
                underline;

        }


        /*
        =================================
        STRIKETHROUGH
        =================================
        */

        .content s {

            text-decoration:
                line-through;

        }


        /*
        =================================
        LIST
        =================================
        */

        .content ul {

            margin-top: 15px;

            margin-bottom: 22px;

            padding-left: 30px;

        }


        .content ol {

            margin-top: 15px;

            margin-bottom: 22px;

            padding-left: 30px;

        }


        .content li {

            margin-bottom: 8px;

        }


        /*
        =================================
        LINK
        =================================
        */

        .content a {

            color: #38bdf8;

            text-decoration: underline;

        }


        .content a:hover {

            color: #7dd3fc;

        }


        /*
        =================================
        BLOCKQUOTE
        =================================
        */

        .content blockquote {

            margin: 25px 0;

            padding:
                15px 20px;

            border-left:
                4px solid #38bdf8;

            background:
                rgba(255,255,255,0.08);

            font-style: italic;

            color: #e2e8f0;

            border-radius:
                0 8px 8px 0;

        }


        /*
        =================================
        TABLE
        =================================
        */

        .content table {

            width: 100%;

            border-collapse:
                collapse;

            margin: 25px 0;

        }


        .content th,
        .content td {

            border:
                1px solid
                rgba(255,255,255,0.25);

            padding: 10px;

            text-align: left;

        }


        .content th {

            background:
                rgba(255,255,255,0.08);

            font-weight: bold;

        }


        /*
        =================================
        IMAGE DI DALAM ARTIKEL
        =================================
        */

        .content img {

            max-width: 100%;

            height: auto;

            border-radius: 10px;

            margin:
                15px 0;

        }


        /*
        =================================
        HORIZONTAL LINE
        =================================
        */

        hr {

            border: none;

            border-top:
                1px solid
                rgba(255,255,255,0.15);

            margin:
                35px 0;

        }


        /*
        =================================
        YOUTUBE PLAYER
        =================================
        */

        #youtube-player {

            position: fixed;

            width: 1px;

            height: 1px;

            opacity: 0;

            pointer-events: none;

            bottom: 0;

            left: 0;

        }


        /*
        =================================
        RESPONSIVE
        =================================
        */

        @media (max-width: 600px) {


            .controls {

                top: 12px;

                right: 12px;

                padding:
                    8px 10px;

            }


            .volume input {

                width: 60px;

            }


            .container {

                padding:
                    100px 18px 60px;

            }


            h1 {

                font-size: 32px;

            }


            .content {

                padding: 20px;

                font-size: 17px;

            }


            .content h1 {

                font-size: 27px;

            }


            .content h2 {

                font-size: 24px;

            }


            .content h3 {

                font-size: 21px;

            }


            .excerpt {

                font-size: 18px;

            }


            .content table {

                display: block;

                overflow-x: auto;

            }

        }

    </style>

     <link rel="icon" type="image/webp" href="foto/logo.webp">

</head>


<body>


<!-- =====================================
     BACKGROUND
===================================== -->

<div class="bg-image"></div>

<div class="overlay"></div>


<!-- =====================================
     KONTROL MUSIK
===================================== -->

<div class="controls">

    <button
        class="music-btn"
        id="musicButton"
        onclick="toggleMusic()"
    >

        🎵 Musik

    </button>


    <div class="volume">

        🔊

        <input
            type="range"
            id="volume"
            min="0"
            max="100"
            value="40"
            oninput="changeVolume(this.value)"
        >

    </div>

</div>


<!-- =====================================
     YOUTUBE PLAYER
===================================== -->

<div id="youtube-player"></div>


<!-- =====================================
     ARTIKEL
===================================== -->

<main class="container">


    <!-- KEMBALI -->

    <a
        class="back"
        href="index.php"
    >

        ← Kembali ke artikel

    </a>


    <!-- JUDUL -->

    <h1>

        <?php

        echo htmlspecialchars(
            $article['tittle']
        );

        ?>

    </h1>


    <!-- META -->

    <div class="meta">

        Oleh

        <strong>

            <?php

            echo htmlspecialchars(
                $article['author']
            );

            ?>

        </strong>

        ·

        <?php

        echo date(
            "d M Y",
            strtotime(
                $article['created_at']
            )
        );

        ?>

    </div>


    <!-- THUMBNAIL -->

    <?php if (!empty($article['thumbnail'])): ?>

        <img
            class="thumbnail"
            src="<?php
                echo htmlspecialchars(
                    $article['thumbnail']
                );
            ?>"
            alt="<?php
                echo htmlspecialchars(
                    $article['tittle']
                );
            ?>"
        >

    <?php endif; ?>


    <!-- RINGKASAN -->

    <p class="excerpt">

        <?php

        echo htmlspecialchars(
            $article['excerpt']
        );

        ?>

    </p>


    <hr>


    <!-- =====================================
         ISI ARTIKEL
    ===================================== -->

    <div class="content">

        <?php

        /*
        PENTING:
        Jangan gunakan htmlspecialchars()
        atau nl2br() di bagian ini.

        Quill menyimpan isi artikel
        dalam bentuk HTML.
        */

        echo $article['content'];

        ?>

    </div>


</main>


<!-- =====================================
     YOUTUBE API
===================================== -->

<script src="https://www.youtube.com/iframe_api"></script>


<script>

    /*
    =================================
    YOUTUBE PLAYER
    =================================
    */

    let player;

    let isPlaying = false;


    /*
    =================================
    YOUTUBE READY
    =================================
    */

    function onYouTubeIframeAPIReady() {

        player = new YT.Player(
            'youtube-player',
            {

                height: '1',

                width: '1',

                videoId: 'ZbyxjGE885I',

                playerVars: {

                    autoplay: 0,

                    controls: 0,

                    loop: 1,

                    playlist:
                        'ZbyxjGE885I',

                    modestbranding: 1

                },

                events: {

                    onReady:
                        function(event) {

                            event.target
                                .setVolume(40);

                        }

                }

            }
        );

    }


    /*
    =================================
    TOGGLE MUSIC
    =================================
    */

    function toggleMusic() {

        if (!player) {

            return;

        }


        const button =
            document.getElementById(
                'musicButton'
            );


        if (!isPlaying) {

            player.playVideo();


            button.classList.add(
                'active'
            );


            button.innerHTML =
                '⏸ Stop Musik';


            isPlaying = true;

        }

        else {

            player.pauseVideo();


            button.classList.remove(
                'active'
            );


            button.innerHTML =
                '🎵 Musik';


            isPlaying = false;

        }

    }


    /*
    =================================
    VOLUME
    =================================
    */

    function changeVolume(value) {

        if (player) {

            player.setVolume(
                Number(value)
            );

        }

    }

</script>


</body>

</html>