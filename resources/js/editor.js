// Tiptap dimuat hanya di halaman yang punya editor (hemat bandwidth).
const roots = document.querySelectorAll('[data-rich-editor]');

if (roots.length > 0) {
  import('./editor-core.js')
    .then(({ mountEditor }) => roots.forEach(mountEditor))
    .catch((error) => {
      // Textarea biasa tetap tampil sebagai cadangan
      console.error('Editor gagal dimuat', error);
    });
}