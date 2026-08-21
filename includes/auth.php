<?php

declare(strict_types=1);

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function hasRole(string|array $roles): bool
{
    $user = currentUser();
    if (!$user) return false;
    $roles = (array) $roles;
    return in_array($user['role'], $roles, true);
}

function requireAuth(string|array|null $role = null): void
{
    if (!isLoggedIn()) {
        flash('warning', 'Please log in to continue.');
        redirect(url('auth/login?redirect=' . urlencode($_SERVER['REQUEST_URI'])));
    }

    if ($role && !hasRole($role)) {
        flash('danger', 'You do not have permission to access this page.');
        redirect(roleDashboardUrl(currentUser()['role']));
    }
}

function login(PDO $pdo, string $email, string $password): bool
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    unset($user['password_hash']);
    $_SESSION['user'] = $user;

    $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

    return true;
}

function register(PDO $pdo, array $data): int|false
{
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return false;
    }

    $uuid = sprintf('%s-%s-%s-%s-%s',
        bin2hex(random_bytes(4)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),
        bin2hex(random_bytes(6))
    );

    $stmt = $pdo->prepare('
        INSERT INTO users (uuid, first_name, last_name, email, phone, password_hash, role, address, city)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $uuid,
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'] ?? null,
        password_hash($data['password'], PASSWORD_DEFAULT),
        'customer',
        $data['address'] ?? null,
        $data['city'] ?? null,
    ]);

    return (int) $pdo->lastInsertId();
}

function logout(): void
{
    unset($_SESSION['user']);
    session_destroy();
}

function autoLogin(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if ($user) {
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
    }
}
