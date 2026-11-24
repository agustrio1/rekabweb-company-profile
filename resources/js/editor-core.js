import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import Table from '@tiptap/extension-table';
import TableRow from '@tiptap/extension-table-row';
import TableHeader from '@tiptap/extension-table-header';
import TableCell from '@tiptap/extension-table-cell';

const icon = (paths) =>
  `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;

// Path konstan (ikon lucide), bukan input pengguna
const ICONS = {
  bullet: icon('<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>'),
  ordered: icon('<path d="M10 6h11M10 12h11M10 18h11"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>'),
  left: icon('<path d="M21 6H3M15 12H3M17 18H3"/>'),
  center: icon('<path d="M21 6H3M17 12H7M19 18H5"/>'),
  right: icon('<path d="M21 6H3M21 12H9M21 18H7"/>'),
  link: icon('<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>'),
  image: icon('<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>'),
  table: icon('<path d="M12 3v18"/><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M3 15h18"/>'),
  hr: icon('<path d="M5 12h14"/>'),
  undo: icon('<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/>'),
  redo: icon('<path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3L21 13"/>'),
  eye: icon('<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>'),
};

/** Terima https://, mailto:, tel:, path /…, atau #…; tolak sisanya (mis. javascript:). */
function normalizeUrl(value) {
  const v = value.trim();
  if (v === '') return '';
  if (/^(https?:\/\/|mailto:|tel:)/i.test(v)) return v;
  if ((v.startsWith('/') && !v.startsWith('//')) || v.startsWith('#')) return v;
  if (/^[\w-]+(\.[\w-]+)+(\/\S*)?$/i.test(v)) return 'https://' + v;
  return null;
}

export function mountEditor(root) {
  const textarea = root.querySelector('textarea');
  const toolbar = root.querySelector('[data-toolbar]');
  const tableBar = root.querySelector('[data-table-tools]');
  const area = root.querySelector('[data-content]');
  if (!textarea || !toolbar || !tableBar || !area) return;

  const canMedia = root.dataset.canMedia === '1';
  const form = textarea.closest('form');
  const controls = [];
  let ready = false;
  let preview = false;
  let dirty = false;
  let submitted = false;

  const editor = new Editor({
    element: area,
    content: textarea.value,
    extensions: [
      StarterKit.configure({ heading: { levels: [2, 3, 4] }, code: false, codeBlock: false }),
      Underline,
      Link.configure({
        openOnClick: false,
        autolink: true,
        HTMLAttributes: { target: '_blank', rel: 'noopener noreferrer' },
      }),
      Image.configure({ inline: false, allowBase64: false }),
      TextAlign.configure({ types: ['heading', 'paragraph'] }),
      Table.configure({ resizable: false }),
      TableRow,
      TableHeader,
      TableCell,
    ],
    editorProps: {
      attributes: {
        class: 'rich-prose',
        role: 'textbox',
        'aria-multiline': 'true',
        'aria-label': root.dataset.label || 'Isi konten',
      },
    },
    onUpdate: () => {
      sync();
      dirty = true;
    },
    onTransaction: () => {
      if (ready) refresh();
    },
  });

  const cmd = () => editor.chain().focus();

  function sync() {
    textarea.value = editor.isEmpty ? '' : editor.getHTML();
  }

  function addButton(parent, opts) {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'rich-btn';
    b.title = opts.label;
    b.setAttribute('aria-label', opts.label);
    if (opts.html) b.innerHTML = opts.html;
    else b.textContent = opts.text;
    if (opts.active) b.setAttribute('aria-pressed', 'false');
    // Jaga fokus dan seleksi di editor saat tombol ditekan
    b.addEventListener('mousedown', (e) => e.preventDefault());
    b.addEventListener('click', opts.run);
    parent.appendChild(b);
    if (!opts.skip) controls.push({ el: b, active: opts.active, disabled: opts.disabled });
    return b;
  }

  function addSep(parent) {
    const s = document.createElement('span');
    s.className = 'rich-sep';
    s.setAttribute('aria-hidden', 'true');
    parent.appendChild(s);
  }

    // Gaya blok
  addButton(toolbar, { text: 'P', label: 'Paragraf', run: () => cmd().setParagraph().run(), active: () => editor.isActive('paragraph') });
  [2, 3, 4].forEach((level) => {
    addButton(toolbar, {
      text: 'H' + level,
      label: 'Judul ' + level,
      run: () => cmd().toggleHeading({ level }).run(),
      active: () => editor.isActive('heading', { level }),
    });
  });
  addSep(toolbar);

  // Gaya teks
  addButton(toolbar, { html: '<b>B</b>', label: 'Tebal', run: () => cmd().toggleBold().run(), active: () => editor.isActive('bold') });
  addButton(toolbar, { html: '<i>I</i>', label: 'Miring', run: () => cmd().toggleItalic().run(), active: () => editor.isActive('italic') });
  addButton(toolbar, { html: '<u>U</u>', label: 'Garis bawah', run: () => cmd().toggleUnderline().run(), active: () => editor.isActive('underline') });
  addButton(toolbar, { html: '<s>S</s>', label: 'Coret', run: () => cmd().toggleStrike().run(), active: () => editor.isActive('strike') });
  addSep(toolbar);

  // Daftar dan kutipan
  addButton(toolbar, { html: ICONS.bullet, label: 'Daftar poin', run: () => cmd().toggleBulletList().run(), active: () => editor.isActive('bulletList') });
  addButton(toolbar, { html: ICONS.ordered, label: 'Daftar nomor', run: () => cmd().toggleOrderedList().run(), active: () => editor.isActive('orderedList') });
  addButton(toolbar, { text: '❝', label: 'Kutipan', run: () => cmd().toggleBlockquote().run(), active: () => editor.isActive('blockquote') });
  addSep(toolbar);

  // Perataan
  addButton(toolbar, { html: ICONS.left, label: 'Rata kiri', run: () => cmd().setTextAlign('left').run(), active: () => editor.isActive({ textAlign: 'left' }) });
  addButton(toolbar, { html: ICONS.center, label: 'Rata tengah', run: () => cmd().setTextAlign('center').run(), active: () => editor.isActive({ textAlign: 'center' }) });
  addButton(toolbar, { html: ICONS.right, label: 'Rata kanan', run: () => cmd().setTextAlign('right').run(), active: () => editor.isActive({ textAlign: 'right' }) });
  addSep(toolbar);

  // Sisipan
  addButton(toolbar, {
    html: ICONS.link,
    label: 'Link',
    active: () => editor.isActive('link'),
    run: () => {
      const previous = editor.getAttributes('link').href || '';
      const input = window.prompt('Alamat link (kosongkan untuk menghapus link):', previous);
      if (input === null) return;
      const url = normalizeUrl(input);
      if (url === null) {
        window.alert('Alamat link tidak valid. Gunakan https://…, mailto:, tel:, atau path seperti /kontak.');
        return;
      }
      const chain = cmd().extendMarkRange('link');
      if (url === '') chain.unsetLink().run();
      else chain.setLink({ href: url }).run();
    },
  });

  if (canMedia) {
    addButton(toolbar, {
      html: ICONS.image,
      label: 'Sisipkan gambar',
      run: () => {
        window.dispatchEvent(new CustomEvent('pick-media', {
          detail: {
            callback: (item) => {
              const alt = item.alt || window.prompt('Alt text untuk gambar ini (deskripsi singkat):', '') || '';
              editor.chain().focus().setImage({ src: item.url, alt }).run();
            },
          },
        }));
      },
    });
  }

  addButton(toolbar, { html: ICONS.table, label: 'Sisipkan tabel', run: () => cmd().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run() });
  addButton(toolbar, { html: ICONS.hr, label: 'Garis pemisah', run: () => cmd().setHorizontalRule().run() });
  addSep(toolbar);

  addButton(toolbar, { html: ICONS.undo, label: 'Urungkan', run: () => cmd().undo().run(), disabled: () => !editor.can().undo() });
  addButton(toolbar, { html: ICONS.redo, label: 'Ulangi', run: () => cmd().redo().run(), disabled: () => !editor.can().redo() });

  const previewBtn = addButton(toolbar, {
    html: ICONS.eye + '<span class="rich-btn-text">Pratinjau</span>',
    label: 'Pratinjau konten',
    skip: true,
    run: () => setPreview(!preview),
  });
  previewBtn.classList.add('rich-btn-end');
  previewBtn.setAttribute('aria-pressed', 'false');

  // Alat tabel (muncul saat kursor di dalam tabel)
  addButton(tableBar, { text: '+ Baris', label: 'Tambah baris', run: () => cmd().addRowAfter().run() });
  addButton(tableBar, { text: '+ Kolom', label: 'Tambah kolom', run: () => cmd().addColumnAfter().run() });
  addButton(tableBar, { text: '− Baris', label: 'Hapus baris', run: () => cmd().deleteRow().run() });
  addButton(tableBar, { text: '− Kolom', label: 'Hapus kolom', run: () => cmd().deleteColumn().run() });
  addButton(tableBar, { text: 'Baris judul', label: 'Aktifkan atau matikan baris judul', run: () => cmd().toggleHeaderRow().run() });
  addButton(tableBar, { text: 'Hapus tabel', label: 'Hapus tabel', run: () => cmd().deleteTable().run() });

  function refresh() {
    if (preview) return;

    controls.forEach((c) => {
      if (c.active) c.el.setAttribute('aria-pressed', c.active() ? 'true' : 'false');
      if (c.disabled) c.el.disabled = c.disabled();
    });

    tableBar.hidden = !editor.isActive('table');
  }

  function setPreview(on) {
    preview = on;
    editor.setEditable(!on, false);
    area.classList.toggle('is-preview', on);
    previewBtn.setAttribute('aria-pressed', on ? 'true' : 'false');

    root.querySelectorAll('[data-toolbar] button, [data-toolbar] select, [data-table-tools] button').forEach((el) => {
      if (el !== previewBtn) el.disabled = on;
    });

    if (on) tableBar.hidden = true;
    else refresh();
  }

  if (form) {
    form.addEventListener('submit', () => {
      sync();
      submitted = true;
    });
  }

  window.addEventListener('beforeunload', (event) => {
    if (dirty && !submitted) {
      event.preventDefault();
      event.returnValue = '';
    }
  });

  textarea.hidden = true;
  ready = true;
  refresh();
}