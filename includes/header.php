<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'CodeDojo - Learn HTML by Coding'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>&#x1F94B;</text></svg>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <?php if (isset($extraHead) && is_string($extraHead)): ?>
        <?php echo $extraHead; ?>
    <?php endif; ?>
</head>
<body>
    <?php
    // Initialize session and auth functions
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check auth files exist before requiring
    $authFile = dirname(__FILE__) . '/../config/auth.php';
    if (file_exists($authFile)) {
        require_once $authFile;
    }
    ?>
    
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="logo">
                    <span class="material-icons">school</span>
                    <span>CodeDojo</span>
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <!-- Main Navigation -->
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="index.php" class="nav-item <?php echo ($currentPage ?? '') === 'home' ? 'active' : ''; ?>">
                        <span class="material-icons">home</span>
                        <span>Home</span>
                    </a>
                    <a href="editor.php" class="nav-item <?php echo ($currentPage ?? '') === 'editor' ? 'active' : ''; ?>">
                        <span class="material-icons">code</span>
                        <span>Code Editor</span>
                    </a>
                    <a href="lessons.php" class="nav-item <?php echo ($currentPage ?? '') === 'lessons' ? 'active' : ''; ?>">
                        <span class="material-icons">book</span>
                        <span>Lessons</span>
                    </a>
                    <a href="my_practice.php" class="nav-item <?php echo ($currentPage ?? '') === 'practice' ? 'active' : ''; ?>">
                        <span class="material-icons">folder</span>
                        <span>My Practice</span>
                    </a>
                </div>
                
                <!-- Quick Start Templates -->
                <div class="nav-section">
                    <div class="nav-section-title">Quick Start</div>
                    <a href="editor.php?template=button" class="nav-item">
                        <span class="material-icons">smart_button</span>
                        <span>Button</span>
                    </a>
                    <a href="editor.php?template=card" class="nav-item">
                        <span class="material-icons">crop_portrait</span>
                        <span>Card</span>
                    </a>
                    <a href="editor.php?template=form" class="nav-item">
                        <span class="material-icons">edit_note</span>
                        <span>Form</span>
                    </a>
                </div>
                
                <!-- Resources -->
                <div class="nav-section">
                    <div class="nav-section-title">Resources</div>
                    <a href="https://developer.mozilla.org/en-US/docs/Web/HTML" target="_blank" class="nav-item">
                        <span class="material-icons">help</span>
                        <span>HTML Reference</span>
                    </a>
                    <?php if (function_exists('isAdmin') && isAdmin()): ?>
                        <a href="admin/dashboard.php" class="nav-item">
                            <span class="material-icons">admin_panel_settings</span>
                            <span>Admin Panel</span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Authentication -->
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <div style="padding: 12px; background: #f1f5f9; border-radius: 8px; margin-bottom: 12px; font-size: 13px;">
                            <div style="color: #64748b; margin-bottom: 4px;">
                                <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong>
                            </div>
                            <div style="color: #94a3b8; font-size: 11px;">
                                Logged in as <?php echo function_exists('getUserRole') ? htmlspecialchars(getUserRole()) : 'user'; ?>
                            </div>
                        </div>
                        <?php if (function_exists('isUser') && isUser()): ?>
                            <a href="user/dashboard.php" class="nav-item">
                                <span class="material-icons">dashboard</span>
                                <span>My Dashboard</span>
                            </a>
                            <a href="user/logout.php" class="nav-item" style="color: #ff6b6b;">
                                <span class="material-icons">logout</span>
                                <span>Logout</span>
                            </a>
                        <?php elseif (function_exists('isAdmin') && isAdmin()): ?>
                            <a href="admin/dashboard.php" class="nav-item">
                                <span class="material-icons">dashboard</span>
                                <span>Dashboard</span>
                            </a>
                            <a href="admin/logout.php" class="nav-item" style="color: #ff6b6b;">
                                <span class="material-icons">logout</span>
                                <span>Logout</span>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="nav-item">
                            <span class="material-icons">login</span>
                            <span>Login</span>
                        </a>
                        <a href="login.php?register=1" class="nav-item">
                            <span class="material-icons">person_add</span>
                            <span>Sign Up</span>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1><?php echo $pageTitle ?? 'CodeDojo'; ?></h1>
                </div>
                <div class="header-right">
                    <button id="themeToggle" class="theme-toggle" title="Toggle theme">
                        <span class="material-icons">dark_mode</span>
                    </button>
                </div>
            </header>
            
            <!-- Page Content (will be filled by individual pages) -->
