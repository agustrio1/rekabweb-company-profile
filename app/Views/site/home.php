<?php
$this->layout('site/layout', ['page' => ['home' => true, 'active' => 'home', 'path' => '/']]);

$hasHero = false;
foreach ($sections as $s) {
    if ($s['key'] === 'hero') {
        $hasHero = true;
        break;
    }
}
?>
<?php if (!$hasHero): ?>
    <h1 class="sr-only"><?= e(\App\Services\Site::name()) ?></h1>
<?php endif; ?>

<?php foreach ($sections as $s): ?>
    <?php $this->partial('site/home/' . $s['key'], [
        'data' => $s['data'],
        'items' => $s['items'],
        'image' => $s['image'],
    ]); ?>
<?php endforeach; ?>