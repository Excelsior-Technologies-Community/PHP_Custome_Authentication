<?php
// public/users.php

// Include authentication check to ensure only logged-in users can access
require_once "../includes/auth.php";

// Include database connection to fetch user data
require_once "../config/database.php";

// Include helper functions (optional for future use)
require_once "../core/helpers.php";

// Get action from URL query (e.g., softdelete)
$action = $_GET['action'] ?? null;

// Get user ID from URL query and cast to integer
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Handle action: soft delete a user
if ($action && $id) {
  if ($action === 'softdelete') {
    // Update user record to mark as inactive and set deleted_at timestamp
    $stmt = $pdo->prepare(
      "UPDATE custome 
         SET status = 0,
             deleted_at = NOW(),
             updated_by = ?
         WHERE id = ?
           AND deleted_at IS NULL"
    );
    // Execute query with current logged-in user ID and target user ID
    $stmt->execute([$_SESSION['custome_id'], $id]);
  }

  // Redirect back to user list after action
  header("Location: user.php");
  exit;
}

// Fetch list of active users (excluding soft-deleted users)
$stmt = $pdo->query(
  "SELECT id, name, email, status, created_at, updated_at, deleted_at
     FROM custome
     WHERE status = 1 AND deleted_at IS NULL
     ORDER BY id ASC"
);

// Fetch all users as associative array
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php 
// Include header HTML (opening tags, CSS, etc.)
require_once "../includes/header.php"; 
?>

<h2>User List</h2>

<!-- Display users in a table -->
<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Status</th>
      <th>Created</th>
      <th>Updated</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= ($u['status'] ? 'Active' : 'Inactive') ?></td>
        <td><?= $u['created_at'] ?></td>
        <td><?= $u['updated_at'] ?></td>
        <td>
          <!-- Soft delete button -->
          <a class="btn btn-danger"
            href="user.php?action=softdelete&id=<?= $u['id'] ?>"
            onclick="return confirm('Are you sure you want to delete this user?')">
            Delete
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php 
// Include footer HTML (closing tags)
require_once "../includes/footer.php"; 
?>
