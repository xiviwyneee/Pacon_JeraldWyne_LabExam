<?php
require __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$statement = $pdo->prepare(
    'SELECT full_name, email, phone FROM users WHERE id = ?'
);
$statement->execute([$_SESSION['user_id']]);
$user = $statement->fetch();

if (!$user) {
    unset($_SESSION['user_id']);
    redirect('login.php');
}

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Account | PineScape</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
<main class="dashboard-layout">
    <section class="form-panel dashboard-panel">
        <img class="dashboard-logo"
            src="assets/images/logo.svg" alt="PineScape">

        <h1>Welcome, <?= escape($user['full_name']) ?>!</h1>
        <p class="subtitle">Your next adventure starts here.</p>

        <?php if ($success): ?>
            <div class="notice success" role="status">
                <?= escape($success) ?>
            </div>
        <?php endif; ?>

        <dl class="account-details">
            <dt>Email Address</dt>
            <dd><?= escape($user['email']) ?></dd>

            <dt>Phone Number</dt>
            <dd><?= escape($user['phone']) ?></dd>
        </dl>

        <form method="post" action="logout.php">
            <input type="hidden" name="csrf"
                value="<?= escape(csrf_token()) ?>">
            <button type="submit" class="primary">Sign Out</button>
        </form>
    </section>
</main>
</body>
</html>