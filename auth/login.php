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

$formHtml = '<form method="POST" class="auth-form" novalidate data-auth-form>
    ' . csrfField() . '
    <div class="auth-field mb-3">
        <label class="form-label" for="email">Email address</label>
        <input type="email" name="email" id="email" class="form-control auth-input" required autocomplete="email" value="' . e($_POST['email'] ?? '') . '" placeholder="you@example.com">
    </div>
    <div class="auth-field mb-3">
        <label class="form-label" for="password">Password</label>
        <div class="auth-password-wrap">
            <input type="password" name="password" id="password" class="form-control auth-input" required autocomplete="current-password" minlength="6" placeholder="Enter your password">
            <button type="button" class="auth-password-toggle" data-password-toggle="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <button type="submit" class="btn btn-gold btn-lg w-100 auth-submit btn-interactive btn-arrow">
        Sign In <i class="bi bi-arrow-right"></i>
    </button>
</form>
<div class="auth-demo-note mt-4">
    <p class="small text-muted mb-1"><strong>Demo accounts</strong></p>
    <p class="small text-muted mb-0">admin@splendidcaterery.com · customer@example.com · Password: <code>password</code></p>
</div>';

echo renderAuthLayout('login', $formHtml, $pdo);
include ROOT_PATH . '/includes/footer.php';
