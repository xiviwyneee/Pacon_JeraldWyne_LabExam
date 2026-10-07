<?php
declare(strict_types=1);

header("Cache-Control: no-store, max-age=0");

ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);

session_start();


$host = 'localhost';
$database = 'pinescape';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check your database settings.');
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function input(string $name): string
{
    return isset($_POST[$name]) && is_string($_POST[$name])
        ? $_POST[$name]
        : '';
}

function redirect(string $page): never
{
    header('Location: ' . $page);
    exit;
}

function csrf_token(): string
{
    return $_SESSION['csrf']
        ??= bin2hex(random_bytes(32));
}

function valid_csrf(): bool
{
    return hash_equals(csrf_token(), input('csrf'));
}

function icon(string $name): string
{
    $paths = [
        'email' => '<rect x="3" y="5" width="18" height="14" rx="2"/>
            <path d="m4 6 8 6 8-6" fill="none"
            stroke="#284332" stroke-width="2"/>',

        'person' => '<circle cx="12" cy="7" r="4"/>
            <path d="M4 22v-3a8 8 0 0 1 16 0v3z"/>',

        'phone' => '<path d="m5 2 5 5-3 3c2 4 3 5 7 7l3-3
            5 5c-2 5-6 4-11 1C6 17 2 12 2 7c0-2 1-4 3-5z"/>',

        'lock' => '<rect x="5" y="10" width="14" height="12" rx="2"/>
            <path d="M8 11V7a4 4 0 0 1 8 0v4" fill="none"
            stroke="currentColor" stroke-width="2.5"/>
            <circle cx="12" cy="15" r="1.5" fill="#284332"/>
            <path d="M12 15v4" stroke="#284332" stroke-width="2"/>',

        'mountain' => '<path d="m1 20 8-15 5 8 3-5 7 12z"/>
            <path d="m7 9 2 3 2-3" stroke="#284332"
            fill="none" stroke-width="2"/>',

        'trail' => '<path d="M3 2h9L7 6H3zm8 5c20 5 5 10-2
            13h15v4H0c0-9 21-10 11-17z"/>',

        'hike' => '<circle cx="13" cy="3" r="2.5"/>
            <path d="m10 8-3 7 5 3-4 6m2-16 5 5 4 1
            m-7-5 2 11 3 4M5 7v7m16-7v17" fill="none"
            stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round"/>',
    ];

    return '<svg viewBox="0 0 24 26" aria-hidden="true"
        fill="currentColor">'
        . ($paths[$name] ?? '')
        . '</svg>';
}

function field(
    string $label,
    string $name,
    string $type,
    string $symbol,
    string $value = '',
    string $autocomplete = '',
    int $maximum = 150
): void {
    ?>
    <div class="field">
        <label for="<?= escape($name) ?>">
            <?= escape($label) ?>
        </label>

        <div class="input-wrap">
            <?= icon($symbol) ?>

            <input
                id="<?= escape($name) ?>"
                name="<?= escape($name) ?>"
                type="<?= escape($type) ?>"
                placeholder="<?= escape($label) ?>"
                value="<?= escape($value) ?>"
                autocomplete="<?= escape($autocomplete) ?>"
                <?= $name === "password" && $autocomplete === "new-password" ? 'aria-describedby="password-help" title="Use at least 8 characters, up to 72 bytes."' : '' ?>
                maxlength="<?= $maximum ?>"
                required
            >

            <?php if ($type === 'password'): ?>
                <button
                    type="button"
                    class="password-toggle"
                    data-password="<?= escape($name) ?>"
                    aria-label="Show <?= escape(strtolower($label)) ?>"
                    aria-pressed="false"
                >
                    <svg class="eye-icon" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true" focusable="false">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                        <path class="eye-slash" d="M3 3 21 21" />
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function adventure(bool $registration = false): void
{
    ?>
    <section class="adventure">
        <img
            class="forest" width="1054" height="792"
            src="assets/images/<?= $registration ? 'signup' : 'signin' ?>-picture.png"
            alt=""
        >

        <h2>
            <?= $registration
                ? 'Create an Account'
                : 'Begin your Adventure' ?>
        </h2>

        <div class="tagline">
            <span>
                <?= $registration
                    ? 'JOIN THE ADVENTURE'
                    : 'THE MOUNTAINS ARE CALLING' ?>
            </span>
        </div>

        <p class="hero-description">
            <?= $registration
                ? 'Start your journey with us and discover trails, peaks,<br>
                   and unforgettable outdoor experiences.'
                : 'Continue your journey and discover where the trail takes you next.' ?>
        </p>

        <div class="features">
            <div>
                <span class="feature-icon"><img src="assets/images/adventurer.svg" alt=""></span>
                <p>Guided<br>Trails</p>
            </div>

            <div>
                <span class="feature-icon"><img src="assets/images/mountain.svg" alt=""></span>
                <p>Mountain<br>Climbs</p>
            </div>

            <div>
                <span class="feature-icon"><img src="assets/images/trail.svg" alt=""></span>
                <p>Outdoor<br>Adventures</p>
            </div>
        </div>

        <p class="motto">EXPLORE · CLIMB · DISCOVER · CONQUER</p>
    </section>
    <?php
}