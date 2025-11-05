<?php
/**
 * Edit Blog Page
 * Allows users to edit their own blog posts
 */

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require authentication
requireLogin();

// Get blog ID from URL
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($blog_id <= 0) {
    setFlashMessage('error', 'Invalid blog ID');
    redirect('index.php');
}

$errors = [];
$conn = getDBConnection();

// Fetch blog post
$stmt = $conn->prepare("SELECT * FROM blogPost WHERE id = ?");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setFlashMessage('error', 'Blog post not found');
    redirect('index.php');
}

$blog = $result->fetch_assoc();
$stmt->close();

// Check if user owns this blog
if (!isOwner($blog['user_id'])) {
    setFlashMessage('error', 'You are not authorized to edit this blog post');
    redirect('index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    
    // Validation
    if (empty($title)) {
        $errors[] = "Title is required";
    } elseif (strlen($title) > 255) {
        $errors[] = "Title must not exceed 255 characters";
    }
    
    if (empty($content)) {
        $errors[] = "Content is required";
    } elseif (strlen($content) < 50) {
        $errors[] = "Content must be at least 50 characters";
    }
    
    // Update blog post if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE blogPost SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $user_id = getUserId();
        $stmt->bind_param("ssii", $title, $content, $blog_id, $user_id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Blog post updated successfully!');
            redirect('view-blog.php?id=' . $blog_id);
        } else {
            $errors[] = "Failed to update blog post. Please try again.";
        }
        
        $stmt->close();
    }
}

closeDBConnection($conn);

// Use POST data if available, otherwise use database values
$title = $_POST['title'] ?? $blog['title'];
$content = $_POST['content'] ?? $blog['content'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog - Blog App</title>
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
                <li><a href="create-blog.php">Create Blog</a></li>
                <li><span class="nav-user">Hello, <?php echo e(getUsername()); ?></span></li>
                <li><a href="logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container main-content">
        <div class="editor-container">
            <h1>Edit Blog Post</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="edit-blog.php?id=<?php echo $blog_id; ?>" class="blog-form" id="blogForm">
                <div class="form-group">
                    <label for="title">Blog Title</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="<?php echo e($title); ?>"
                        placeholder="Enter an engaging title..."
                        required
                        maxlength="255">
                    <small id="titleCount">0 / 255 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="content">Blog Content</label>
                    <textarea 
                        id="content" 
                        name="content" 
                        rows="15"
                        placeholder="Write your blog content here... (Minimum 50 characters)"
                        required><?php echo e($content); ?></textarea>
                    <small id="contentCount">0 characters (minimum 50)</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Post</button>
                    <a href="view-blog.php?id=<?php echo $blog_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 BlogApp. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>