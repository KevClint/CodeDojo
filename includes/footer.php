        </main>
    </div>
    
    <!-- JavaScript Files -->
    <script src="assets/js/theme.js?v=<?php echo @filemtime(__DIR__ . '/../assets/js/theme.js') ?: time(); ?>"></script>
    <?php if (isset($includeEditor) && $includeEditor): ?>
        <script src="assets/js/editor.js?v=<?php echo @filemtime(__DIR__ . '/../assets/js/editor.js') ?: time(); ?>"></script>
    <?php endif; ?>
    <?php if (isset($includePractice) && $includePractice): ?>
        <script src="assets/js/practice.js?v=<?php echo @filemtime(__DIR__ . '/../assets/js/practice.js') ?: time(); ?>"></script>
    <?php endif; ?>
    <?php if (isset($includePlayground) && $includePlayground): ?>
        <script src="assets/js/live_playground.js?v=<?php echo @filemtime(__DIR__ . '/../assets/js/live_playground.js') ?: time(); ?>"></script>
    <?php endif; ?>
</body>
</html>
