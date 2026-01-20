<div class="space-y-6">
    {{-- Header with badges --}}
    <div class="flex flex-wrap items-center gap-2">
        {{-- Type Badge --}}
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $record->type === 'incoming' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
            {{ $record->type === 'incoming' ? '← Entrant' : '→ Sortant' }}
        </span>

        {{-- Service Badge --}}
        @php
            $serviceColors = [
                'openai' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                'app' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                'catalogue' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'analytics' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
            ];
            $serviceColor = $serviceColors[$record->service] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium uppercase {{ $serviceColor }}">
            {{ $record->service }}
        </span>

        {{-- Method Badge --}}
        @php
            $methodColors = [
                'GET' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'POST' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                'PUT' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                'PATCH' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                'DELETE' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            ];
            $methodColor = $methodColors[$record->method] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $methodColor }}">
            {{ $record->method }}
        </span>

        {{-- Status Code Badge --}}
        @if($record->status_code)
            @php
                $statusColor = match(true) {
                    $record->status_code >= 200 && $record->status_code < 300 => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                    $record->status_code >= 400 && $record->status_code < 500 => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                    $record->status_code >= 500 => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                };
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                {{ $record->status_code }}
            </span>
        @endif
    </div>

    {{-- Info Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Endpoint</p>
            <p class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100 break-all">{{ $record->endpoint }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $record->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durée</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                @if($record->duration_ms)
                    <span class="{{ $record->duration_ms > 2000 ? 'text-red-600 dark:text-red-400' : ($record->duration_ms > 500 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400') }}">
                        {{ number_format($record->duration_ms) }} ms
                    </span>
                @else
                    <span class="text-gray-400">N/A</span>
                @endif
            </p>
        </div>
        @if($record->ip_address)
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Adresse IP</p>
            <p class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $record->ip_address }}</p>
        </div>
        @endif
        @if($record->user_agent)
        <div class="sm:col-span-2">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User Agent</p>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300 truncate">{{ $record->user_agent }}</p>
        </div>
        @endif
    </div>

    {{-- Error Message --}}
    @if($record->error_message)
    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm font-medium text-red-800 dark:text-red-300">Message d'erreur</p>
        </div>
        <p class="text-sm font-mono text-red-700 dark:text-red-400">{{ $record->error_message }}</p>
    </div>
    @endif

    {{-- Request Body --}}
    @if($record->request_body)
    <div>
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Request Body</p>
            <span class="text-xs text-gray-400">JSON</span>
        </div>
        <pre class="text-xs font-mono bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto max-h-64 scrollbar-thin">{{ json_encode($record->request_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif

    {{-- Response Body --}}
    @if($record->response_body)
    <div>
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Response Body</p>
            <span class="text-xs text-gray-400">JSON</span>
        </div>
        <pre class="text-xs font-mono bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto max-h-64 scrollbar-thin">{{ json_encode($record->response_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif
</div>
