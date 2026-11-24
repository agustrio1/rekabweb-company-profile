<?php
$queue = [];
foreach (['error', 'success'] as $type) {
    $message = flash($type);
    if (is_string($message) && $message !== '') {
        $queue[] = ['message' => $message, 'type' => $type];
    }
}
$json = json_encode($queue, JSON_UNESCAPED_UNICODE);
?>
<div class="pointer-events-none fixed inset-x-4 bottom-4 z-50 flex flex-col gap-2 sm:left-auto sm:right-4 sm:w-96"
     aria-live="polite"
     x-data="{
        items: [],
        n: 0,
        add(message, type = 'success') {
            const id = ++this.n;
            this.items.push({ id, message, type });
            setTimeout(() => this.remove(id), type === 'error' ? 7000 : 4500);
        },
        remove(id) {
            this.items = this.items.filter(i => i.id !== id);
        }
     }"
     x-init="(<?= e($json) ?>).forEach(t => add(t.message, t.type))"
     @toast.window="add($event.detail.message, $event.detail.type)">

    <template x-for="t in items" :key="t.id">
        <div class="pointer-events-auto flex items-start gap-3 rounded-lg border border-line bg-surface p-4 text-sm shadow-lg"
             :role="t.type === 'error' ? 'alert' : 'status'"
             x-transition:enter="transition duration-200 ease-out"
             x-transition:enter-start="translate-y-2 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition duration-150 ease-in"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <svg x-show="t.type !== 'error'" class="mt-0.5 h-4 w-4 shrink-0 text-green-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
            <svg x-show="t.type === 'error'" class="mt-0.5 h-4 w-4 shrink-0 text-red-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>

            <p class="flex-1 font-medium leading-snug" x-text="t.message"></p>

            <button type="button" class="-mr-1 -mt-1 rounded p-1 text-muted transition-colors hover:text-ink"
                    @click="remove(t.id)" aria-label="Tutup">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </template>
</div>