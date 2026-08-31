<?php

session_start();

/*
=================================
CEK LOGIN ADMIN
=================================
*/

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}


/*
=================================
KONEKSI DATABASE
=================================
*/

include '../koneksi.php';

$error = '';


/*
=================================
CEK ID ARTIKEL
=================================
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID artikel tidak valid.');
}

$id = (int) $_GET['id'];


/*
=================================
AMBIL DATA ARTIKEL
=================================
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM articles
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    die(
        'Gagal menyiapkan database: '
        . mysqli_error($conn)
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$article = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$article) {
    die('Artikel tidak ditemukan.');
}


/*
=================================
PROSES UPDATE
=================================
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tittle   = trim($_POST['tittle'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $author   = trim($_POST['author'] ?? '');
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $content  = $_POST['content'] ?? '';
    $status   = $_POST['status'] ?? 'draft';


    /*
    ==============================
    VALIDASI
    ==============================
    */

    if (
        $tittle === '' ||
        $category === '' ||
        $author === '' ||
        $excerpt === '' ||
        trim(strip_tags($content)) === ''
    ) {

        $error = 'Semua field wajib diisi.';
    }


    /*
    ==============================
    VALIDASI STATUS
    ==============================
    */

    if (
        $error === '' &&
        !in_array(
            $status,
            ['published', 'draft'],
            true
        )
    ) {

        $error = 'Status artikel tidak valid.';
    }


    /*
    ==============================
    BUAT SLUG
    ==============================
    */

    $slug = strtolower($tittle);

    $slug = preg_replace(
        '/[^a-z0-9\s-]/',
        '',
        $slug
    );

    $slug = preg_replace(
        '/[\s-]+/',
        '-',
        $slug
    );

    $slug = trim(
        $slug,
        '-'
    );


    /*
    ==============================
    THUMBNAIL
    ==============================
    */

    $thumbnail = $article['thumbnail'];


    /*
    ==============================
    CEK GAMBAR BARU
    ==============================
    */

    if (
        $error === '' &&
        isset($_FILES['thumbnail']) &&
        $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        /*
        Cek error upload
        */

        if (
            $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK
        ) {

            $error =
                'Terjadi kesalahan saat mengupload gambar.';

        } else {

            $fileName =
                $_FILES['thumbnail']['name'];

            $tmpName =
                $_FILES['thumbnail']['tmp_name'];

            $fileSize =
                $_FILES['thumbnail']['size'];


            /*
            ==============================
            EXTENSION
            ==============================
            */

            $extension = strtolower(
                pathinfo(
                    $fileName,
                    PATHINFO_EXTENSION
                )
            );


            /*
            ==============================
            FORMAT YANG DIPERBOLEHKAN
            ==============================
            */

            $allowed = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];


            if (!in_array(
                $extension,
                $allowed,
                true
            )) {

                $error =
                    'Format gambar harus JPG, JPEG, PNG, atau WEBP.';

            }


            /*
            ==============================
            UKURAN MAKSIMAL 5 MB
            ==============================
            */

            if (
                $error === '' &&
                $fileSize > 5 * 1024 * 1024
            ) {

                $error =
                    'Ukuran gambar maksimal 5 MB.';
            }


            /*
            ==============================
            FOLDER UPLOADS
            ==============================
            */

            if ($error === '') {

                $uploadDirectory =
                    '../uploads/';


                if (!is_dir($uploadDirectory)) {

                    if (!mkdir(
                        $uploadDirectory,
                        0777,
                        true
                    )) {

                        $error =
                            'Folder uploads tidak dapat dibuat.';
                    }
                }
            }


            /*
            ==============================
            UPLOAD GAMBAR BARU
            ==============================
            */

            if ($error === '') {

                $newName =
                    uniqid(
                        'artikel_',
                        true
                    )
                    . '.'
                    . $extension;


                $uploadPath =
                    $uploadDirectory
                    . $newName;


                if (
                    move_uploaded_file(
                        $tmpName,
                        $uploadPath
                    )
                ) {

                    /*
                    Simpan thumbnail baru
                    */

                    $thumbnail =
                        'uploads/'
                        . $newName;


                    /*
                    Hapus thumbnail lama
                    */

                    if (
                        !empty($article['thumbnail'])
                    ) {

                        $oldImage =
                            '../'
                            . $article['thumbnail'];


                        if (
                            file_exists($oldImage)
                        ) {

                            unlink($oldImage);
                        }
                    }

                } else {

                    $error =
                        'Gagal mengupload gambar baru.';
                }
            }
        }
    }


    /*
    ==============================
    UPDATE DATABASE
    ==============================
    */

    if ($error === '') {

        $stmt = mysqli_prepare(
            $conn,

            "UPDATE articles SET

                tittle = ?,
                slug = ?,
                category = ?,
                author = ?,
                excerpt = ?,
                content = ?,
                thumbnail = ?,
                status = ?

             WHERE id = ?"
        );


        if (!$stmt) {

            $error =
                'Gagal menyiapkan database: '
                . mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssi",

                $tittle,
                $slug,
                $category,
                $author,
                $excerpt,
                $content,
                $thumbnail,
                $status,
                $id
            );


            if (
                mysqli_stmt_execute($stmt)
            ) {

                mysqli_stmt_close($stmt);

                header(
                    'Location: artikel.php'
                );

                exit;

            } else {

                $error =
                    'Gagal memperbarui artikel: '
                    . mysqli_stmt_error($stmt);

                mysqli_stmt_close($stmt);
            }
        }
    }


    /*
    ==============================
    JIKA ERROR
    TAMPILKAN DATA POST
    ==============================
    */

    $article['tittle'] =
        $tittle;

    $article['category'] =
        $category;

    $article['author'] =
        $author;

    $article['excerpt'] =
        $excerpt;

    $article['content'] =
        $content;

    $article['status'] =
        $status;

    $article['thumbnail'] =
        $thumbnail;
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
        Edit Artikel - Native Papuan
    </title>


    <!--
    =================================
    QUILL CSS
    =================================
    -->

    <link
        href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
        rel="stylesheet"
    >


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family: Arial, sans-serif;

            background: #f1f5f9;

            color: #1e293b;
        }


        /*
        =================================
        SIDEBAR
        =================================
        */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;

            width: 240px;

            height: 100vh;

            background: #0f172a;

            padding: 25px 18px;

            color: white;

            z-index: 1000;
        }


        .logo {

            font-size: 24px;

            font-weight: bold;

            margin-bottom: 40px;
        }


        .logo span {

            color: #38bdf8;
        }


        .menu {

            list-style: none;
        }


        .menu li {

            margin-bottom: 8px;
        }


        .menu a {

            display: block;

            padding: 13px;

            color: #cbd5e1;

            text-decoration: none;

            border-radius: 8px;

            transition: 0.2s;
        }


        .menu a:hover,
        .menu a.active {

            background: #38bdf8;

            color: #0f172a;
        }


        /*
        =================================
        LOGOUT
        =================================
        */

        .logout {

            position: absolute;

            bottom: 25px;

            left: 18px;

            right: 18px;
        }


        .logout a {

            display: block;

            padding: 12px;

            text-align: center;

            background: #dc2626;

            color: white;

            text-decoration: none;

            border-radius: 8px;
        }


        .logout a:hover {

            background: #b91c1c;
        }


        /*
        =================================
        MAIN
        =================================
        */

        .main {

            margin-left: 240px;

            padding: 30px;

            max-width: 1250px;
        }


        .header {

            margin-bottom: 25px;
        }


        .header h1 {

            font-size: 28px;

            margin-bottom: 6px;
        }


        .header p {

            color: #64748b;
        }


        /*
        =================================
        ERROR
        =================================
        */

        .error {

            background: #fee2e2;

            color: #b91c1c;

            border: 1px solid #fecaca;

            padding: 14px;

            border-radius: 8px;

            margin-bottom: 20px;
        }


        /*
        =================================
        FORM
        =================================
        */

        .form-box {

            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 2px 10px rgba(0,0,0,0.05);
        }


        .form-group {

            margin-bottom: 22px;
        }


        label {

            display: block;

            font-weight: bold;

            margin-bottom: 8px;
        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 13px;

            border: 1px solid #cbd5e1;

            border-radius: 8px;

            font-family: Arial, sans-serif;

            font-size: 14px;
        }


        input:focus,
        select:focus,
        textarea:focus {

            outline: none;

            border-color: #38bdf8;
        }


        textarea {

            resize: vertical;
        }


        /*
        =================================
        CURRENT IMAGE
        =================================
        */

        .current-image {

            margin-bottom: 12px;

            padding: 12px;

            background: #f8fafc;

            border-radius: 8px;
        }


        .current-image p {

            margin-bottom: 10px;

            color: #64748b;

            font-size: 14px;
        }


        .current-image img {

            width: 200px;

            height: 130px;

            object-fit: cover;

            border-radius: 8px;

            display: block;
        }


        /*
        =================================
        QUILL EDITOR
        =================================
        */

        #toolbar {

            border: 1px solid #cbd5e1;

            border-bottom: none;

            border-radius: 8px 8px 0 0;

            background: #f8fafc;
        }


        #editor {

            min-height: 500px;

            border: 1px solid #cbd5e1;

            border-radius: 0 0 8px 8px;

            font-size: 16px;

            background: white;
        }


        #editor .ql-editor {

            min-height: 500px;

            cursor: text;

            line-height: 1.7;
        }


        #editor .ql-editor.ql-blank::before {

            color: #94a3b8;

            font-style: normal;
        }


        .ql-toolbar {

            font-family: Arial, sans-serif;
        }


        /*
        =================================
        BUTTON
        =================================
        */

        .button-group {

            display: flex;

            gap: 10px;

            margin-top: 25px;
        }


        .button {

            padding: 13px 22px;

            border: none;

            border-radius: 8px;

            cursor: pointer;

            font-weight: bold;

            text-decoration: none;

            font-size: 14px;
        }


        .save {

            background: #38bdf8;

            color: #0f172a;
        }


        .save:hover {

            background: #0ea5e9;
        }


        .cancel {

            background: #e2e8f0;

            color: #334155;
        }


        .cancel:hover {

            background: #cbd5e1;
        }


        /*
        =================================
        RESPONSIVE
        =================================
        */

        @media (max-width: 700px) {

            .sidebar {

                width: 70px;

                padding: 20px 10px;
            }


            .logo {

                font-size: 0;

                text-align: center;
            }


            .logo::after {

                content: "NP";

                font-size: 20px;
            }


            .menu a {

                font-size: 0;

                text-align: center;
            }


            .menu a::first-letter {

                font-size: 20px;
            }


            .logout a {

                font-size: 0;
            }


            .logout a::first-letter {

                font-size: 20px;
            }


            .main {

                margin-left: 70px;

                padding: 20px;
            }


            .form-box {

                padding: 20px;
            }


            #editor,
            #editor .ql-editor {

                min-height: 400px;
            }


            .button-group {

                flex-direction: column;
            }


            .button {

                text-align: center;
            }


            .current-image img {

                width: 100%;

                max-width: 300px;

                height: auto;
            }
        }

    </style>

</head>


<body>


<!--
=================================
SIDEBAR
=================================
-->

<aside class="sidebar">

    <div class="logo">

        Native <span>Papuan</span>

    </div>


    <ul class="menu">

        <li>

            <a href="dashboard.php">

                🏠 Dashboard

            </a>

        </li>


        <li>

            <a href="tambah_artikel.php">

                📝 Tambah Artikel

            </a>

        </li>


        <li>

            <a
                href="artikel.php"
                class="active"
            >

                📚 Kelola Artikel

            </a>

        </li>

    </ul>


    <div class="logout">

        <a href="logout.php">

            🚪 Logout

        </a>

    </div>

</aside>


<!--
=================================
MAIN
=================================
-->

<main class="main">


    <div class="header">

        <h1>
            Edit Artikel
        </h1>

        <p>
            Ubah informasi artikel Native Papuan.
        </p>

    </div>


    <?php if ($error !== ''): ?>

        <div class="error">

            <?= htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <div class="form-box">


        <form
            method="POST"
            enctype="multipart/form-data"
            id="article-form"
        >


            <!--
            ==============================
            JUDUL
            ==============================
            -->

            <div class="form-group">

                <label for="tittle">
                    Judul Artikel
                </label>

                <input
                    type="text"
                    id="tittle"
                    name="tittle"
                    value="<?= htmlspecialchars(
                        $article['tittle'] ?? ''
                    ); ?>"
                    required
                >

            </div>


            <!--
            ==============================
            SEGMEN
            ==============================
            -->

            <div class="form-group">

                <label for="category">
                    Segmen
                </label>

                <select
                    id="category"
                    name="category"
                    required
                >

                    <option value="">
                        -- Pilih Segmen --
                    </option>


                    <option
                        value="Native File"
                        <?= (
                            ($article['category'] ?? '') ===
                            'Native File'
                        )
                            ? 'selected'
                            : ''; ?>
                    >
                        Native File
                    </option>


                    <option
                        value="Suara Tanah"
                        <?= (
                            ($article['category'] ?? '') ===
                            'Suara Tanah'
                        )
                            ? 'selected'
                            : ''; ?>
                    >
                        Suara Tanah
                    </option>


                    <option
                        value="Report"
                        <?= (
                            ($article['category'] ?? '') ===
                            'Report'
                        )
                            ? 'selected'
                            : ''; ?>
                    >
                        Report
                    </option>

                </select>

            </div>


            <!--
            ==============================
            PENULIS
            ==============================
            -->

            <div class="form-group">

                <label for="author">
                    Penulis
                </label>

                <input
                    type="text"
                    id="author"
                    name="author"
                    value="<?= htmlspecialchars(
                        $article['author'] ?? ''
                    ); ?>"
                    required
                >

            </div>


            <!--
            ==============================
            GAMBAR
            ==============================
            -->

            <div class="form-group">

                <label for="thumbnail">
                    Gambar Artikel
                </label>


                <?php if (!empty($article['thumbnail'])): ?>

                    <div class="current-image">

                        <p>
                            Gambar saat ini:
                        </p>

                        <img
                            src="../<?= htmlspecialchars(
                                $article['thumbnail']
                            ); ?>"
                            alt="Thumbnail artikel"
                        >

                    </div>

                <?php endif; ?>


                <input
                    type="file"
                    id="thumbnail"
                    name="thumbnail"
                    accept="image/jpeg,image/png,image/webp"
                >


                <small>
                    Kosongkan jika tidak ingin mengganti gambar.
                    Format JPG, JPEG, PNG, WEBP. Maksimal 5 MB.
                </small>

            </div>


            <!--
            ==============================
            RINGKASAN
            ==============================
            -->

            <div class="form-group">

                <label for="excerpt">
                    Ringkasan Artikel
                </label>

                <textarea
                    id="excerpt"
                    name="excerpt"
                    rows="5"
                    required
                ><?= htmlspecialchars(
                    $article['excerpt'] ?? ''
                ); ?></textarea>

            </div>


            <!--
            ==============================
            ISI ARTIKEL
            ==============================
            -->

            <div class="form-group">

                <label>
                    Isi Artikel
                </label>


                <!--
                TOOLBAR QUILL
                -->

                <div id="toolbar">

                    <span class="ql-formats">

                        <select class="ql-header">

                            <option value="1">
                                Heading 1
                            </option>

                            <option value="2">
                                Heading 2
                            </option>

                            <option selected>
                                Normal
                            </option>

                        </select>

                    </span>


                    <span class="ql-formats">

                        <select class="ql-font">

                            <option selected>
                                Sans Serif
                            </option>

                            <option value="serif">
                                Serif
                            </option>

                            <option value="monospace">
                                Monospace
                            </option>

                        </select>


                        <select class="ql-size">

                            <option value="small">
                                Kecil
                            </option>

                            <option selected>
                                Normal
                            </option>

                            <option value="large">
                                Besar
                            </option>

                            <option value="huge">
                                Sangat Besar
                            </option>

                        </select>

                    </span>


                    <span class="ql-formats">

                        <button
                            type="button"
                            class="ql-bold"
                        ></button>

                        <button
                            type="button"
                            class="ql-italic"
                        ></button>

                        <button
                            type="button"
                            class="ql-underline"
                        ></button>

                        <button
                            type="button"
                            class="ql-strike"
                        ></button>

                    </span>


                    <span class="ql-formats">

                        <select class="ql-color"></select>

                        <select class="ql-background"></select>

                    </span>


                    <span class="ql-formats">

                        <button
                            type="button"
                            class="ql-list"
                            value="ordered"
                        ></button>

                        <button
                            type="button"
                            class="ql-list"
                            value="bullet"
                        ></button>

                    </span>


                    <span class="ql-formats">

                        <select class="ql-align"></select>

                    </span>


                    <span class="ql-formats">

                        <button
                            type="button"
                            class="ql-blockquote"
                        ></button>

                        <button
                            type="button"
                            class="ql-link"
                        ></button>

                    </span>


                    <span class="ql-formats">

                        <button
                            type="button"
                            class="ql-clean"
                        ></button>

                    </span>

                </div>


                <!--
                EDITOR
                -->

                <div id="editor"></div>


                <!--
                INPUT HIDDEN

                Isi Quill akan dimasukkan
                ke sini sebelum submit.
                -->

                <input
                    type="hidden"
                    name="content"
                    id="content-data"
                >

            </div>


            <!--
            ==============================
            STATUS
            ==============================
            -->

            <div class="form-group">

                <label for="status">
                    Status
                </label>


                <select
                    id="status"
                    name="status"
                >

                    <option
                        value="published"
                        <?= (
                            ($article['status'] ?? '') ===
                            'published'
                        )
                            ? 'selected'
                            : ''; ?>
                    >
                        Published
                    </option>


                    <option
                        value="draft"
                        <?= (
                            ($article['status'] ?? '') ===
                            'draft'
                        )
                            ? 'selected'
                            : ''; ?>
                    >
                        Draft
                    </option>

                </select>

            </div>


            <!--
            ==============================
            BUTTON
            ==============================
            -->

            <div class="button-group">

                <button
                    type="submit"
                    class="button save"
                >

                    💾 Simpan Perubahan

                </button>


                <a
                    href="artikel.php"
                    class="button cancel"
                >

                    Batal

                </a>

            </div>


        </form>

    </div>

</main>


<!--
=================================
QUILL JAVASCRIPT
=================================
-->

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>


<script>

    /*
    =================================
    AMBIL ISI ARTIKEL DARI PHP
    =================================
    */

    const articleContent =
        <?= json_encode(
            $article['content'] ?? '',
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;


    /*
    =================================
    BUAT QUILL
    =================================
    */

    const quill = new Quill(
        '#editor',
        {

            theme: 'snow',

            placeholder:
                'Tulis artikel kamu di sini...',

            modules: {

                toolbar: '#toolbar'

            }

        }
    );


    /*
    =================================
    MASUKKAN ARTIKEL LAMA
    =================================
    */

    if (articleContent) {

        quill.clipboard.dangerouslyPasteHTML(
            articleContent
        );

    }


    /*
    =================================
    FOCUS EDITOR
    =================================
    */

    quill.focus();


    /*
    =================================
    FORM SUBMIT
    =================================
    */

    const form =
        document.getElementById(
            'article-form'
        );


    const contentData =
        document.getElementById(
            'content-data'
        );


    form.addEventListener(
        'submit',
        function () {

            /*
            Ambil HTML dari Quill
            */

            contentData.value =
                quill.root.innerHTML;

        }
    );

</script>


</body>

</html>