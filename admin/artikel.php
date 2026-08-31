<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi.php';


/*
=================================
AMBIL SEMUA ARTIKEL
=================================
*/

$query = "
    SELECT
        id,
        tittle,
        slug,
        category,
        author,
        status,
        created_at
    FROM articles
    ORDER BY created_at DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die(
        "Gagal mengambil data artikel: "
        . mysqli_error($conn)
    );
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
        Kelola Artikel - Native Papuan
    </title>


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


        /* SIDEBAR */

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

            left: 18px;
            right: 18px;
            bottom: 25px;

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


        /* MAIN */

        .main {

            margin-left: 240px;

            padding: 30px;

        }


        .header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;

        }


        .header h1 {

            font-size: 28px;

            margin-bottom: 6px;

        }


        .header p {

            color: #64748b;

        }


        .add-button {

            display: inline-block;

            padding: 12px 18px;

            background: #38bdf8;

            color: #0f172a;

            text-decoration: none;

            font-weight: bold;

            border-radius: 8px;

        }


        .add-button:hover {

            background: #0ea5e9;

        }


        /* TABLE BOX */

        .table-box {

            background: white;

            border-radius: 12px;

            padding: 25px;

            box-shadow:
                0 2px 10px rgba(0,0,0,0.05);

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 750px;

        }


        th {

            text-align: left;

            padding: 14px 10px;

            background: #f8fafc;

            color: #64748b;

            font-size: 13px;

            border-bottom: 1px solid #e2e8f0;

        }


        td {

            padding: 15px 10px;

            border-bottom: 1px solid #e2e8f0;

            font-size: 14px;

        }


        .title {

            font-weight: bold;

            color: #334155;

        }


        .category {

            color: #475569;

        }


        .author {

            color: #64748b;

        }


        .date {

            color: #64748b;

            white-space: nowrap;

        }


        /* STATUS */

        .status {

            display: inline-block;

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

        }


        .published {

            background: #dcfce7;

            color: #166534;

        }


        .draft {

            background: #fef3c7;

            color: #92400e;

        }


        /* EMPTY */

        .empty {

            text-align: center;

            padding: 50px 20px;

            color: #94a3b8;

        }


        .empty-icon {

            font-size: 40px;

            margin-bottom: 10px;

        }


        /* RESPONSIVE */

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


            .logout a {

                font-size: 0;

            }


            .main {

                margin-left: 70px;

                padding: 20px;

            }


            .header {

                display: block;

            }


            .add-button {

                margin-top: 15px;

            }

        }

    </style>

</head>


<body>


<!-- SIDEBAR -->

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
            <a href="edit_artikel.php?id=<?= $article['id']; ?>">
    ✏️ Edit
            </a>
            <a
    href="hapus_artikel.php?id=<?= $article['id']; ?>"
    onclick="return confirm('Yakin ingin menghapus artikel ini?');"
>
    🗑️ Hapus
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



<!-- MAIN -->

<main class="main">


    <div class="header">

        <div>

            <h1>
                Kelola Artikel
            </h1>

            <p>
                Kelola seluruh artikel Native Papuan.
            </p>

        </div>


        <a
            href="tambah_artikel.php"
            class="add-button"
        >

            + Tambah Artikel

        </a>

    </div>



    <div class="table-box">


        <?php if (mysqli_num_rows($result) > 0): ?>


            <table>

                <thead>

                    <tr>

                        <th>
                            No
                        </th>

                        <th>
                            Judul
                        </th>

                        <th>
                            Segmen
                        </th>

                        <th>
                            Penulis
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Tanggal
                        </th>
                        <th>
                            Aksi

                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php

                    $no = 1;

                    while (
                        $article =
                        mysqli_fetch_assoc($result)
                    ):

                    ?>


                        <tr>


                            <td>

                                <?= $no++; ?>

                            </td>


                            <td class="title">

                                <?= htmlspecialchars(
                                    $article['tittle']
                                ); ?>

                            </td>


                            <td class="category">

                                <?= htmlspecialchars(
                                    $article['category']
                                ); ?>

                            </td>


                            <td class="author">

                                <?= htmlspecialchars(
                                    $article['author']
                                ); ?>

                            </td>



                            <td>


                                <?php

                                if (
                                    $article['status']
                                    === 'published'
                                ):

                                ?>

                                    <span
                                        class="status published"
                                    >
                                        Published
                                    </span>


                                <?php else: ?>


                                    <span
                                        class="status draft"
                                    >
                                        Draft
                                    </span>


                                <?php endif; ?>


                            </td>


                            <td class="date">

                                <?= date(
                                    'd M Y',
                                    strtotime(
                                        $article['created_at']
                                    )
                                ); ?>

                            </td>
                            <td>

    <a
        href="edit_artikel.php?id=<?= $article['id']; ?>"
        style="
            display:inline-block;
            padding:7px 10px;
            background:#38bdf8;
            color:#0f172a;
            text-decoration:none;
            border-radius:6px;
            font-size:12px;
            font-weight:bold;
        "
    >
        ✏️ Edit
    </a>

    <a
        href="hapus_artikel.php?id=<?= $article['id']; ?>"
        onclick="return confirm('Yakin ingin menghapus artikel ini?');"
        style="
            display:inline-block;
            padding:7px 10px;
            background:#dc2626;
            color:white;
            text-decoration:none;
            border-radius:6px;
            font-size:12px;
            font-weight:bold;
        "
    >
        🗑️ Hapus
    </a>

</td>


                        </tr>


                    <?php endwhile; ?>


                </tbody>

            </table>


        <?php else: ?>


            <div class="empty">

                <div class="empty-icon">
                    📝
                </div>

                <h3>
                    Belum ada artikel
                </h3>

                <p>
                    Silakan tambahkan artikel pertama kamu.
                </p>

            </div>


        <?php endif; ?>


    </div>


</main>


</body>

</html>