# 🥋 CodeDojo - HTML Practice Platform

CodeDojo is a modern, beginner-friendly web application where users learn HTML by coding directly in the browser. Built with PHP and MySQL, it features live code preview, guided practice tasks, smart hints, and progress tracking.

![CodeDojo](https://img.shields.io/badge/PHP-7.4+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## ✨ Features

### Core Features
- **🚀 Live HTML Code Editor** - Write code on the left, see instant results on the right
- **📝 Practice Tasks** - Guided challenges that build HTML skills progressively
- **💡 Smart Hints System** - Helpful hints without giving away answers
- **💾 Save & Manage Practice** - Full CRUD operations for practice submissions
- **🎨 Starter Templates** - Pre-built templates for buttons, cards, and forms

### UI/UX Features
- **🎯 Clean, Modern Interface** - Professional coding platform design
- **🌓 Dark Mode Support** - Eye-friendly dark theme for extended coding sessions
- **📱 Responsive Design** - Works seamlessly on desktop and tablets
- **⚡ Smooth Animations** - Polished transitions and interactions
- **🔤 Google Fonts & Icons** - Beautiful typography with Material Icons

### Technical Features
- **XAMPP Compatible** - Easy local development setup
- **MySQL Database** - Robust data storage
- **Auto-save** - Code automatically saved to localStorage
- **Keyboard Shortcuts** - Ctrl+Enter to run, Ctrl+S to save
- **Clean Code** - Well-commented, beginner-readable

---

## 📋 Requirements

- **XAMPP** (includes Apache, PHP 7.4+, MySQL 5.7+)
- Modern web browser (Chrome, Firefox, Edge, Safari)
- Text editor (VS Code, Sublime Text, etc.)

---

## 🚀 Installation Guide

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP (default installation path is usually `C:\xampp` on Windows)
3. Launch XAMPP Control Panel

### Step 2: Extract CodeDojo Files

1. Extract the `codedojo` folder to your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\codedojo\
   ```
   
   Your structure should look like:
   ```
   C:\xampp\htdocs\codedojo\
   ├── assets/
   ├── config/
   ├── includes/
   ├── api/
   ├── database/
   ├── index.php
   └── ...
   ```

### Step 3: Start XAMPP Services

1. Open XAMPP Control Panel
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Both should show green "Running" status

### Step 4: Create Database

1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Click **Choose File** and select: `codedojo/database/schema.sql`
4. Click **Go** at the bottom of the page
5. You should see a success message

**Alternative Method (SQL Tab):**
1. Go to `http://localhost/phpmyadmin`
2. Click **SQL** tab
3. Copy the contents of `database/schema.sql`
4. Paste into the SQL query box
5. Click **Go**

### Step 5: Verify Installation

1. Open browser and navigate to: `http://localhost/codedojo`
2. You should see the CodeDojo homepage
3. Click **Start Coding Now** to test the editor

---

## 🗄️ Database Schema

### Tables

**lessons**
- Stores HTML learning modules
- Fields: id, title, description, difficulty, order_num, created_at

**practice_tasks**
- Specific coding challenges for each lesson
- Fields: id, lesson_id, title, instruction, hint, starter_code, solution_code, order_num, created_at

**user_practice**
- User's saved practice work
- Fields: id, title, html_code, task_id, is_completed, created_at, updated_at

---

## 💻 Usage Guide

### For Students

1. **Start Coding**
   - Click "Code Editor" or "Start Coding Now"
   - Write HTML in the left panel
   - Click "Run" (or press Ctrl+Enter) to see results
   - Click "Save" (or press Ctrl+S) to save your work

2. **Practice Tasks**
   - Go to "Lessons" page
   - Choose a task based on difficulty
   - Read the instruction
   - Write code to complete the task
   - Click "Show Hint" if stuck

3. **Quick Templates**
   - Use sidebar to access Button, Card, or Form templates
   - Modify templates to learn how they work

4. **Manage Practice**
   - Go to "My Practice" page
   - View all saved work
   - Edit, preview, or delete practices

### Keyboard Shortcuts

- `Ctrl + Enter` - Run code
- `Ctrl + S` - Save practice

---

## 🎨 Customization

### Change Theme Colors

Edit `assets/css/style.css` and modify CSS variables:

```css
:root {
    --color-primary: #667eea;      /* Primary brand color */
    --color-secondary: #764ba2;    /* Secondary brand color */
    /* ... more variables ... */
}
```

### Add New Lessons

1. Go to `http://localhost/phpmyadmin`
2. Select `codedojo` database
3. Open `lessons` table
4. Click "Insert" to add new lessons
5. Then add tasks to `practice_tasks` table

### Modify Starter Templates

Edit the template code in `editor.php` around line 45-150.

---

## 🔧 Configuration

### Database Settings

If you need to change database credentials, edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
define('DB_NAME', 'codedojo');
```

---

## 🐛 Troubleshooting

### Issue: "Database connection failed"

**Solution:**
1. Make sure MySQL is running in XAMPP
2. Check `config/database.php` credentials
3. Verify database `codedojo` exists in phpMyAdmin

### Issue: Blank page or PHP errors

**Solution:**
1. Make sure Apache is running in XAMPP
2. Check PHP error logs in `C:\xampp\apache\logs\error.log`
3. Verify all files are in correct locations

### Issue: CSS/JS not loading

**Solution:**
1. Check browser console (F12) for errors
2. Verify file paths are correct
3. Clear browser cache (Ctrl+F5)

### Issue: Can't save practices

**Solution:**
1. Check browser console for JavaScript errors
2. Verify `api/save_practice.php` file exists
3. Check MySQL connection and table structure

---

## 🚦 Features Roadmap

- [ ] User authentication system
- [ ] Progress tracking & achievements
- [ ] Community-shared practice solutions
- [ ] CSS & JavaScript lessons
- [ ] Export practice as HTML files
- [ ] Collaborative coding sessions

---

## 📝 Sample Data

The database includes:
- **6 Lessons** (HTML Basics to Semantic HTML)
- **10 Practice Tasks** (Beginner to Advanced)
- **2 Sample Practices** (for testing)

All sample data is automatically inserted when you import `database/schema.sql`.

---

## 🤝 Contributing

Contributions are welcome! Here's how you can help:

1. Add new practice tasks
2. Improve UI/UX design
3. Fix bugs
4. Add new features
5. Improve documentation

---

## 📄 License

This project is open source and available under the MIT License.

---

## 🙏 Acknowledgments

- **Google Fonts** - Inter font family
- **Google Material Icons** - Icon set
- **XAMPP** - Local development environment

---

## 📧 Support

If you encounter any issues or have questions:

1. Check the Troubleshooting section above
2. Review the code comments for guidance
3. Check XAMPP error logs

---

## 🎓 Learning Resources

- [MDN Web Docs - HTML](https://developer.mozilla.org/en-US/docs/Web/HTML)
- [W3Schools - HTML Tutorial](https://www.w3schools.com/html/)
- [HTML.com](https://html.com/)

---

## 🌟 Quick Start Summary

```bash
1. Install XAMPP
2. Extract codedojo to C:\xampp\htdocs\
3. Start Apache & MySQL in XAMPP
4. Import database/schema.sql in phpMyAdmin
5. Visit http://localhost/codedojo
6. Start coding! 🎉
```

---

**Built with ❤️ for aspiring web developers**

Happy Coding! 🥋💻
