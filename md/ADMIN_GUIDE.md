# 🔐 Admin Panel Guide - CodeDojo

## Quick Access

**Admin Login URL:** `http://localhost/codedojo/admin_login.php`

**Default Credentials:**
- Username: `admin`
- Password: `codedojo123`

---

## Features

### 1. Dashboard
- View statistics (lessons, tasks, user practices)
- Quick actions for common tasks
- Tips for creating great content

### 2. Manage Lessons
- **Create** new lessons
- **Edit** existing lessons
- **Delete** lessons (will also delete associated tasks)
- Set difficulty level (beginner, intermediate, advanced)
- Set display order

### 3. Manage Tasks
- **Create** practice tasks for students
- **Edit** existing tasks
- **Delete** tasks
- Set task instructions
- Add helpful hints
- Provide starter code
- Assign to lessons

### 4. View User Practices
- See all student submissions
- Preview their code
- Monitor learning progress

---

## Creating Your First Lesson

### Step 1: Login
1. Go to `http://localhost/codedojo/admin_login.php`
2. Enter username: `admin`
3. Enter password: `codedojo123`
4. Click "Sign In"

### Step 2: Create a Lesson
1. Click "Manage Lessons" in sidebar
2. Click "New Lesson" button
3. Fill in:
   - **Title**: e.g., "Introduction to HTML"
   - **Description**: Brief summary of what students will learn
   - **Difficulty**: Choose beginner/intermediate/advanced
   - **Order Number**: Lower numbers appear first (0, 1, 2...)
4. Click "Create Lesson"

### Step 3: Add Tasks to Your Lesson
1. Click "Manage Tasks" in sidebar
2. Click "New Task" button
3. Fill in:
   - **Lesson**: Select the lesson you just created
   - **Task Title**: e.g., "Create a Heading"
   - **Instruction**: Clear, concise instructions (1-3 sentences)
     - Example: "Create an h1 heading with the text 'Welcome to My Page' and style it with a blue color."
   - **Hint** (optional): Guide without spoiling
     - Example: "Use the <h1> tag and add style='color: blue;' attribute"
   - **Starter Code** (optional): Help students get started
     - Example: `<!-- Create your heading here -->`
   - **Order Number**: Sequence within the lesson (0, 1, 2...)
4. Click "Create Task"

---

## Best Practices

### Writing Good Instructions
✅ **Good**: "Create a button with the text 'Click Me' and give it a blue background color."
❌ **Too vague**: "Make a button."
❌ **Too detailed**: "Create a button element using the <button> tag with the text content 'Click Me' and add an inline style attribute..."

### Writing Helpful Hints
✅ **Good**: "Use the <button> tag and the style attribute. Try: style='background-color: blue;'"
❌ **Too much**: Full solution code
❌ **Too little**: "Use HTML"

### Starter Code Tips
- Provide HTML comments as guidance
- Give structure but not the answer
- Help students focus on the learning goal

Example:
```html
<!-- Create your button here -->
<!-- Remember to add text and styling -->
```

### Order Numbers
- Use gaps (0, 10, 20) so you can insert tasks later
- Or use consecutive numbers (0, 1, 2, 3)
- Lower numbers appear first

---

## Changing Admin Credentials

### For Better Security (Recommended)

Edit `admin_login.php` around line 17:

```php
// Change these credentials
if ($username === 'admin' && $password === 'codedojo123') {
```

Change to your preferred username and password:

```php
if ($username === 'yourusername' && $password === 'yourpassword') {
```

### For Production (Highly Recommended)
Store hashed passwords in database instead of plain text. This requires additional setup but is much more secure.

---

## Session Security

- **Auto-logout**: Admin session expires after 30 minutes of inactivity
- **Login required**: All admin pages check authentication
- **Session-based**: Uses PHP sessions for security

---

## Admin Panel Structure

```
admin/
├── auth_check.php      # Authentication verification
├── dashboard.php       # Main admin dashboard
├── manage_lessons.php  # Create/Edit/Delete lessons
├── manage_tasks.php    # Create/Edit/Delete tasks
├── view_practices.php  # View user submissions
└── logout.php          # Logout script

admin_login.php         # Login page (in root folder)
```

---

## Keyboard Shortcuts

While in admin forms:
- `Tab` - Navigate between fields
- `Enter` - Submit form (when in text input)
- `Ctrl + Click` on "View Site" - Opens in new tab

---

## Common Admin Tasks

### Adding a New Lesson Series
1. Create the lesson (Manage Lessons)
2. Add multiple tasks (Manage Tasks)
3. Start with easy tasks, increase difficulty
4. Test each task yourself first

### Editing Content
1. Click the edit (pencil) icon on any lesson/task
2. Make your changes
3. Click "Update"

### Deleting Content
1. Click the delete (trash) icon
2. Confirm the deletion
3. Note: Deleting a lesson deletes all its tasks

### Viewing Student Work
1. Go to "User Practices"
2. Click "View" to see their code
3. Opens in editor for full preview

---

## Tips for Admins

### Content Creation
- Create lessons in a logical sequence
- Balance difficulty - not too easy, not too hard
- Test tasks yourself before publishing
- Provide hints for challenging concepts
- Use real-world examples

### Task Design
- One learning goal per task
- Clear success criteria
- Achievable in 5-15 minutes
- Builds on previous knowledge

### Starter Code Strategy
- Use for complex structures
- Provide commented guidance
- Don't give away the answer
- Help students focus on the main concept

---

## Troubleshooting

### "You need to create a lesson first"
- This appears when trying to add a task without any lessons
- Solution: Create at least one lesson first

### Can't login
- Check credentials (username: admin, password: codedojo123)
- Make sure cookies/sessions are enabled
- Try clearing browser cache

### Changes not showing on site
- Hard refresh: Ctrl + F5 (Windows) or Cmd + Shift + R (Mac)
- Check if you're viewing the correct page
- Verify the task is assigned to the right lesson

### Session timeout
- Admin sessions expire after 30 minutes of inactivity
- Solution: Log in again

---

## Security Recommendations

### For Development (XAMPP)
✅ Default credentials are fine
✅ Only accessible locally (localhost)

### For Production (Live Server)
🔒 Change default username/password
🔒 Use HTTPS
🔒 Store hashed passwords in database
🔒 Add IP restrictions
🔒 Enable two-factor authentication
🔒 Regular security updates

---

## Quick Reference

| Action | Location | Button |
|--------|----------|--------|
| Create Lesson | Manage Lessons | "New Lesson" |
| Create Task | Manage Tasks | "New Task" |
| Edit Lesson | Manage Lessons | Pencil icon |
| Edit Task | Manage Tasks | Pencil icon |
| Delete | Any page | Trash icon |
| View Dashboard | Sidebar | "Dashboard" |
| Logout | Sidebar | "Logout" |

---

## Support

If you encounter any issues:
1. Check this guide first
2. Verify your PHP/MySQL versions
3. Check browser console for JavaScript errors
4. Look at XAMPP error logs

---

**Remember:** Test every task yourself before students see it! 🎯

Happy teaching! 🥋📚
