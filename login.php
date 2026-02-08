<?php
/**
 * CodeDojo - Unified Login Page
 * Allows users to login as either admin or regular user
 */

session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

// Check if already logged in
if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit;
}

$error = '';
$role = $_GET['role'] ?? 'user'; // Default to user role
$showRegister = ($_GET['register'] ?? null) === '1';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $loginRole = $_POST['role'] ?? 'user';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
        } else {
            // Authenticate based on selected role
            if ($loginRole === 'admin') {
                $userData = authenticateAdmin($username, $password);
            } else {
                $userData = authenticateUser($username, $password);
            }
            
            if ($userData) {
                createUserSession($userData);
                $redirectUrl = ($userData['role'] === 'admin') ? 'admin/dashboard.php' : 'user/dashboard.php';
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        }
        $role = $loginRole;
    }
    
    // Handle registration
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = trim($_POST['reg_username'] ?? '');
        $email = trim($_POST['reg_email'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        $confirmPassword = $_POST['reg_confirm_password'] ?? '';
        $firstName = trim($_POST['reg_first_name'] ?? '');
        $lastName = trim($_POST['reg_last_name'] ?? '');
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Please fill in all required fields';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $result = registerUser($username, $email, $password, $firstName, $lastName);
            if ($result['success']) {
                $error = ''; // Clear error
                $showRegister = false;
                $message = 'Registration successful! Please login with your credentials.';
                // Auto-authenticate the new user
                $userData = authenticateUser($username, $password);
                if ($userData) {
                    createUserSession($userData);
                    header('Location: user/dashboard.php');
                    exit;
                }
            } else {
                $error = $result['message'];
            }
        }
    }
}

$timeout = $_GET['timeout'] ?? '0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $showRegister ? 'Register' : 'Login'; ?> - CodeDojo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
        }
        
        .role-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
        }
        
        .role-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .role-btn.active {
            background: #667eea;
            color: white;
        }
        
        .role-btn:hover {
            background: #667eea;
            color: white;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-row .form-group {
            margin-bottom: 0;
        }
        
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #5568d3;
        }
        
        .submit-btn:active {
            transform: scale(0.98);
        }
        
        .auth-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .auth-footer p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .auth-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .login-form, .register-form {
            display: none;
        }
        
        .login-form.active, .register-form.active {
            display: block;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .logo-section h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .logo-section p {
            color: #64748b;
            font-size: 14px;
        }
        
        .timeout-warning {
            background: #fef3c7;
            color: #92400e;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-divider {
            margin: 24px 0;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }
        
        .default-creds {
            background: #f0f4ff;
            padding: 12px;
            border-radius: 8px;
            margin-top: 16px;
            font-size: 12px;
            color: #475569;
            line-height: 1.6;
        }
        
        .default-creds strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="logo-section">
                <div style="font-size: 48px; margin-bottom: 12px;">🥋</div>
                <h1>CodeDojo</h1>
                <p><?php echo $showRegister ? 'Create your account' : 'Sign in to your account'; ?></p>
            </div>
            
            <?php if ($timeout === '1'): ?>
                <div class="timeout-warning">
                    <span class="material-icons" style="font-size: 18px;">schedule</span>
                    Session expired. Please login again.
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <span class="material-icons" style="font-size: 18px;">error</span>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($message) && !$error): ?>
                <div class="success-message">
                    <span class="material-icons" style="font-size: 18px;">check_circle</span>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <div class="login-form <?php echo !$showRegister ? 'active' : ''; ?>">
                <div class="role-selector">
                    <button type="button" class="role-btn <?php echo $role === 'admin' ? 'active' : ''; ?>" data-role="admin">
                        <span class="material-icons" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">admin_panel_settings</span>
                        Admin
                    </button>
                    <button type="button" class="role-btn <?php echo $role === 'user' ? 'active' : ''; ?>" data-role="user">
                        <span class="material-icons" style="font-size: 16px; vertical-align: middle; margin-right: 4px;">person</span>
                        User
                    </button>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="role" id="loginRole" value="<?php echo htmlspecialchars($role); ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="Enter your username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <span class="material-icons">login</span>
                        Sign In
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Don't have an account?</p>
                    <a onclick="toggleForms()" style="cursor: pointer;">Create one now</a>
                </div>
                
                <div class="default-creds">
                    <strong style="display: block; margin-bottom: 6px;">Demo Credentials:</strong>
                    <strong>Admin:</strong> admin / codedojo123<br>
                    <strong>User:</strong> user / user123
                </div>
            </div>
            
            <!-- Register Form -->
            <div class="register-form <?php echo $showRegister ? 'active' : ''; ?>">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg_first_name">First Name</label>
                            <input type="text" id="reg_first_name" name="reg_first_name" placeholder="First name">
                        </div>
                        <div class="form-group">
                            <label for="reg_last_name">Last Name</label>
                            <input type="text" id="reg_last_name" name="reg_last_name" placeholder="Last name">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_username">Username</label>
                        <input type="text" id="reg_username" name="reg_username" required placeholder="Choose a username">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_email">Email Address</label>
                        <input type="email" id="reg_email" name="reg_email" required placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_password">Password</label>
                        <input type="password" id="reg_password" name="reg_password" required placeholder="Create a password (min 6 characters)">
                    </div>
                    
                    <div class="form-group">
                        <label for="reg_confirm_password">Confirm Password</label>
                        <input type="password" id="reg_confirm_password" name="reg_confirm_password" required placeholder="Confirm your password">
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <span class="material-icons">person_add</span>
                        Create Account
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account?</p>
                    <a onclick="toggleForms()" style="cursor: pointer;">Sign in here</a>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" style="color: #667eea; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-icons" style="font-size: 16px;">arrow_back</span>
                    Back to Homepage
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Role selector for login
        document.querySelectorAll('[data-role]').forEach(btn => {
            btn.addEventListener('click', function() {
                const role = this.dataset.role;
                document.querySelectorAll('[data-role]').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('loginRole').value = role;
            });
        });
        
        // Toggle between login and register forms
        function toggleForms() {
            document.querySelector('.login-form').classList.toggle('active');
            document.querySelector('.register-form').classList.toggle('active');
        }
    </script>
</body>
</html>
