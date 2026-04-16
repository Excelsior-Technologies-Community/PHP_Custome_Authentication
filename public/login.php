<?php
session_start();
require_once "../config/database.php";
require_once "../core/helpers.php";

// ✅ Auto redirect if already logged in
if (isset($_SESSION['custome_id'])) {
  header("Location: dashboard.php");
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';

  if ($email === '' || $pass === '') {
    $error = "Both fields are required.";
  } else {
    $stmt = $pdo->prepare(
      "SELECT * FROM custome WHERE email = ? AND status = 1 AND deleted_at IS NULL LIMIT 1"
    );
    $stmt->execute([$email]);
    $custome = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($custome && password_verify($pass, $custome['password'])) {

      $_SESSION['custome_id'] = $custome['id'];
      $_SESSION['custome_name'] = $custome['name'];

      session_regenerate_id(true);

      // ✅ Remember Me (FIXED 🔥)
      if (isset($_POST['remember'])) {
        setcookie("email", $email, time() + (86400 * 7), "/");
      } else {
        setcookie("email", "", time() - 3600, "/");
      }

      header("Location: dashboard.php");
      exit;
    } else {
      $error = "Invalid credentials or inactive account.";
    }
  }
}
?>

<?php require_once "../includes/header.php"; ?>

<h2>Login</h2>

<?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="">

  <div class="form-group">
    <label>Email</label>
    <!-- ✅ Remember Me value -->
    <input type="email" name="email"
      value="<?= $_COOKIE['email'] ?? '' ?>">
  </div>

  <div class="form-group">
    <label>Password</label>
    <!-- ✅ Show/Hide Password -->
    <input type="password" id="password" name="password">
    <input type="checkbox" onclick="togglePassword()"> Show Password
  </div>

  <!-- ✅ Remember Me -->
  <div class="form-group">
    <input type="checkbox" name="remember"> Remember Me
  </div>

  <button type="submit">Login</button>
</form>

<!-- ✅ FIXED LINK 🔥 -->
<p class="link small">
  Don't have an account?
  <a href="/PHP_Custome_Authentication/public/register.php">Register</a>
</p>

<script>
function togglePassword() {
  let pass = document.getElementById("password");
  pass.type = (pass.type === "password") ? "text" : "password";
}
</script>

<?php require_once "../includes/footer.php"; ?>