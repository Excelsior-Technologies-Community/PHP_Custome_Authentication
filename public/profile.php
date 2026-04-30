<?php
require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../core/helpers.php";

$user_id = $_SESSION['custome_id'];
$stmt = $pdo->prepare("SELECT * FROM custome WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if ($name && $email) {
        $stmt = $pdo->prepare("UPDATE custome SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $user_id]);
        $_SESSION['custome_name'] = $name;
        set_flash("Profile updated successfully!");
        header("Location: profile.php");
        exit;
    }
}

require_once "../includes/header.php";
?>

<h2>Edit Profile</h2>
<?php display_flash(); ?>

<form method="post">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>">
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
    </div>
    <button type="submit">Update Profile</button>
    <a href="dashboard.php">Back</a>
</form>

<?php require_once "../includes/footer.php"; ?>