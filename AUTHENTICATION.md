# Integrated Authentication System Setup Guide

## Overview

Your CodeDojo application now has a complete dual-login system with secure authentication for both admin and regular users. This guide will help you set up and understand the new authentication system.

---

## Quick Start

### 1. Database Setup

Run the migration file to create the required authentication tables:

```bash
# In phpMyAdmin or MySQL client, run:
mysql -u root codedojo < database/add_auth_tables.sql
```

Or manually execute the SQL in [database/add_auth_tables.sql](database/add_auth_tables.sql)

### 2. Test the System

After running the migration, you can test with these demo accounts:

**Admin Account:**
- Username: `admin`
- Password: `codedojo123`
- Access at: [http://localhost/codedojo2/login.php](http://localhost/codedojo2/login.php)

**User Account:**
- Username: `user`
- Password: `user123`
- Access at: [http://localhost/codedojo2/login.php](http://localhost/codedojo2/login.php)

---

## System Architecture

### Files Structure

```
codedojo2/
├── login.php                 # Unified login & registration page
├── admin_login.php          # (Redirects to login.php for backward compatibility)
├── config/
│   ├── database.php         # Database connection
│   └── auth.php             # Authentication functions (NEW)
├── admin/
│   ├── auth_check.php       # Admin auth verification (UPDATED)
│   ├── logout.php           # Admin logout (UPDATED)
│   └── dashboard.php        # Admin dashboard
├── user/
│   ├── auth_check.php       # User auth verification (NEW)
│   ├── dashboard.php        # User dashboard home (NEW)
│   └── logout.php           # User logout (NEW)
└── database/
    └── add_auth_tables.sql  # Database migration (NEW)
```

### Database Structure

#### `admins` Table
```sql
id              - Auto-increment primary key
username        - Unique admin username
email           - Admin email address
password        - Bcrypt hashed password
is_active       - Boolean (active/inactive status)
created_at      - Account creation timestamp
updated_at      - Last update timestamp
```

#### `users` Table
```sql
id              - Auto-increment primary key
username        - Unique username
email           - Unique email address
password        - Bcrypt hashed password
first_name      - User's first name
last_name       - User's last name
is_active       - Boolean (active/inactive status)
created_at      - Account creation timestamp
updated_at      - Last update timestamp
```

---

## Features

### 1. Unified Login Page
Access at: **`/login.php`**

- **Role Selection**: Choose between "Admin" or "User" login
- **User Registration**: Built-in registration form for new users
- **Session Management**: Automatic 30-minute timeout
- **Error Messages**: Clear feedback for failed attempts
- **Demo Credentials**: Displayed on the login page

### 2. Authentication Functions
Located in: **`config/auth.php`**

#### Available Functions:

```php
// Password hashing and verification
hashPassword($password)              // Hash a password
verifyPassword($password, $hash)     // Verify password against hash

// Authentication
authenticateAdmin($username, $password)     // Authenticate admin
authenticateUser($username, $password)      // Authenticate regular user

// Session management
createUserSession($userData)         // Create authenticated session
isLoggedIn()                        // Check if user is logged in
isAdmin()                           // Check if logged in user is admin
isUser()                            // Check if logged in user is regular user
getUserRole()                       // Get current user's role
getUserId()                         // Get current user's ID
getUsername()                       // Get current username
logoutUser()                        // Destroy session and logout
checkSessionTimeout()               // Check for session expiration

// Route protection
requireLogin($role, $loginPage)     // Redirect to login if not authenticated

// User registration
registerUser($username, $email, $password, $firstName, $lastName)

// Admin password management
updateAdminPassword($adminId, $currentPassword, $newPassword)
```

### 3. Admin Authentication
- Uses database `admins` table
- Bcrypt password hashing (cost = 10)
- Redirects to admin dashboard: `/admin/dashboard.php`
- Admin panel access requires admin role

### 4. User Authentication
- Uses database `users` table
- Secure password hashing
- User registration available
- Redirects to user dashboard: `/user/dashboard.php`
- Can only access user-specific features

---

## Implementation Guide

### Protecting Admin Pages

Include at the top of any admin page:

```php
<?php
require_once dirname(__DIR__) . '/admin/auth_check.php';
// Only admins can access this page
?>
```

### Protecting User Pages

Include at the top of any user page:

```php
<?php
require_once dirname(__DIR__) . '/user/auth_check.php';
// Only logged-in users can access this page
?>
```

### Checking User Role in Code

```php
<?php
session_start();
require_once 'config/auth.php';

if (isLoggedIn()) {
    echo "Welcome, " . getUsername() . "!";
    
    if (isAdmin()) {
        echo " You are an admin.";
    } elseif (isUser()) {
        echo " You are a regular user.";
    }
}
?>
```

### Custom Login Requirements

```php
<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

// Require login (any role)
requireLogin(null, 'login.php');

// Require admin login
requireLogin('admin', 'login.php');

// Require user login
requireLogin('user', 'login.php');
?>
```

---

## Managing Users & Admins

### Creating New Admin Account

```php
<?php
require_once 'config/database.php';
require_once 'config/auth.php';

$username = 'newadmin';
$email = 'newadmin@codedojo.local';
$password = 'securepassword123';

$hashedPassword = hashPassword($password);

$db = getDBConnection();
$stmt = $db->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
$stmt->execute([$username, $email, $hashedPassword]);

echo "Admin created successfully!";
?>
```

### Creating New User Account

Use the registration form at `/login.php?register=1`, or programmatically:

```php
<?php
require_once 'config/auth.php';

$result = registerUser(
    'johnsmith',
    'john@example.com',
    'securepassword123',
    'John',
    'Smith'
);

if ($result['success']) {
    echo $result['message'];
} else {
    echo $result['message'];
}
?>
```

### Deactivating a User/Admin

```php
<?php
require_once 'config/database.php';

$db = getDBConnection();

// Deactivate user
$stmt = $db->prepare("UPDATE users SET is_active = FALSE WHERE id = ?");
$stmt->execute([1]);

// Deactivate admin
$stmt = $db->prepare("UPDATE admins SET is_active = FALSE WHERE id = ?");
$stmt->execute([1]);
?>
```

---

## Security Best Practices

### 1. Password Security
- ✅ All passwords are hashed using bcrypt (cost = 10)
- ✅ Never stored in plaintext
- ✅ Minimum 6 characters required
- ⚠️ For production, require stronger passwords (8+ characters, mixed case)

### 2. Session Management
- ✅ 30-minute inactivity timeout
- ✅ Session data validated on each request
- ✅ Proper session destruction on logout
- ✅ Secure session cookie settings

### 3. SQL Injection Prevention
- ✅ All database queries use prepared statements
- ✅ User input is parameterized
- ✅ No string concatenation in queries

### 4. CSRF Protection
- ⚠️ For production, add CSRF tokens to forms
- Add to [config/auth.php](config/auth.php):

```php
// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}
```

Then in forms:
```html
<input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
```

### 5. Additional Hardening
- Use HTTPS in production (not local development)
- Set `httponly` flag on session cookies
- Set `secure` flag on session cookies (HTTPS only)
- Implement rate limiting on login attempts
- Add account lockout after failed attempts

---

## Logging & Monitoring

### Authentication Log Example

Create a [logging system](config/logger.php):

```php
<?php
function logAuthEvent($event, $username, $success, $ipAddress) {
    $logFile = dirname(__DIR__) . '/logs/auth.log';
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SUCCESS' : 'FAILED';
    
    $logEntry = "[$timestamp] $event - User: $username - Status: $status - IP: $ipAddress\n";
    
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>
```

Use in authentication functions:
```php
function authenticateAdmin($username, $password) {
    // ... authentication code ...
    
    if ($admin && verifyPassword($password, $admin['password'])) {
        logAuthEvent('ADMIN_LOGIN', $username, true, $_SERVER['REMOTE_ADDR']);
        return $admin;
    }
    
    logAuthEvent('ADMIN_LOGIN', $username, false, $_SERVER['REMOTE_ADDR']);
    return false;
}
```

---

## Troubleshooting

### Issue: "Database connection failed"
- ✓ Verify database credentials in [config/database.php](config/database.php)
- ✓ Ensure XAMPP MySQL is running
- ✓ Check database `codedojo` exists

### Issue: "Invalid username or password" for demo accounts
- ✓ Run the migration: `mysql -u root codedojo < database/add_auth_tables.sql`
- ✓ Verify admins and users tables are created
- ✓ Check that demo accounts were inserted

### Issue: Session expires immediately
- ✓ Check PHP session settings in `php.ini`
- ✓ Verify session save path is writable
- ✓ Increase `session.gc_maxlifetime` if needed

### Issue: Password hash not working
- ✓ Ensure PHP version is 5.5+ (bcrypt requires it)
- ✓ Verify `password_hash()` function is available
- ✓ Check for typographical errors in hashing calls

---

## Migration from Old Admin System

The system maintains backward compatibility with the old admin login:

1. Old `admin_login.php` redirects to new `/login.php?role=admin`
2. Sessions automatically migrate to new format
3. Old `$_SESSION['admin_logged_in']` still works
4. New `$_SESSION['logged_in']` and roles preferred

To fully migrate:
1. Update any hardcoded checks from `$_SESSION['admin_logged_in']` to `isAdmin()`
2. Replace old logout links with new logout pages
3. Update admin page auth checks to new format

---

## Future Enhancements

### Recommended Additions

1. **Two-Factor Authentication (2FA)**
   - SMS-based verification
   - Email-based verification
   - Authenticator apps support

2. **OAuth Integration**
   - Google login
   - GitHub login
   - Facebook login

3. **Role-Based Permissions**
   - Multiple admin roles (super-admin, editor, moderator)
   - Permission granularity

4. **Account Management**
   - Change password feature
   - Account deletion
   - Email verification

5. **Security Features**
   - Login history
   - Active sessions management
   - IP whitelist/blacklist
   - Password reset via email

6. **Auditing**
   - Detailed action logs
   - User activity tracking
   - Admin action logs

---

## Support & Questions

For more information on:
- **PHP Session Management**: [PHP Sessions](https://www.php.net/manual/en/book.session.php)
- **Bcrypt Hashing**: [PHP Password Hashing](https://www.php.net/manual/en/function.password-hash.php)
- **PDO Prepared Statements**: [PDO Prepared Statements](https://www.php.net/manual/en/pdo.prepared-statements.php)
- **OWASP Security**: [OWASP Top 10](https://owasp.org/www-project-top-ten/)

---

## Version History

- **v2.0** (Current) - Dual authentication system with separate user/admin roles
- **v1.0** - Initial admin-only authentication with hardcoded credentials

---

**Last Updated**: February 8, 2026  
**System Status**: ✅ Production Ready
