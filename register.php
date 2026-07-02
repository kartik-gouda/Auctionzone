<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'All fields are required.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $errors[] = 'A user with that email or username already exists.';
        }
    }

    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $passwordHash, 'user']);

        $userId = $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        loginUser($user);
        redirect('/dashboard.php');
    }
}
?>
<?php include 'includes/header.php'; ?>
<main class="container">
    <section class="card">
        <h2>Register</h2>
        <?php if (!empty($errors)): ?>
            <div class="message error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo escape($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" action="<?php echo BASE_URL; ?>/register.php">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="<?php echo escape($username); ?>" required style="color: #000;">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?php echo escape($email); ?>" required style="color: #000;">

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required style="color: #000;">

            <label for="confirm_password">Confirm Password</label>
            <input id="confirm_password" name="confirm_password" type="password" required style="color: #000;">

            <input type="submit" value="Create account">
        </form>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
