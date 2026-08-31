<?php

session_start();

/* =================================
   CEK LOGIN ADMIN
================================= */

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}


/* =================================
   KONEKSI DATABASE
================================= */

include '../koneksi.php';

$error = '';


/* =================================
   PROSES FORM
================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tittle   = trim($_POST['tittle'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $author   = trim($_POST['author'] ?? '');
    $excerpt  = trim($_POST['excerpt'] ?? '');
    $content  = $_POST['content'] ?? '';
    $status   = $_POST['status'] ?? 'draft';


    /* =================================
       VALIDASI
    ================================= */

    if (
        $tittle === '' ||
        $category === '' ||
        $author === '' ||
        $excerpt === '' ||
        trim(strip_tags($content)) === ''
    ) {
        $error = 'Semua field wajib diisi.';
    }


    /* =================================
       VALIDASI STATUS
    ================================= */

    if (
        $error === '' &&
        !in_array($status, ['published', 'draft'], true)
    ) {
        $error = 'Status artikel tidak valid.';
    }


    /* =================================
       UPLOAD GAMBAR
    ================================= */

    $thumbnail = '';

    if ($error === '') {

        if (
            !isset($_FILES['thumbnail']) ||
            $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK
        ) {

            $error = 'Gambar artikel wajib dipilih.';

        } else {

            $fileName = $_FILES['thumbnail']['name'];
            $tmpName  = $_FILES['thumbnail']['tmp_name'];

            $extension = strtolower(
                pathinfo($fileName, PATHINFO_EXTENSION)
            );


            /* FORMAT */

            $allowed = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];


            if (!in_array($extension, $allowed, true)) {

                $error =
                    'Format gambar harus JPG, JPEG, PNG, atau WEBP.';

            }


            /* UKURAN */

            if (
                $error === '' &&
                $_FILES['thumbnail']['size'] > 5 * 1024 * 1024
            ) {

                $error =
                    'Ukuran gambar maksimal 5 MB.';
            }


            /* FOLDER */

            if ($error === '') {

                $uploadDirectory = '../uploads/';

                if (!is_dir($uploadDirectory)) {

                    if (!mkdir($uploadDirectory, 0777, true)) {

                        $error =
                            'Folder uploads tidak dapat dibuat.';
                    }
                }
            }


            /* NAMA FILE */

            if ($error === '') {

                $newName =
                    uniqid('artikel_', true)
                    . '.'
                    . $extension;

                $uploadPath =
                    $uploadDirectory . $newName;


                /* PINDAHKAN FILE */

                if (
                    move_uploaded_file(
                        $tmpName,
                        $uploadPath
                    )
                ) {

                    $thumbnail =
                        'uploads/' . $newName;

                } else {

                    $error =
                        'Gagal mengupload gambar.';
                }
            }
        }
    }


    /* =================================
       BUAT SLUG
    ================================= */

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

    $slug = trim($slug, '-');


    /* =================================
       SIMPAN DATABASE
    ================================= */

    if ($error === '') {

        $stmt = mysqli_prepare(
            $conn,

            "INSERT INTO articles
            (
                tittle,
                slug,
                category,
                author,
                excerpt,
                content,
                thumbnail,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );


        if (!$stmt) {

            $error =
                'Gagal menyiapkan database: '
                . mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssss",
                $tittle,
                $slug,
                $category,
                $author,
                $excerpt,
                $content,
                $thumbnail,
                $status
            );


            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header('Location: dashboard.php');

                exit;

            } else {

                $error =
                    'Gagal menyimpan artikel: '
                    . mysqli_stmt_error($stmt);

                mysqli_stmt_close($stmt);
            }
        }
    }
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
        Tambah Artikel - Native Papuan
    </title>


    <!-- =================================
         QUILL CSS
    ================================= -->

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


        /* =================================
           SIDEBAR
        ================================= */

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


        /* =================================
           LOGOUT
        ================================= */

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


        /* =================================
           MAIN
        ================================= */

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


        /* =================================
           ERROR
        ================================= */

        .error {

            background: #fee2e2;

            color: #b91c1c;

            border: 1px solid #fecaca;

            padding: 14px;

            border-radius: 8px;

            margin-bottom: 20px;

        }


        /* =================================
           FORM
        ================================= */

        .form-box {

            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.05);

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


        .file-info {

            display: block;

            margin-top: 7px;

            color: #64748b;

            font-size: 13px;

        }


        /* =================================
           QUILL EDITOR
        ================================= */

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


        /* =================================
           FONT TAMBAHAN
        ================================= */

        .ql-font-arial {

            font-family: Arial, sans-serif;

        }


        .ql-font-georgia {

            font-family: Georgia, serif;

        }


        .ql-font-times {

            font-family: "Times New Roman", serif;

        }


        .ql-font-verdana {

            font-family: Verdana, sans-serif;

        }


        .ql-font-trebuchet {

            font-family: "Trebuchet MS", sans-serif;

        }


        .ql-font-courier {

            font-family: "Courier New", monospace;

        }


        /* =================================
           BUTTON
        ================================= */

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


        /* =================================
           RESPONSIVE
        ================================= */

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

        }

    </style>

</head>


<body>


<!-- =================================
     SIDEBAR
================================= -->

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

            <a
                href="tambah_artikel.php"
                class="active"
            >

                📝 Tambah Artikel

            </a>

        </li>


        <li>

            <a href="artikel.php">

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


<!-- =================================
     MAIN
================================= -->

<main class="main">


    <div class="header">

        <h1>
            Tambah Artikel
        </h1>

        <p>
            Buat artikel baru untuk Native Papuan.
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


            <!-- JUDUL -->

            <div class="form-group">

                <label for="tittle">

                    Judul Artikel

                </label>


                <input
                    type="text"
                    id="tittle"
                    name="tittle"
                    value="<?= htmlspecialchars(
                        $_POST['tittle'] ?? ''
                    ); ?>"
                    placeholder="Masukkan judul artikel"
                    required
                >

            </div>


            <!-- SEGMEN -->

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
                            ($_POST['category'] ?? '') ===
                            'Native File'
                        ) ? 'selected' : ''; ?>
                    >

                        Native File

                    </option>


                    <option
                        value="Suara Tanah"
                        <?= (
                            ($_POST['category'] ?? '') ===
                            'Suara Tanah'
                        ) ? 'selected' : ''; ?>
                    >

                        Suara Tanah

                    </option>


                    <option
                        value="Report"
                        <?= (
                            ($_POST['category'] ?? '') ===
                            'Report'
                        ) ? 'selected' : ''; ?>
                    >

                        Report

                    </option>

                </select>

            </div>


            <!-- PENULIS -->

            <div class="form-group">

                <label for="author">

                    Penulis

                </label>


                <input
                    type="text"
                    id="author"
                    name="author"
                    value="<?= htmlspecialchars(
                        $_POST['author']
                        ?? $_SESSION['admin']
                        ?? ''
                    ); ?>"
                    placeholder="Nama penulis"
                    required
                >

            </div>


            <!-- GAMBAR -->

            <div class="form-group">

                <label for="thumbnail">

                    Gambar Artikel

                </label>


                <input
                    type="file"
                    id="thumbnail"
                    name="thumbnail"
                    accept="image/jpeg,image/png,image/webp"
                    required
                >


                <small class="file-info">

                    Format: JPG, JPEG, PNG, WEBP.
                    Maksimal 5 MB.

                </small>

            </div>


            <!-- RINGKASAN -->

            <div class="form-group">

                <label for="excerpt">

                    Ringkasan Artikel

                </label>


                <textarea
                    id="excerpt"
                    name="excerpt"
                    rows="5"
                    placeholder="Tulis ringkasan singkat artikel..."
                    required
                ><?= htmlspecialchars(
                    $_POST['excerpt'] ?? ''
                ); ?></textarea>

            </div>


            <!-- =================================
                 ISI ARTIKEL
            ================================= -->

            <div class="form-group">

                <label>

                    Isi Artikel

                </label>


                <!-- TOOLBAR -->

                <div id="toolbar">


                    <!-- FORMAT -->

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


                    <!-- FONT -->

                    <span class="ql-formats">

                        <select class="ql-font">

                            <option selected>
                                Sans Serif
                            </option>

                            <option value="arial">
                                Arial
                            </option>

                            <option value="georgia">
                                Georgia
                            </option>

                            <option value="times">
                                Times New Roman
                            </option>

                            <option value="verdana">
                                Verdana
                            </option>

                            <option value="trebuchet">
                                Trebuchet MS
                            </option>

                            <option value="courier">
                                Courier New
                            </option>

                        </select>


                        <!-- UKURAN -->

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


                    <!-- FORMAT TEKS -->

                    <span class="ql-formats">

                        <button
                            class="ql-bold"
                            type="button"
                        ></button>


                        <button
                            class="ql-italic"
                            type="button"
                        ></button>


                        <button
                            class="ql-underline"
                            type="button"
                        ></button>


                        <button
                            class="ql-strike"
                            type="button"
                        ></button>

                    </span>


                    <!-- WARNA -->

                    <span class="ql-formats">

                        <select class="ql-color"></select>

                        <select class="ql-background"></select>

                    </span>


                    <!-- LIST -->

                    <span class="ql-formats">

                        <button
                            class="ql-list"
                            value="ordered"
                            type="button"
                        ></button>


                        <button
                            class="ql-list"
                            value="bullet"
                            type="button"
                        ></button>

                    </span>


                    <!-- ALIGN -->

                    <span class="ql-formats">

                        <select class="ql-align"></select>

                    </span>


                    <!-- LINK -->

                    <span class="ql-formats">

                        <button
                            class="ql-blockquote"
                            type="button"
                        ></button>


                        <button
                            class="ql-link"
                            type="button"
                        ></button>

                    </span>


                    <!-- CLEAN -->

                    <span class="ql-formats">

                        <button
                            class="ql-clean"
                            type="button"
                        ></button>

                    </span>

                </div>


                <!-- EDITOR -->

                <div id="editor">

                    <?php

                    if (!empty($_POST['content'])) {

                        echo $_POST['content'];

                    }

                    ?>

                </div>


                <!-- INPUT HIDDEN -->

                <input
                    type="hidden"
                    name="content"
                    id="content-data"
                >

            </div>


            <!-- STATUS -->

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
                            ($_POST['status'] ?? 'published') ===
                            'published'
                        ) ? 'selected' : ''; ?>
                    >

                        Published

                    </option>


                    <option
                        value="draft"
                        <?= (
                            ($_POST['status'] ?? '') ===
                            'draft'
                        ) ? 'selected' : ''; ?>
                    >

                        Draft

                    </option>

                </select>

            </div>


            <!-- BUTTON -->

            <div class="button-group">

                <button
                    type="submit"
                    class="button save"
                >

                    💾 Simpan Artikel

                </button>


                <a
                    href="dashboard.php"
                    class="button cancel"
                >

                    Batal

                </a>

            </div>


        </form>

    </div>

</main>


<!-- =================================
     QUILL JAVASCRIPT
================================= -->

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>


<script>

/* =================================
   FONT CUSTOM
================================= */

const Font = Quill.import('formats/font');


Font.whitelist = [

    'sans-serif',

    'arial',

    'georgia',

    'times',

    'verdana',

    'trebuchet',

    'courier'

];


Quill.register(Font, true);


/* =================================
   BUAT EDITOR
================================= */

const quill = new Quill('#editor', {

    theme: 'snow',

    placeholder:
        'Tulis artikel kamu di sini...',

    modules: {

        toolbar: '#toolbar'

    }

});


/* =================================
   FORM
================================= */

const form =
    document.getElementById('article-form');


const contentData =
    document.getElementById('content-data');


/* =================================
   SUBMIT
================================= */

form.addEventListener(
    'submit',
    function () {

        contentData.value =
            quill.root.innerHTML;

    }
);


/* =================================
   FOKUS EDITOR
================================= */

quill.focus();

</script>


</body>

</html>