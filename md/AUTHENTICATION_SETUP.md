# CodeDojo Authentication - Quick Setup Checklist

## Initial Setup Steps

### Step 1: Run Database Migration ✓
- [ ] Open phpMyAdmin (http://localhost/phpmyadmin)
- [ ] Select database `codedojo`
- [ ] Click "Import" tab
- [ ] Choose file: `database/add_auth_tables.sql`
- [ ] Click "Go"

**OR use command line:**
```bash
cd c:\xampp\htdocs\codedojo2
mysql -u root codedojo < database/add_auth_tables.sql
```

### Step 2: Verify Database Tables
- [ ] In phpMyAdmin, check that `admins` table exists
- [ ] Verify `users` table exists
- [ ] Confirm demo data was inserted

### Step 3: Test Admin Login
- [ ] Go to http://localhost/codedojo2/login.php
- [ ] Click "Admin" button
- [ ] Enter: `admin` / `codedojo123`
- [ ] Should redirect to `/admin/dashboard.php`

### Step 4: Test User Login
- [ ] Go to http://localhost/codedojo2/login.php
- [ ] Click "User" button
- [ ] Enter: `user` / `user123`
- [ ] Should redirect to `/user/dashboard.php`

### Step 5: Test Registration
- [ ] Click "Create one now" link
- [ ] Fill in registration form with new account details
- [ ] Should automatically log in after registration

### Step 6: Test Logout
- [ ] From user/admin dashboard, click logout
- [ ] Should redirect to login page
- [ ] Session should be cleared

---

## Files Created/Modified

### New Files
- ✓ `login.php` - Unified login & registration page
- ✓ `config/auth.php` - Authentication functions library
- ✓ `user/auth_check.php` - User auth middleware
- ✓ `user/dashboard.php` - User dashboard page
- ✓ `user/logout.php` - User logout handler
- ✓ `database/add_auth_tables.sql` - Database migration
- ✓ `AUTHENTICATION.md` - Complete documentation

### Modified Files
- ✓ `admin/auth_check.php` - Updated for new auth system (backward compatible)
- ✓ `admin_login.php` - Redirects to unified login
- ✓ `admin/logout.php` - Uses new auth logout function
- ✓ `includes/header.php` - Added login/logout menu items

---

## Default Credentials

### Admin Account
```
Username: admin
Password: codedojo123
```

### Demo User Account
```
Username: user
Password: user123
```

---

## Testing Endpoints

| Endpoint | Expected Result |
|----------|-----------------|
| `/login.php` | Unified login page with role selection |
| `/login.php?register=1` | Registration form for new users |
| `/admin/dashboard.php` | Admin dashboard (requires admin login) |
| `/user/dashboard.php` | User dashboard (requires user login) |
| `/user/logout.php` | Logs out user and redirects to login |
| `/admin/logout.php` | Logs out admin and redirects to login |
| `/editor.php` | Code editor (public, no auth required) |
| `/lessons.php` | Public lessons page |

---

## Security Checklist

- [ ] All passwords are bcrypt hashed
- [ ] Database queries use prepared statements (SQL injection protected)
- [ ] Session timeout set to 30 minutes
- [ ] Separate tables for admins and users
- [ ] Password strength validation (minimum 6 characters)
- [ ] Session validation on each page load
- [ ] Proper access control based on roles
- [ ] Sensitive data not logged

---

## Browser Testing

Test with these scenarios in different browsers:

### Chrome/Firefox/Edge
- [ ] Login as admin
- [ ] Login as user
- [ ] Register new user
- [ ] Logout and verify redirect
- [ ] Test session timeout (30 minutes)
- [ ] Test dark/light theme toggle

### Mobile Browser
- [ ] Login page is responsive
- [ ] Dashboard is mobile-friendly
- [ ] Forms are easy to use on mobile

---

## Help & Support

### Common Issues & Solutions

**"Table doesn't exist" error**
→ Run the migration file in phpMyAdmin

**"Invalid credentials" even with correct password**
→ Verify demo accounts were inserted after migration

**Cannot access admin dashboard after login**
→ Check that admin role is set in session

**Logout doesn't work**
→ Verify `session_destroy()` is being called

---

## Next Steps

1. **Secure the system** (see AUTHENTICATION.md for security hardening)
2. **Customize credentials** (change demo account passwords)
3. **Add more admins** (create additional admin accounts)
4. **Implement logging** (track authentication events)
5. **Set up email verification** (for password resets)

---

## Documentation Files

- **AUTHENTICATION.md** - Complete technical documentation
- **README.md** - General project information
- **INSTALLATION.md** - Original installation guide
- **ADMIN_GUIDE.md** - Admin panel usage guide

---

**Setup Date**: February 8, 2026  
**System Status**: Ready for Testing ✓
