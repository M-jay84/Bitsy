<?php
use App\Libs\View;
style_begin_block('Home');
View::flash();
?>
<p>Test controller index.</p>
<ul>
    <li><a href="<?= e(URLROOT) ?>/home"><?= e('Home') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/home/test"><?= e('Home Test') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/test"><?= e('Test index') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/test/second/42"><?= e('Test second with id') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/test/flash"><?= e('Test flash message') ?></a></li>
    <li><a href="<?= e(URLROOT) ?>/test/error"><?= e('Test error page (styled)') ?></a></li>
</ul>
<?php
style_end_block();