<?php
$breadcrumbs = $breadcrumbs ?? [
    ['label' => 'Home', 'url' => 'index.php']
];
?>
<nav class="text-sm text-gray-600 mb-4 flex items-center gap-2 flex-wrap">
    <?php foreach ($breadcrumbs as $index => $crumb): ?>
        <?php if (isset($crumb['url'])): ?>
            <a href="<?= htmlspecialchars($crumb['url']) ?>" class="text-gray-600 hover:text-gray-800 transition-colors flex items-center gap-1">
                <?php if ($index === 0): ?>
                    <i class="bi bi-house"></i>
                <?php endif; ?>
                <?= htmlspecialchars($crumb['label']) ?>
            </a>
            <?php if ($index < count($breadcrumbs) - 1): ?>
                <span class="text-gray-400">/</span>
            <?php endif; ?>
        <?php else: ?>
            <span class="text-gray-600"><?= htmlspecialchars($crumb['label']) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
