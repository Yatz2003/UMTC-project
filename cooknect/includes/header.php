<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <?php if (strpos($_SERVER['REQUEST_URI'], '/auth/') !== false): ?>
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <?php endif; ?>
    <link rel="icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = event.target.href;
            }
        }
    </script>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="<?php echo SITE_URL; ?>/index.php" class="logo">Cooknect</a>
            
            <nav class="nav-links">
                <a href="<?php echo SITE_URL; ?>/index.php">Home</a>    
                <a href="<?php echo SITE_URL; ?>/search.php">Search</a>
                <a href="<?php echo SITE_URL; ?>/recipes/create.php">Post Recipe</a>
            </nav>
            
            <div class="user-actions">
                <?php if (isLoggedIn()): ?>
                    <div class="user-profile">
                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo getUser()['profile_pic']; ?>" alt="<?php echo $_SESSION['username']; ?>">
                        <a href="<?php echo SITE_URL; ?>/profile.php"><?php echo $_SESSION['username']; ?></a>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="btn btn-outline" onclick="confirmLogout(event)">Logout</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-outline">Login</a>
                    <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    </header>
    
    <main>