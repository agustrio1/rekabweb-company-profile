<?php
/** @var int $status @var string $title @var string $message @var string $debug */
/** @var string $homeUrl @var string $homeLabel */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?></title>
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;
            background:#f6f3ee;color:#14181f;font:1.0625rem/1.6 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
        main{width:100%;max-width:34rem}
        .code{margin:0;font-size:.85rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#5e6470}
        h1{margin:.75rem 0 0;font:600 clamp(1.75rem,1.4rem + 1.5vw,2.5rem)/1.15 Georgia,serif;letter-spacing:-.015em}
        p.msg{margin:1rem 0 0;color:#5e6470}
        a.btn{display:inline-block;margin-top:2rem;padding:.75rem 1.5rem;border-radius:4px;background:#b4532a;color:#fff;
            font-weight:500;text-decoration:none}
        a.btn:hover{background:#14181f}
        a.btn:focus-visible{outline:2px solid #b4532a;outline-offset:3px}
        pre{margin:2rem 0 0;padding:1rem;overflow-x:auto;white-space:pre-wrap;word-break:break-all;border:1px solid #ddd7cc;
            border-radius:4px;background:#fff;font-size:.75rem}
    </style>
</head>
<body>
<main>
    <p class="code">Error <?= e($status) ?></p>
    <h1><?= e($title) ?></h1>
    <p class="msg"><?= e($message) ?></p>
    <a href="<?= e($homeUrl) ?>" class="btn"><?= e($homeLabel) ?></a>
    <?php if (($debug ?? '') !== ''): ?>
        <pre><?= e($debug) ?></pre>
    <?php endif; ?>
</main>
</body>
</html>