<?php
if (isLoggedIn()) redirect(roleDashboardUrl(currentUser()['role']));

if (isPost()) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flash('danger', 'Invalid request.');
        redirect(url('auth/login'));
    }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login($pdo, $email, $password)) {
        $redirect = $_GET['redirect'] ?? roleDashboardUrl(currentUser()['role']);
        flash('success', 'Welcome back, ' . currentUser()['first_name'] . '!');
        redirect($redirect);
    }
    flash('danger', 'Invalid email or password.');
}

$pageTitle = 'Login';
$bodyClass = 'auth-page';
include ROOT_PATH . '/includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="auth-card" data-aos="fade-up">
                <div class="brand-icon"><i class="bi bi-cup-hot-fill"></i></div>
                <h3 class="text-center mb-1">Welcome Back</h3>
                <p class="text-center text-muted mb-4">Sign in to your Splendid Caterery account</p>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-gold w-100 mb-3">Sign In</button>
                </form>
                <p class="text-center small text-muted mb-0">Don't have an account? <a href="<?= url('auth/register') ?>">Register</a></p>
                <hr>
                <p class="small text-muted mb-1"><strong>Demo Accounts:</strong></p>
                <p class="small text-muted mb-0">admin@splendidcaterery.com · customer@example.com · events@splendidcaterery.com<br>Password: <code>password</code></p>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
