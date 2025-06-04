<?php
session_start();
$conn = new mysqli("localhost", "root", "", "blog");

// Pagination setup
$limit = 5;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// Search setup
$search = $_GET['search'] ?? '';
$searchTerm = "%$search%";

// Count total posts
$countQuery = $conn->prepare("SELECT COUNT(*) as total FROM posts WHERE title LIKE ? OR content LIKE ?");
$countQuery->bind_param("ss", $searchTerm, $searchTerm);
$countQuery->execute();
$totalPosts = $countQuery->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalPosts / $limit);

// Fetch posts
$query = $conn->prepare("SELECT * FROM posts WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$query->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
$query->execute();
$posts = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Blog</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h1 class="mb-4">Blog Posts</h1>

  <form method="GET" action="" class="mb-4">
    <div class="input-group">
      <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-primary">Search</button>
    </div>
  </form>

  <?php while ($row = $posts->fetch_assoc()): ?>
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
        <p class="card-text"><?= htmlspecialchars($row['content']) ?></p>
        <small class="text-muted">Posted on <?= $row['created_at'] ?></small>
      </div>
    </div>
  <?php endwhile; ?>

  <!-- Pagination -->
  <nav>
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>
</body>
</html>
