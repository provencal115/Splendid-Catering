<?php
if (isLoggedIn()) redirect(roleDashboardUrl(currentUser()['role']));

if (isPost()) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flash('danger', 'Invalid request.');
        redirect(url('auth/register'));
    }

    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
    ];

    if (strlen($data['password']) < 6) {
        flash('danger', 'Password must be at least 6 characters.');
        redirect(url('auth/register'));
    }

    if ($data['password'] !== ($_POST['password_confirm'] ?? '')) {
        flash('danger', 'Passwords do not match.');
        redirect(url('auth/register'));
    }

    $userId = register($pdo, $data);
    if (!$userId) {
        flash('danger', 'Email already registered.');
        redirect(url('auth/register'));
    }

    autoLogin($pdo, $userId);
    flash('success', 'Welcome to Splendid Catering!');
    redirect(url('dashboard/customer'));
}

$pageTitle = 'Register';
$bodyClass = 'auth-page';
include ROOT_PATH . '/includes/header.php';

$formHtml = '<form method="POST" class="auth-form" novalidate data-auth-form data-auth-register>
    ' . csrfField() . '
    <div class="row g-3">
        <div class="col-md-6 auth-field">
            <label class="form-label" for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" class="form-control auth-input" required autocomplete="given-name">
        </div>
        <div class="col-md-6 auth-field">
            <label class="form-label" for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" class="form-control auth-input" required autocomplete="family-name">
        </div>
        <div class="col-md-6 auth-field">
            <label class="form-label" for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control auth-input" required autocomplete="email" placeholder="you@example.com">
        </div>
        <div class="col-md-6 auth-field">
            <label class="form-label" for="phone">Phone</label>
            <input type="tel" name="phone" id="phone" class="form-control auth-input" autocomplete="tel" placeholder="+233 XX XXX XXXX">
        </div>
        <div class="col-md-6 auth-field">
            <label class="form-label" for="city">City</label>
            <input type="text" name="city" id="city" class="form-control auth-input" placeholder="Accra">
        </div>
        <div class="col-md-6 auth-field">
            <label class="form-label" for="password">Password</label>
            <div class="auth-password-wrap">
                <input type="password" name="password" id="password" class="form-control auth-input" required minlength="6" autocomplete="new-password" data-password-strength placeholder="Min. 6 characters">
                <button type="button" class="auth-password-toggle" data-password-toggle="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="auth-password-strength" data-strength-bar aria-hidden="true"><span></span></div>
        </div>
        <div class="col-md-12 auth-field">
            <label class="form-label" for="password_confirm">Confirm Password</label>
            <div class="auth-password-wrap">
                <input type="password" name="password_confirm" id="password_confirm" class="form-control auth-input" required minlength="6" autocomplete="new-password" data-password-match="#password" placeholder="Re-enter password">
                <button type="button" class="auth-password-toggle" data-password-toggle="#password_confirm" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
            <div class="auth-field-hint small text-muted" data-match-hint></div>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-gold btn-lg w-100 auth-submit btn-interactive btn-arrow">
                Create Account <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
</form>';

echo renderAuthLayout('register', $formHtml, $pdo);
include ROOT_PATH . '/includes/footer.php';
