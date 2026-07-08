<?php
include_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full-name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';

    if ($full_name === '' || $email === '' || $phone === '' || $password === '' || $confirm_password === '') {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? OR phone = ?');
        if ($checkStmt) {
            $checkStmt->bind_param('ss', $email, $phone);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $error = 'Email or phone number already exists. Please use different credentials.';
            }
            $checkStmt->close();
        } else {
            $error = 'Unable to verify user details. Please try again later.';
        }
    }

    if ($error === '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $conn->prepare('INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)');
        if ($insertStmt) {
            $role = 'user';
            $insertStmt->bind_param('sssss', $full_name, $email, $phone, $hashedPassword, $role);
            if ($insertStmt->execute()) {
                $insertStmt->close();
                header("Location: login.php");
                exit();
            }
            $error = 'Registration failed. Please try again.';
            $insertStmt->close();
        } else {
            $error = 'Unable to create account. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Ojo Local Government Area Security Incident Reporting System</title>
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
                <h1>Create Your Resident Account</h1>
                <p>Register to report security concerns, share vital information, and stay connected with public safety resources in Ojo LGA.</p>
            </header>

            <form class="auth-form" action="#" method="post" autocomplete="on">
                <?php if ($error !== ''): ?>
                    <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="full-name">Full name</label>
                    <input type="text" id="full-name" name="full-name" placeholder="Adewale Okafor" required>
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" placeholder="resident@example.com" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone number</label>
                    <input type="tel" id="phone" name="phone" inputmode="tel" placeholder="+234 803 000 0000" required>
                </div>

                <div class="form-group password-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Create a secure password" required>
                        <button type="button" class="password-toggle" aria-label="Show password">👁️</button>
                    </div>
                </div>

                <div class="form-group password-group">
                    <label for="confirm-password">Confirm password</label>
                    <div class="password-field">
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Re-enter your password" required>
                        <button type="button" class="password-toggle" aria-label="Show confirm password">👁️</button>
                    </div>
                </div>

                <p class="form-instructions">Use your official contact details so the Ojo LGA safety team can reach you when needed. For urgent emergencies, contact [Official Phone Number: +234 803 520 2065].</p>

                <button type="submit" class="button primary-button auth-submit">Register</button>
            </form>

            <footer class="auth-footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
                <p>Ojo Local Government Area Security Incident Reporting System</p>
            </footer>
        </section>
    </main>

    <script>
        const registerForm = document.querySelector('.auth-form');
        const passwordField = document.querySelector('#password');
        const confirmPasswordField = document.querySelector('#confirm-password');
        const toggleButtons = document.querySelectorAll('.password-toggle');

        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const input = button.previousElementSibling;
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                button.textContent = type === 'password' ? '👁️' : '🙈';
            });
        });

        function validatePasswords() {
            if (!confirmPasswordField.value) {
                confirmPasswordField.setCustomValidity('');
                return;
            }
            if (passwordField.value !== confirmPasswordField.value) {
                confirmPasswordField.setCustomValidity('Passwords do not match.');
            } else {
                confirmPasswordField.setCustomValidity('');
            }
        }

        confirmPasswordField.addEventListener('input', validatePasswords);
        passwordField.addEventListener('input', validatePasswords);

        registerForm.addEventListener('submit', event => {
            validatePasswords();
            if (!registerForm.checkValidity()) {
                event.preventDefault();
                registerForm.reportValidity();
            }
        });
    </script>
</body>
</html>
