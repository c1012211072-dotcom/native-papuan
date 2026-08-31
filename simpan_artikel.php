<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'koneksi.php';

$title = $_POST['title'] ?? '';
$slug = $_POST['slug'] ?? '';
$category = $_POST['category'] ?? '';
$thumbnail = $_POST['thumbnail'] ?? '';
$excerpt = $_POST['excerpt'] ?? '';
$content = $_POST['content'] ?? '';
$author = $_POST['author'] ?? '';
$status = $_POST['status'] ?? '';

$query = "INSERT INTO articles
(tittle, slug, category, thumbnail, excerpt, content, author, status, created_at, updated_at)
VALUES
('$title', '$slug', '$category', '$thumbnail', '$excerpt', '$content', '$author', '$status', NOW(), NOW())";

if (mysqli_query($conn, $query)) {
    echo "<h1>Artikel berhasil disimpan!</h1>";
    echo "<p>Artikel sudah masuk ke database.</p>";
    echo "<a href='tambah_artikel.php'>Tambah artikel lagi</a>";
} else {
    echo "<h1>Gagal menyimpan artikel</h1>";
    echo "<p>" . mysqli_error($conn) . "</p>";
}

?>