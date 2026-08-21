<?php
$pageTitle = 'Setup';
$step = $_GET['step'] ?? 'check';
$message = '';
$success = false;

if (isPost() && isset($_POST['setup_db'])) {
    try {
        $dbConfig = require ROOT_PATH . '/config/database.php';
        $pdoSetup = new PDO(
            "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4",
            $dbConfig['username'],
            $dbConfig['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $schema = file_get_contents(ROOT_PATH . '/database/schema.sql');
        $pdoSetup->exec($schema);

        $seed = file_get_contents(ROOT_PATH . '/database/seed.sql');
        $pdoSetup->exec($seed);

        $success = true;
        $message = 'Database installed successfully! You can now use the platform.';
    } catch (Exception $e) {
        $message = 'Setup failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Splendid Caterery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body class="auth-page">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="auth-card">
                <div class="brand-icon"><i class="bi bi-database-fill-gear"></i></div>
                <h3 class="text-center">Splendid Caterery Setup</h3>
                <?php if ($message): ?>
                <div class="alert alert-<?= $success ? 'success' : 'danger' ?>"><?= e($message) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                <a href="<?= url() ?>" class="btn btn-gold w-100">Go to Website</a>
                <a href="<?= url('auth/login') ?>" class="btn btn-outline-secondary w-100 mt-2">Login</a>
                <?php else: ?>
                <p class="text-muted text-center">This will create the database and seed demo data.</p>
                <form method="POST">
                    <button type="submit" name="setup_db" class="btn btn-gold w-100">Install Database</button>
                </form>
                <hr>
                <p class="small text-muted"><strong>Requirements:</strong> PHP 8+, MySQL 8+, PDO extension</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
