<?php
/** @var string $name  nama field form */
/** @var string $value HTML tersimpan (sudah disanitasi) */
/** @var string $id    id textarea */
?>
<div class="rich-editor" data-rich-editor
     data-can-media="<?= can('media.view') ? '1' : '0' ?>"
     data-label="<?= e($label ?? 'Isi konten') ?>">
    <div class="rich-toolbar" data-toolbar role="toolbar" aria-label="Format teks"></div>
    <div class="rich-toolbar rich-toolbar-sub" data-table-tools role="toolbar" aria-label="Alat tabel" hidden></div>
    <div class="rich-content" data-content></div>
    <?php /* Fallback tanpa JS; disembunyikan oleh editor.js saat Tiptap aktif */ ?>
    <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="12"
              class="textarea rounded-none border-0 shadow-none"><?= e($value) ?></textarea>
</div>