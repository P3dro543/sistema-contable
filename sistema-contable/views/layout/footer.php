<!-- Scripts de Bootstrap y demás -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
<?php if (isset($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $src): ?>
        <script src="<?php echo htmlspecialchars($src); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>

</html>
