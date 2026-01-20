<x-filament-panels::page>
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
        </span>
        <span>Actualisation automatique toutes les 5 secondes</span>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
