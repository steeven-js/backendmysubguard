<div class="space-y-4">
    {{-- Basic Info --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</p>
            <p class="text-sm">{{ $record->type === 'incoming' ? '← Entrant' : '→ Sortant' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Service</p>
            <p class="text-sm uppercase">{{ $record->service }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Méthode</p>
            <p class="text-sm">{{ $record->method }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Code HTTP</p>
            <p class="text-sm">{{ $record->status_code ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Durée</p>
            <p class="text-sm">{{ $record->duration_ms ? $record->duration_ms . ' ms' : 'N/A' }}</p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</p>
            <p class="text-sm">{{ $record->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    {{-- Endpoint --}}
    <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Endpoint</p>
        <p class="text-sm font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded">{{ $record->endpoint }}</p>
    </div>

    {{-- IP & User Agent --}}
    @if($record->ip_address || $record->user_agent)
    <div class="grid grid-cols-2 gap-4">
        @if($record->ip_address)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">IP</p>
            <p class="text-sm">{{ $record->ip_address }}</p>
        </div>
        @endif
        @if($record->user_agent)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">User Agent</p>
            <p class="text-sm text-xs truncate">{{ $record->user_agent }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Error Message --}}
    @if($record->error_message)
    <div>
        <p class="text-sm font-medium text-red-500">Erreur</p>
        <p class="text-sm font-mono bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 p-2 rounded">{{ $record->error_message }}</p>
    </div>
    @endif

    {{-- Request Body --}}
    @if($record->request_body)
    <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Request Body</p>
        <pre class="text-xs font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-x-auto max-h-48">{{ json_encode($record->request_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif

    {{-- Response Body --}}
    @if($record->response_body)
    <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Response Body</p>
        <pre class="text-xs font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-x-auto max-h-48">{{ json_encode($record->response_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif
</div>
