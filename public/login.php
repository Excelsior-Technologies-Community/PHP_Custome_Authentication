<?php
session_start();
require_once "../config/database.php";
require_once "../core/helpers.php";

if (isset($_SESSION['custome_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM custome WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $now = date('Y-m-d H:i:s');
        
        if ($user['locked_until'] && $user['locked_until'] > $now) {
            $remaining = strtotime($user['locked_until']) - strtotime($now);
            $error = "Too many failed attempts. Please wait $remaining seconds.";
        } else {
            if (password_verify($pass, $user['password'])) {
                $stmt = $pdo->prepare("UPDATE custome SET login_attempts = 0, locked_until = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);

                $_SESSION['custome_id'] = $user['id'];
                $_SESSION['custome_name'] = $user['name'];
                set_flash("Welcome back, " . $user['name']);
                header("Location: dashboard.php");
                exit;
            } else {
                $attempts = $user['login_attempts'] + 1;
                $lock_time = null;
                
                if ($attempts >= 3) {
                    $lock_time = date('Y-m-d H:i:s', strtotime('+2 minutes'));
                    $error = "Account locked for 2 minutes due to failed attempts.";
                } else {
                    $error = "Invalid credentials. Attempts left: " . (3 - $attempts);
                }

                $stmt = $pdo->prepare("UPDATE custome SET login_attempts = ?, locked_until = ? WHERE id = ?");
                $stmt->execute([$attempts, $lock_time, $user['id']]);
            }
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<?php require_once "../includes/header.php"; ?>
<h2>Login</h2>
<?php display_flash(); ?>
<?php if ($error): ?> <div class="error"><?= $error ?></div> <?php endif; ?>

<form method="post">
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= $_COOKIE['email'] ?? '' ?>">
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password">
    </div>
    <button type="submit">Login</button>
</form>
<?php require_once "../includes/footer.php"; ?>