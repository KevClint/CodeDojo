<?php
/**
 * CodeDojo - Home Page
 * Welcome page with getting started guide
 */

$pageTitle = 'Welcome to CodeDojo';
$currentPage = 'home';

include 'includes/header.php';
?>

<div style="padding: var(--spacing-xl); max-width: 1200px; margin: 0 auto;">
    <!-- Hero Section -->
    <div style="text-align: center; padding: var(--spacing-xl) 0; margin-bottom: var(--spacing-xl);">
        <h1 style="font-size: 48px; font-weight: 700; color: var(--color-primary); margin-bottom: var(--spacing-md);">
            &#x1F94B; Welcome to CodeDojo
        </h1>
        <p style="font-size: 20px; color: var(--text-secondary); margin-bottom: var(--spacing-lg); max-width: 600px; margin-left: auto; margin-right: auto;">
            Master HTML through hands-on practice. Write code, see results instantly, and build your skills one challenge at a time.
        </p>
        <div style="display: flex; gap: var(--spacing-md); justify-content: center; flex-wrap: wrap;">
            <a href="editor.php" class="btn btn-primary" style="font-size: 16px; padding: 12px 24px;">
                <span class="material-icons">code</span>
                Start Coding Now
            </a>
            <a href="lessons.php" class="btn btn-secondary" style="font-size: 16px; padding: 12px 24px;">
                <span class="material-icons">book</span>
                Browse Lessons
            </a>
        </div>
    </div>
    
    <!-- Features Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-xl);">
        <!-- Feature 1 -->
        <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: var(--border-radius-lg); display: flex; align-items: center; justify-content: center; margin-bottom: var(--spacing-md);">
                <span class="material-icons" style="font-size: 32px; color: white;">flash_on</span>
            </div>
            <h3 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-sm); color: var(--text-primary);">
                Live Preview
            </h3>
            <p style="color: var(--text-secondary); line-height: 1.6;">
                See your HTML code come to life instantly. Write on the left, preview on the right - no refresh needed.
            </p>
        </div>
        
        <!-- Feature 2 -->
        <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: var(--border-radius-lg); display: flex; align-items: center; justify-content: center; margin-bottom: var(--spacing-md);">
                <span class="material-icons" style="font-size: 32px; color: white;">assignment</span>
            </div>
            <h3 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-sm); color: var(--text-primary);">
                Guided Tasks
            </h3>
            <p style="color: var(--text-secondary); line-height: 1.6;">
                Learn by doing with structured practice tasks. Each challenge builds your skills step by step.
            </p>
        </div>
        
        <!-- Feature 3 -->
        <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: var(--border-radius-lg); display: flex; align-items: center; justify-content: center; margin-bottom: var(--spacing-md);">
                <span class="material-icons" style="font-size: 32px; color: white;">lightbulb</span>
            </div>
            <h3 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-sm); color: var(--text-primary);">
                Smart Hints
            </h3>
            <p style="color: var(--text-secondary); line-height: 1.6;">
                Stuck? Get helpful hints that guide you without giving away the answer. Learn to solve problems independently.
            </p>
        </div>
        
        <!-- Feature 4 -->
        <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: var(--border-radius-lg); display: flex; align-items: center; justify-content: center; margin-bottom: var(--spacing-md);">
                <span class="material-icons" style="font-size: 32px; color: white;">save</span>
            </div>
            <h3 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-sm); color: var(--text-primary);">
                Save Progress
            </h3>
            <p style="color: var(--text-secondary); line-height: 1.6;">
                All your practice work is saved. Come back anytime to review, edit, or build on what you've created.
            </p>
        </div>
        
        <!-- Feature 5 -->
        <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: var(--border-radius-lg); display: flex; align-items: center; justify-content: center; margin-bottom: var(--spacing-md);">
                <span class="material-icons" style="font-size: 32px; color: white;">code</span>
            </div>
            <h3 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-sm); color: var(--text-primary);">
                Ready Templates
            </h3>
            <p style="color: var(--text-secondary); line-height: 1.6;">
                Start with pre-built templates for buttons, cards, and forms. Modify them to understand how they work.
            </p>
        </div>
        
        <!-- Feature 6 -->
        <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); border-radius: var(--border-radius-lg); display: flex; align-items: center; justify-content: center; margin-bottom: var(--spacing-md);">
                <span class="material-icons" style="font-size: 32px; color: white;">dark_mode</span>
            </div>
            <h3 style="font-size: 20px; font-weight: 600; margin-bottom: var(--spacing-sm); color: var(--text-primary);">
                Dark Mode
            </h3>
            <p style="color: var(--text-secondary); line-height: 1.6;">
                Easy on the eyes with dark mode support. Toggle between light and dark themes with one click.
            </p>
        </div>
    </div>
    
    <!-- Getting Started Section -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: var(--spacing-xl); border-radius: var(--border-radius-lg); color: white; text-align: center;">
        <h2 style="font-size: 32px; font-weight: 700; margin-bottom: var(--spacing-md);">
            Ready to Start Your Journey?
        </h2>
        <p style="font-size: 18px; margin-bottom: var(--spacing-lg); opacity: 0.9; max-width: 600px; margin-left: auto; margin-right: auto;">
            Join CodeDojo today and learn HTML through practice. No lectures, no boring theory - just you, your code, and instant results.
        </p>
        <a href="editor.php" class="btn" style="background: white; color: #667eea; font-size: 16px; padding: 12px 32px; font-weight: 600;">
            <span class="material-icons">play_arrow</span>
            Launch Editor
        </a>
    </div>
    
    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-md); margin-top: var(--spacing-xl); text-align: center;">
        <?php
        // Get quick stats from database
        try {
            require_once 'config/database.php';
            $pdo = getDBConnection();
            
            $lessonsCount = $pdo->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
            $tasksCount = $pdo->query("SELECT COUNT(*) FROM practice_tasks")->fetchColumn();
            $practiceCount = $pdo->query("SELECT COUNT(*) FROM user_practice")->fetchColumn();
        } catch (Exception $e) {
            $lessonsCount = 0;
            $tasksCount = 0;
            $practiceCount = 0;
        }
        ?>
        
        <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius); border: 1px solid var(--border-color);">
            <div style="font-size: 36px; font-weight: 700; color: var(--color-primary); margin-bottom: var(--spacing-xs);">
                <?php echo $lessonsCount; ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">
                Lessons Available
            </div>
        </div>
        
        <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius); border: 1px solid var(--border-color);">
            <div style="font-size: 36px; font-weight: 700; color: var(--color-primary); margin-bottom: var(--spacing-xs);">
                <?php echo $tasksCount; ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">
                Practice Tasks
            </div>
        </div>
        
        <div style="background: var(--bg-primary); padding: var(--spacing-lg); border-radius: var(--border-radius); border: 1px solid var(--border-color);">
            <div style="font-size: 36px; font-weight: 700; color: var(--color-primary); margin-bottom: var(--spacing-xs);">
                <?php echo $practiceCount; ?>
            </div>
            <div style="color: var(--text-secondary); font-size: 14px; font-weight: 500;">
                Practices Saved
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

