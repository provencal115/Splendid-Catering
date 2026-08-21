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

    $userId = register($pdo, $data);
    if (!$userId) {
        flash('danger', 'Email already registered.');
        redirect(url('auth/register'));
    }

    autoLogin($pdo, $userId);
    flash('success', 'Welcome to Splendid Caterery!');
    redirect(url('dashboard/customer'));
}

$pageTitle = 'Register';
$bodyClass = 'auth-page';
include ROOT_PATH . '/includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="auth-card" data-aos="fade-up">
                <div class="brand-icon"><i class="bi bi-person-plus-fill"></i></div>
                <h3 class="text-center mb-1">Create Account</h3>
                <p class="text-center text-muted mb-4">Join Splendid Caterery to book and manage events</p>
                <form method="POST">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" class="form-control" placeholder="Accra"></div>
                        <div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
                        <div class="col-12"><button type="submit" class="btn btn-gold w-100">Create Account</button></div>
                    </div>
                </form>
                <p class="text-center small text-muted mt-3 mb-0">Already have an account? <a href="<?= url('auth/login') ?>">Sign In</a></p>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
