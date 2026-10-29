<?php
style_begin_block($TITLE ?? (LANG['NOTHING_FOUND'] ?? 'Not found'));
?>
<p><?= e($MESSAGE ?? (LANG['NOTHING_FOUND'] ?? 'Page not found')) ?></p>
<p><a href="<?= e(URLROOT) ?>"><?= e(LANG['HOME'] ?? 'Home') ?></a></p>
<?php
style_end_block();