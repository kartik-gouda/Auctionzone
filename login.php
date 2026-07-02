<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'Invalid email or password.';
        } else {
            loginUser($user);
            redirect('/dashboard.php');
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<main class="container">
    <section class="card">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="message error"><?php echo escape($error); ?></div>
        <?php endif; ?>
        <form method="post" action="<?php echo BASE_URL; ?>/login.php">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?php echo escape($email); ?>" required style="color: #000;">

            <label for="password">Password</label>
            <input id="password" name="password" type="password" required style="color: #000;">

            <input type="submit" value="Login">
        </form>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
