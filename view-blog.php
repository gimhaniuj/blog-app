<?php
/**
 * View Single Blog Page
 * Displays full blog post with author and date
 */

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Get blog ID from URL
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($blog_id <= 0) {
    setFlashMessage('error', 'Invalid blog ID');
    redirect('index.php');
}

// Fetch blog post with author information
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT blogPost.*, user.username 
                        FROM blogPost 
                        INNER JOIN user ON blogPost.user_id = user.id 
                        WHERE blogPost.id = ?");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setFlashMessage('error', 'Blog post not found');
    redirect('index.php');
}

$blog = $result->fetch_assoc();
$stmt->close();
closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($blog['title']); ?> - Word Nest</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">
    <img src="assets/images/logo_png.png" alt="Word Nest Logo" class="logo-image">
    <span class="logo-text">Word Nest</span>
</a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="create-blog.php" class="btn btn-primary btn-sm">Create Blog</a></li>
                    <li><span class="nav-user">Hello, <?php echo e(getUsername()); ?></span></li>
                    <li><a href="logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn btn-secondary btn-sm">Login</a></li>
                    <li><a href="register.php" class="btn btn-primary btn-sm">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container main-content">
        <article class="blog-single">
            <header class="blog-header">
                <h1><?php echo e($blog['title']); ?></h1>
                <div class="blog-meta">
                    <span class="blog-author">
                        <strong>By:</strong> <?php echo e($blog['username']); ?>
                    </span>
                    <span class="blog-date">
                        <strong>Published:</strong> <?php echo formatDateTime($blog['created_at']); ?>
                    </span>
                    <?php if ($blog['created_at'] != $blog['updated_at']): ?>
                        <span class="blog-date">
                            <strong>Updated:</strong> <?php echo formatDateTime($blog['updated_at']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (isOwner($blog['user_id'])): ?>
                    <div class="blog-actions-single">
                        <a href="edit-blog.php?id=<?php echo $blog['id']; ?>" class="btn btn-warning">
                            Edit Post
                        </a>
                        <a href="delete-blog.php?id=<?php echo $blog['id']; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete this blog post?');">
                            Delete Post
                        </a>
                    </div>
                <?php endif; ?>
            </header>
            
            <div class="blog-content">
                <?php echo nl2br(e($blog['content'])); ?>
            </div>
            
            <div class="blog-footer">
                <a href="index.php" class="btn btn-secondary">← Back to All Posts</a>
            </div>
        </article>
    </div>

    <!-- Footer -->
   <footer class="footer">
  <div class="footer-container">

    <div class="footer-right">
      <h4>Connect With Us</h4>
      <ul>
        <li> GitHub: wordnest-dev</li>
        <li> Email: wordnest@gmail.com</li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy 2025 WordNest. All rights reserved.</p>
  </div>
</footer>

    <script src="assets/js/main.js"></script>
</body>
</html>