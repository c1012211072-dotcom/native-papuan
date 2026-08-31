<?php

session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi.php';

$query_total = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM articles"
);

$data_total = mysqli_fetch_assoc($query_total);

$total_articles = $data_total['total'] ?? 0;

$query_latest = mysqli_query(
    $conn,
    "SELECT id, tittle, author, created_at
     FROM articles
     ORDER BY created_at DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">


    <title>Dashboard Admin - Native Papuan</title>
    

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

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #0f172a;
            color: white;
            padding: 25px 18px;
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
        }

        .menu a:hover,
        .menu a.active {
            background: #38bdf8;
            color: #0f172a;
        }

        .logout {
            position: absolute;
            bottom: 25px;
            left: 18px;
            right: 18px;
        }

        .logout a {
            display: block;
            text-align: center;
            padding: 12px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .main {
            margin-left: 240px;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .topbar h1 {
            font-size: 28px;
        }

        .topbar p {
            margin-top: 5px;
            color: #64748b;
        }

        .admin {
            background: white;
            padding: 12px 18px;
            border-radius: 10px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .card p {
            color: #64748b;
            margin-bottom: 10px;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
        }

        .content {
            background: white;
            padding: 25px;
            border-radius: 12px;
        }

        .content h2 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            color: #64748b;
            font-size: 13px;
        }

        td {
            font-size: 14px;
        }

        .empty {
            color: #94a3b8;
            padding: 20px 0;
        }

        @media (max-width: 800px) {

            .sidebar {
                width: 70px;
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

            .main {
                margin-left: 70px;
            }

            .cards {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

    <aside class="sidebar">

        <div class="logo">
            Native <span>Papuan</span>
        </div>

        <ul class="menu">

            <li>
                <a href="dashboard.php" class="active">
                    🏠 Dashboard
                </a>
            </li>

            <li>
                <a href="tambah_artikel.php">
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


    <main class="main">

        <div class="topbar">

            <div>

                <h1>Dashboard</h1>

                <p>
                    Selamat datang kembali di Admin Native Papuan.
                </p>

            </div>

            <div class="admin">

                👤
                <?= htmlspecialchars($_SESSION['admin']); ?>

            </div>

        </div>


        <div class="cards">

            <div class="card">

                <p>Total Artikel</p>

                <div class="number">
                    <?= $total_articles; ?>
                </div>

            </div>


            <div class="card">

                <p>Status Website</p>

                <div class="number">
                    Aktif
                </div>

            </div>


            <div class="card">

                <p>Role Akun</p>

                <div class="number">
                    Admin
                </div>

            </div>

        </div>


        <div class="content">

            <h2>Artikel Terbaru</h2>

            <?php if ($query_latest && mysqli_num_rows($query_latest) > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Tanggal</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($article = mysqli_fetch_assoc($query_latest)): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($article['tittle']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($article['author']); ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd M Y',
                                        strtotime($article['created_at'])
                                    ); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty">
                    Belum ada artikel.
                </div>

            <?php endif; ?>

        </div>

    </main>

</body>

</html>