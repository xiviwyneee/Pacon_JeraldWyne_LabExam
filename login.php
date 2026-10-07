<?php
require __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim(input('email')));
    $password = input('password');

    if (!valid_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 150
    ) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) > 72) {
        $errors[] = 'Invalid email or password.';
    }

    
    $now = time();
    $_SESSION['attempts'] = array_values(array_filter(
        $_SESSION['attempts'] ?? [],
        fn($timestamp) => $timestamp > $now - 300
    ));

    if (count($_SESSION['attempts']) >= 5) {
        $errors[] = 'Too many attempts. Please wait five minutes.';
    }

    if (!$errors) {
        $statement = $pdo->prepare(
            'SELECT id, password_hash FROM users WHERE email = ?'
        );
        $statement->execute([$email]);
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['attempts'] = [];
            $_SESSION['success'] = 'You have signed in successfully.';

            redirect('dashboard.php');
        }

        $_SESSION['attempts'][] = $now;
        $errors[] = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In | PineScape</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__ . "/assets/style.css") ?>">
    <script src="assets/script.js?v=<?= filemtime(__DIR__ . "/assets/script.js") ?>" defer></script>
</head>
<body class="login-page">
<main class="auth-layout">
    <section class="form-panel" aria-labelledby="form-title">
        <a class="brand" href="login.php">
            <img src="assets/images/logo.svg" alt="PineScape">
        </a>

        <h1 id="form-title">Welcome Back</h1>
        <p class="subtitle">Continue your journey with PineScape.</p>

        <?php if ($success): ?>
            <div class="notice success" role="status">
                <?= escape($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="notice error" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= escape($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" id="login-form">
            <input
                type="hidden"
                name="csrf"
                value="<?= escape(csrf_token()) ?>"
            >

            <?php
            field('Email Address', 'email', 'email', 'email', $email, 'email');
            field('Password', 'password', 'password', 'lock', '', 'current-password', 72);
            ?>

            <div class="form-options">
                <label class="check">
                    <input type="checkbox" id="remember-email">
                    Remember email
                </label>

                <button type="button" class="text-link underlined"
                    data-dialog="recovery-dialog">Forgot Password?</button>
            </div>

            <button type="submit" class="primary">Sign In</button>

            <div class="divider"><span>OR</span></div>

            <a href="register.php" class="secondary">Sign Up</a>
        </form>

        <p class="switch">
            Don’t have an account?
            <a href="register.php">Create Account</a>
        </p>
    </section>

    <?php adventure(); ?>
</main>

<dialog id="recovery-dialog">
    <h2>Password Recovery</h2>
    <p>Password recovery is not configured for this student demonstration.
       Contact the project administrator for assistance.</p>
    <button type="button" class="primary" data-close>Close</button>
</dialog>
</body>
</html>