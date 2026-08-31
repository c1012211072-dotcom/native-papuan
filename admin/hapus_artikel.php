<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi.php';


/*
=================================
CEK ID ARTIKEL
=================================
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: artikel.php");
    exit;
}

$id = (int) $_GET['id'];


/*
=================================
AMBIL DATA ARTIKEL
=================================
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT thumbnail
     FROM articles
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$article = mysqli_fetch_assoc($result);


/*
=================================
JIKA ARTIKEL TIDAK DITEMUKAN
=================================
*/

if (!$article) {
    header("Location: artikel.php");
    exit;
}


/*
=================================
HAPUS GAMBAR
=================================
*/

if (!empty($article['thumbnail'])) {

    $imagePath = '../' . $article['thumbnail'];

    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}


/*
=================================
HAPUS ARTIKEL DARI DATABASE
=================================
*/

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM articles
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);


/*
=================================
KEMBALI KE KELOLA ARTIKEL
=================================
*/

header("Location: artikel.php");
exit;

?>