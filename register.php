<?php
require __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$errors = [];
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(input('full_name'));
    $email = strtolower(trim(input('email')));
    $phone = trim(input('phone'));

    
    $password = input('password');
    $confirmation = input('confirm_password');

    if (!valid_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    if ($name === '') {
        $errors[] = 'Full name is required.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = 'Full name must contain 2–100 characters.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($email) > 150
    ) {
        $errors[] = 'Enter a valid email address.';
    }

    $digits = preg_replace('/\D/', '', $phone);

    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    } elseif (
        !preg_match('/^\+?[0-9() .-]{7,30}$/', $phone)
        || strlen($digits) < 7
        || strlen($digits) > 15
    ) {
        $errors[] = 'Enter a phone number with 7–15 digits.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (mb_strlen($password) < 8 || strlen($password) > 72) {
        $errors[] = 'Use at least 8 characters and no more than 72 bytes.';
    }

    if ($confirmation === '') {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirmation) {
        $errors[] = 'Your passwords do not match.';
    }

    if (input('terms') !== '1') {
        $errors[] = 'Please agree to the terms and privacy policy.';
    }

    if (!$errors) {
        try {
            $statement = $pdo->prepare(
                'INSERT INTO users
                 (full_name, email, phone, password_hash)
                 VALUES (?, ?, ?, ?)'
            );

            $statement->execute([
                $name,
                $email,
                $phone,
                password_hash($password, PASSWORD_DEFAULT),
            ]);

            $_SESSION['success'] =
                'Account created successfully. Please sign in.';

            redirect('login.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                $errors[] =
                    'This email is already registered. Please sign in.';
            } else {
                error_log($exception->getMessage());
                $errors[] =
                    'We could not create your account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up | PineScape</title>
    <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__ . "/assets/style.css") ?>">
    <script src="assets/script.js?v=<?= filemtime(__DIR__ . "/assets/script.js") ?>" defer></script>
</head>
<body class="register-page">
<main class="auth-layout">
    <?php adventure(true); ?>

    <section class="form-panel" aria-labelledby="form-title">
        <h1 id="form-title">Sign Up</h1>
        <p class="subtitle">Plan your next escape with PineScape.</p>

        <?php if ($errors): ?>
            <div class="notice error" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= escape($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php">
            <input
                type="hidden"
                name="csrf"
                value="<?= escape(csrf_token()) ?>"
            >

            <?php
            field('Full Name', 'full_name', 'text', 'person', $name, 'name', 100);
            field('Email Address', 'email', 'email', 'email', $email, 'email');
            field('Phone Number', 'phone', 'tel', 'phone', $phone, 'tel', 30);
            field('Password', 'password', 'password', 'lock', '', 'new-password', 72);
            field('Confirm Password', 'confirm_password', 'password', 'lock', '', 'new-password', 72);
            ?>

            <p id="password-help" class="password-help">
                Use at least 8 characters. Maximum 72 bytes.
            </p>

            <label class="check terms">
                <input
                    type="checkbox"
                    name="terms"
                    value="1"
                    required
                    <?= input('terms') === '1' ? 'checked' : '' ?>
                >
                <span>
                    I agree to the
                    <button type="button" class="text-link"
                        data-dialog="terms-dialog">Terms and Conditions</button>
                    and
                    <button type="button" class="text-link"
                        data-dialog="privacy-dialog">Privacy Policy</button>.
                </span>
            </label>

            <button type="submit" class="primary">Create Account</button>
        </form>

        <p class="switch">
            Already have an account?
            <a href="login.php">Sign In</a>
        </p>
    </section>
</main>

<dialog id="terms-dialog">
    <h2>Terms and Conditions</h2>
    <p>This is a student demonstration of account registration and login.
       Keep your password private and use the system responsibly.</p>
    <button type="button" class="primary" data-close>Close</button>
</dialog>

<dialog id="privacy-dialog">
    <h2>Privacy Policy</h2>
    <p>This demonstration stores your name, email, phone number,
       and a hashed password. A session cookie keeps you signed in.</p>
    <button type="button" class="primary" data-close>Close</button>
</dialog>
</body>
</html>