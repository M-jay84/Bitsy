<?php

declare(strict_types=1);

/**
 * Modern Black + Blue Theme - Style Helpers
 * These are plain functions so old views need almost no changes.
 */
function style_header(string $title = '', string $extra = ''): void
{
    $siteName = CONFIG['SITENAME'] ?? 'M-BiTsy';
    $fullTitle = $title !== '' ? e($title) . ' - ' . e($siteName) : e($siteName);
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $fullTitle ?></title>
        <link rel="stylesheet" href="<?= URLROOT ?>/assets/css/theme.css">
        <?= $extra ?>
    </head>

    <body>
        <div class="site-header">
            <div class="container">
                <h1><?= htmlspecialchars($siteName) ?> <span>Tracker</span></h1>
            </div>
        </div>
        <div class="container">
        <?php
    }

    function style_footer(): void
    {
        ?>
        </div><!-- .container -->
        <div style="text-align:center; padding:30px 0; color:#64748b; font-size:0.9rem;">
            &copy; <?= date('Y') ?> <?= htmlspecialchars(CONFIG['SITENAME'] ?? 'M-BiTsy') ?>
        </div>
    </body>

    </html>
    <?php
    }

    function style_begin_block(string $title = ''): void
    {
        echo '<div class="block">';
        if ($title !== '') {
            echo '<div class="block-header">' . htmlspecialchars($title) . '</div>';
        }
        echo '<div class="block-content">';
    }

    function style_end_block(): void
    {
        echo '</div></div>'; // close content + block
    }

    function style_begin_frame(): void
    {
        echo '<div class="frame">';
    }

    function style_end_frame(): void
    {
        echo '</div>';
    }

    function style_form_start(string $action = '', string $method = 'post', string $extra = ''): void
    {
        $action = $action ? URLROOT . '/' . ltrim($action, '/') : '';
        echo '<form action="' . htmlspecialchars($action) . '" method="' . htmlspecialchars($method) . '" ' . $extra . '>';
    }

    function style_form_end(): void
    {
        echo '</form>';
    }

    function style_table_start(): void
    {
        echo '<table>';
    }

    function style_table_head(array $columns): void
    {
        echo '<thead><tr>';
        foreach ($columns as $col) {
            echo '<th>' . htmlspecialchars($col) . '</th>';
        }
        echo '</tr></thead><tbody>';
    }

    function style_table_end(): void
    {
        echo '</tbody></table>';
    }

    function style_success(string $message): void
    {
        echo '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
    }

    function style_error(string $message): void
    {
        echo '<div class="alert alert-error">' . htmlspecialchars($message) . '</div>';
    }

    function style_warning(string $message): void
    {
        echo '<div class="alert alert-warning">' . htmlspecialchars($message) . '</div>';
    }

    function style_info(string $message): void
    {
        echo '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>';
    }
