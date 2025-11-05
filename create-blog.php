<?php
/**
 * Create Blog Page
 * Allows authenticated users to create new blog posts
 */

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Require authentication
requireLogin();

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $content = $_POST['content'] ?? ''; // Don't sanitize content yet, allow formatting
    $user_id = getUserId();
    
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
    
    // Create blog post if no errors
    if (empty($errors)) {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("INSERT INTO blogPost (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $content);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Blog post created successfully!');
            redirect('index.php');
        } else {
            $errors[] = "Failed to create blog post. Please try again.";
        }
        
        $stmt->close();
        closeDBConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog - Word Nest</title>
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
                <li><span class="nav-user">Hello, <?php echo e(getUsername()); ?></span></li>
                <li><a href="index.php">Home</a></li>
                <li><a href="create-blog.php" class="active">Create Blog</a></li>
                <li><a href="logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container main-content">
        <div class="editor-container">
            <h1>Write Something New</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="create-blog.php" class="blog-form" id="blogForm">
                <div class="form-group">
                    <label for="title">Blog Title</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="<?php echo e($_POST['title'] ?? ''); ?>"
                        placeholder="Enter title here"
                        required
                        maxlength="200">
                    <small id="titleCount">0 / 200 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea 
                        id="content" 
                        name="content" 
                        rows="15"
                        placeholder="Write your blog content here... (Minimum 50 characters)"
                        required><?php echo e($_POST['content'] ?? ''); ?></textarea>
                    <small id="contentCount">0 characters (minimum 50)</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Publish Post</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 WordNest. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>