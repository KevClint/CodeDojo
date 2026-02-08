# 📋 CodeDojo - Quick Installation Checklist

## Step-by-Step Setup (5 minutes)

### ✅ Step 1: Install XAMPP
- [ ] Download XAMPP from https://www.apachefriends.org/
- [ ] Run installer (use default settings)
- [ ] Note installation path (usually C:\xampp)

### ✅ Step 2: Place Files
- [ ] Extract codedojo folder
- [ ] Copy to: `C:\xampp\htdocs\codedojo`
- [ ] Verify folder structure exists

### ✅ Step 3: Start Services
- [ ] Open XAMPP Control Panel
- [ ] Click Start next to Apache (wait for green light)
- [ ] Click Start next to MySQL (wait for green light)

### ✅ Step 4: Import Database
- [ ] Open browser
- [ ] Go to: http://localhost/phpmyadmin
- [ ] Click "Import" tab
- [ ] Choose file: `codedojo/database/schema.sql`
- [ ] Click "Go" button
- [ ] Wait for success message

### ✅ Step 5: Test Installation
- [ ] Open browser
- [ ] Go to: http://localhost/codedojo
- [ ] See homepage? ✓
- [ ] Click "Start Coding Now"
- [ ] Type some HTML
- [ ] Click "Run" button
- [ ] See preview? ✓

---

## 🎉 Success!

If you see the CodeDojo homepage and can write code with live preview, you're all set!

---

## ⚠️ Common Issues

**Problem: Can't access http://localhost/codedojo**
- Solution: Make sure Apache is running (green in XAMPP)

**Problem: "Database connection failed"**
- Solution: Make sure MySQL is running (green in XAMPP)

**Problem: Pages load but look broken**
- Solution: Press Ctrl+F5 to hard refresh the browser

**Problem: Database import fails**
- Solution: Go to SQL tab in phpMyAdmin, paste contents of schema.sql manually

---

## 🆘 Need Help?

1. Check XAMPP Control Panel - both services should be green
2. Check browser console (F12) for errors
3. Check README.md for detailed troubleshooting
4. Look at error logs: C:\xampp\apache\logs\error.log

---

## 🚀 Next Steps

Once installed:

1. **Explore** - Click around and see all features
2. **Try a Task** - Go to Lessons page and pick a beginner task
3. **Save Your Work** - Practice saving and loading code
4. **Customize** - Modify colors in assets/css/style.css
5. **Add Content** - Add your own lessons via phpMyAdmin

---

## 📁 Verify File Structure

Your structure should look like this:

```
C:\xampp\htdocs\codedojo\
├── assets\
│   ├── css\
│   │   ├── style.css
│   │   └── themes.css
│   └── js\
│       ├── editor.js
│       ├── theme.js
│       └── practice.js
├── config\
│   └── database.php
├── includes\
│   ├── header.php
│   └── footer.php
├── api\
│   ├── save_practice.php
│   ├── load_practice.php
│   ├── delete_practice.php
│   └── get_tasks.php
├── database\
│   └── schema.sql
├── index.php
├── editor.php
├── lessons.php
├── my_practice.php
└── README.md
```

---

**Installation Time: ~5 minutes**
**Difficulty: Easy (Beginner-friendly)**
**Requirements: Just XAMPP!**

Happy Coding! 🥋💻
