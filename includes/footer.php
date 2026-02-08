        </main>
    </div>
    
    <!-- JavaScript Files -->
    <script src="assets/js/theme.js"></script>
    <?php if (isset($includeEditor) && $includeEditor): ?>
        <script src="assets/js/editor.js"></script>
    <?php endif; ?>
    <?php if (isset($includePractice) && $includePractice): ?>
        <script src="assets/js/practice.js"></script>
    <?php endif; ?>
</body>
</html>
