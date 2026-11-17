</main>
<?php if (!empty($pageScripts) && is_array($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?= base_url($script) ?>?v=2.3"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
