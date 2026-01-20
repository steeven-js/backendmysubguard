<x-filament-panels::page>
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <x-heroicon-o-signal class="w-5 h-5 text-green-500 animate-pulse" />
        <span>Actualisation automatique toutes les 5 secondes</span>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
