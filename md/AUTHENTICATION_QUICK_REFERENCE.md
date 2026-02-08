# CodeDojo Authentication - Quick Reference Guide

## 🔗 Main URLs

| Purpose | URL | Role Required |
|---------|-----|---------------|
| Main Login | `/login.php` | None |
| Register | `/login.php?register=1` | None |
| Admin Dashboard | `/admin/dashboard.php` | Admin |
| User Dashboard | `/user/dashboard.php` | User |
| Admin Logout | `/admin/logout.php` | Admin |
| User Logout | `/user/logout.php` | User |

---

## 👤 Demo Accounts

| Role | Username | Password | Use Case |
|------|----------|----------|----------|
| Admin | `admin` | `codedojo123` | Testing admin features |
| User | `user` | `user123` | Testing user features |

**⚠️ Change these before production!**

---

## 💡 Quick Code Snippets

### Require Admin Login
```php
<?php require_once 'admin/auth_check.php'; ?>
```

### Require User Login
```php
<?php require_once 'user/auth_check.php'; ?>
```

### Check User Role
```php
<?php
session_start();
require_once 'config/auth.php';

if (isAdmin()) {
    echo "Admin access granted";
} elseif (isUser()) {
    echo "User access granted";
} else {
    echo "Not logged in";
}
?>
```

### Display Current Username
```php
<?php
session_start();
require_once 'config/auth.php';

if (isLoggedIn()) {
    echo "Welcome, " . getUsername();
}
?>
```

### Get User ID
```php
<?php
$userId = getUserId();  // Returns null if not logged in
?>
```

### Register New User (Programmatically)
```php
<?php
require_once 'config/auth.php';

$result = registerUser(
    'john_smith',           // username
    'john@example.com',     // email
    'securepassword123',    // password
    'John',                 // firstName
    'Smith'                 // lastName
);

if ($result['success']) {
    echo "User created: " . $result['message'];
} else {
    echo "Error: " . $result['message'];
}
?>
```

---

## 🔐 Available Functions

### Authentication Functions
```php
authenticateAdmin($username, $password)        // Returns user array or false
authenticateUser($username, $password)         // Returns user array or false
createUserSession($userData)                   // Creates authenticated session
registerUser($username, $email, $pass, $fn, $ln) // Registers new user
```

### Session Functions
```php
isLoggedIn()                                   // Boolean
isAdmin()                                      // Boolean
isUser()                                       // Boolean
getUserId()                                    // Return user ID or null
getUsername()                                  // Return username or null
getUserRole()                                  // Return role or null
logoutUser()                                   // Destroys session
checkSessionTimeout()                          // Checks 30-min timeout
requireLogin($requiredRole, $loginPage)       // Requires login or redirects
```

### Password Functions
```php
hashPassword($plaintext)                       // Returns bcrypt hash
verifyPassword($plaintext, $hash)              // Returns true/false
updateAdminPassword($id, $current, $new)      // Updates admin password
```

---

## 🗄️ Database Queries

### All Active Admins
```sql
SELECT id, username, email FROM admins WHERE is_active = TRUE ORDER BY username;
```

### All Active Users
```sql
SELECT id, username, email, first_name, last_name FROM users WHERE is_active = TRUE ORDER BY username;
```

### User by Username
```sql
SELECT * FROM users WHERE username = 'john_smith' AND is_active = TRUE;
```

### Admin by ID
```sql
SELECT * FROM admins WHERE id = 5 AND is_active = TRUE;
```

### Deactivate User
```sql
UPDATE users SET is_active = FALSE WHERE id = 3;
```

### Deactivate Admin
```sql
UPDATE admins SET is_active = FALSE WHERE id = 2;
```

---

## 🛡️ Security Checklist

- [ ] Database migration run successfully
- [ ] All passwords are hashed (bcrypt)
- [ ] Demo accounts working
- [ ] Session timeout set to 30 minutes
- [ ] No hardcoded credentials in code
- [ ] All database queries use prepared statements
- [ ] Access control working correctly
- [ ] Error messages don't expose system info
- [ ] Logout properly destroys sessions
- [ ] Changed demo account passwords

---

## 🐛 Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| "Table doesn't exist" | Run: `mysql -u root codedojo < database/add_auth_tables.sql` |
| "Invalid credentials" with correct password | Verify demo data was inserted after migration |
| Cannot access admin dashboard | Check admin role is set: `if (isAdmin())` should return true |
| Logout doesn't work | Verify `logoutUser()` is being called in logout.php |
| Session expires immediately | Check PHP `session.gc_maxlifetime` in php.ini |
| User can't register | Minimum password is 6 characters; email/username must be unique |

---

## 📝 Session Data Stored

After login, `$_SESSION` contains:

```php
$_SESSION['logged_in']      // Boolean: true
$_SESSION['user_id']        // Integer: user's database ID
$_SESSION['username']       // String: username
$_SESSION['email']          // String: email address
$_SESSION['role']           // String: 'admin' or 'user'
$_SESSION['last_activity']  // Integer: timestamp for timeout

// For User role only:
$_SESSION['first_name']     // String: first name
$_SESSION['last_name']      // String: last name
```

---

## 🔄 File Relationships

```
┌─ login.php ────────┐
│  (Main entry)      │
└────────┬───────────┘
         │
         ├──> config/database.php
         └──> config/auth.php
              │
              ├──> admin/auth_check.php (optional)
              ├──> admin/logout.php (optional)
              ├──> user/auth_check.php (optional)
              └──> user/logout.php (optional)
```

---

## ⚡ Performance Tips

1. **Cache User Data**
   - Store user data in `$_SESSION` after login
   - Don't query database for each page

2. **Use Prepared Statements**
   - Already implemented in `config/auth.php`
   - Prevents re-parsing queries

3. **Check Session Before Database**
   ```php
   if ($userId = getUserId()) {
       // Use $userId instead of querying database
   }
   ```

4. **Minimize Auth Checks**
   - Include auth_check.php once at top of page
   - Don't include multiple times

---

## 📊 Session Timeout

- **Duration**: 30 minutes (1800 seconds)
- **Implementation**: Checked on every request via `checkSessionTimeout()`
- **Reset**: Activity timestamp updates on each page load
- **Customization**: Edit timeout in `requireLogin()` function

---

## 🎓 Best Practices

### DO ✅
- Use `requireLogin()` for protected pages
- Store user data in session, not database
- Use `isset()` before accessing session data
- Hash passwords with provided function
- Validate input on form submission
- Use prepared statements for queries

### DON'T ❌
- Store passwords in plaintext
- Concatenate SQL queries with user input
- Display detailed error messages to users
- Log sensitive information
- Hardcode credentials in code
- Use `mysql_*` functions (deprecated)

---

## 🔗 Related Documentation

- **Complete Guide**: [AUTHENTICATION.md](AUTHENTICATION.md)
- **Setup Steps**: [AUTHENTICATION_SETUP.md](AUTHENTICATION_SETUP.md)
- **Implementation Summary**: [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)
- **Admin Help**: [ADMIN_GUIDE.md](ADMIN_GUIDE.md)

---

## 📱 Mobile Compatibility

The login and dashboard pages are fully responsive:
- ✅ Mobile browsers (iOS, Android)
- ✅ Tablet devices
- ✅ Desktop browsers
- ✅ Dark/light theme support

---

## 🚀 Deployment Checklist

- [ ] Change demo account passwords
- [ ] Update database backups schedule
- [ ] Enable HTTPS on server
- [ ] Set up error logging
- [ ] Configure SMTP for emails
- [ ] Implement rate limiting
- [ ] Add CSRF token validation
- [ ] Setup monitoring/alerts
- [ ] Perform security audit
- [ ] Update database.php credentials for production

---

## 🆘 Getting Help

### For Code Issues
1. Check [AUTHENTICATION.md](AUTHENTICATION.md)
2. Review function documentation
3. Check error logs in browser console
4. Verify database migration ran successfully

### For Security Questions
1. Review OWASP Top 10
2. Check implemented security measures
3. Consider hiring security audit
4. Use tools like BURP Suite for testing

### For Database Issues
1. Verify credentials in config/database.php
2. Check MySQL is running
3. View MySQL error logs
4. Use phpMyAdmin to inspect tables

---

**Last Updated**: February 8, 2026  
**Quick Reference Version**: 1.0  
**Status**: Ready for Use ✅
