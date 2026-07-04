<?php
include_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare('SELECT id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($userId, $userFullName, $userEmail, $userPasswordHash, $userRole);
                $stmt->fetch();
                if (password_verify($password, $userPasswordHash)) {
                    session_start();
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['full_name'] = $userFullName;
                    $_SESSION['email'] = $userEmail;
                    $_SESSION['role'] = $userRole;

                    if ($userRole === 'admin') {
                        header('Location: admin.php');
                    } else {
                        header('Location: dashboard.php');
                    }
                    exit;
                }
            }
            $error = 'Invalid email or password.';
            $stmt->close();
        } else {
            $error = 'Unable to complete login. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ojo Local Government Area Security Incident Reporting System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a href="../index.html" class="logo">Ojo LGA Security</a>
            <nav class="main-nav" aria-label="Primary navigation">
                <ul class="nav-list">
                    <li class="nav-item"><a href="../index.html#features">Features</a></li>
                    <li class="nav-item"><a href="../index.html#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a href="../index.html#emergency">Emergency</a></li>
                    <li class="nav-item"><a href="../index.html#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="auth-page">
        <section class="auth-card">
            <header class="auth-header">
                <a href="../index.html" class="auth-logo">Ojo LGA Security</a>
                <h1>Welcome to Ojo LGA Safety Portal</h1>
                <p>Sign in to access incident reports, public safety updates, and local response tools.</p>
            </header>

            <form class="auth-form" action="#" method="post" autocomplete="on">
                <?php if ($error !== ''): ?>
                    <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="email">E-mail address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="form-group password-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                    </div>
                </div>

                <div class="form-utilities">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="button primary-button auth-submit">Sign In</button>
            </form>

            <footer class="auth-footer">
                <p>Don't have an account? <a href="register.html">Register now</a></p>
                <p>Ojo Local Government Area Security Incident Reporting System</p>
            </footer>
        </section>
    </main>

    <script src="../js/script.js"></script>
</body>
</html>
