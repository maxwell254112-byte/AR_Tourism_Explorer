    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/admin.js')) ?>"></script>
<?php if (!empty($adminScripts)): foreach ($adminScripts as $src): ?>
<script src="<?= e($src) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
