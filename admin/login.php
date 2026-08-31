<?php

session_start();

include '../koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = 'Username dan password wajib diisi.';

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, username, password 
             FROM admins 
             WHERE username = ? 
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $username);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $admin = mysqli_fetch_assoc($result);

        if ($admin && password_verify($password, $admin['password'])) {

            $_SESSION['admin'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];

            header("Location: loading.php");
            exit;

        } else {

            $error = 'Username atau password salah.';

        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - Native Papuan</title>
</head>

<body>

    <h1>Native Papuan</h1>
    <h2>Login Admin</h2>

    <?php if ($error): ?>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">

        <input
            type="text"
            name="username"
            placeholder="Username"
            required
        >

        <br><br>

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <br><br>

        <button type="submit">
            Login
        </button>

    </form>

</body>
</html>