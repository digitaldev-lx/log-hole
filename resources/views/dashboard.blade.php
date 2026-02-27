@extends('log-hole::layout')

@section('content')
<div x-data="{
    autoRefresh: {{ $autoRefresh ? 'true' : 'false' }},
    refreshInterval: null,
    startRefresh() {
        if (this.autoRefresh) {
            this.refreshInterval = setInterval(() => window.location.reload(), 5000);
        }
    },
    stopRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }
}" x-init="if (autoRefresh) startRefresh()" x-effect="autoRefresh ? startRefresh() : stopRefresh()">

    {{-- Stats Bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats->total }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total</div>
        </div>
        @foreach($levels as $level)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats->countForLevel($level) }}</div>
                <div class="text-xs uppercase tracking-wide">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $level->badgeClasses() }}">
                        {{ $level->value }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <form method="GET" action="{{ route('log-hole.dashboard') }}" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="flex-1 min-w-0">
                <label for="level" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Level</label>
                <select name="level" id="level" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Levels</option>
                    @foreach($levels as $level)
                        <option value="{{ strtolower($level->value) }}" {{ $filters['level'] === strtolower($level->value) ? 'selected' : '' }}>
                            {{ $level->value }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-[2] min-w-0">
                <label for="search" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ $filters['search'] }}" placeholder="Search messages..."
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex-1 min-w-0">
                <label for="from" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">From</label>
                <input type="date" name="from" id="from" value="{{ $filters['from'] }}"
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex-1 min-w-0">
                <label for="to" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">To</label>
                <input type="date" name="to" id="to" value="{{ $filters['to'] }}"
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors">
                    Filter
                </button>
                <a href="{{ route('log-hole.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Reset
                </a>
            </div>
        </form>

        {{-- Auto-refresh toggle --}}
        <div class="mt-3 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <label class="flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" x-model="autoRefresh" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                Auto-refresh (5s)
            </label>
        </div>
    </div>

    {{-- Log Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($logs->isEmpty())
            <div class="py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No logs found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if($filters['level'] || $filters['search'] || $filters['from'] || $filters['to'])
                        No logs match your current filters. Try adjusting your search criteria.
                    @else
                        Logs will appear here once your application starts logging.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Message</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Context</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Logged At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($logs as $log)
                            @php
                                $logLevel = \DigitalDevLx\LogHole\Enums\LogLevel::tryFrom(strtoupper($log->level)) ?? \DigitalDevLx\LogHole\Enums\LogLevel::Debug;
                                $loggedAt = $log->logged_at ? \Carbon\Carbon::parse($log->logged_at) : null;
                                $context = $log->context ? json_decode($log->context, true) : null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $logLevel->badgeClasses() }}">
                                        {{ $logLevel->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 max-w-md truncate" title="{{ $log->message }}">
                                    {{ $log->message }}
                                </td>
                                <td class="px-4 py-3 text-sm" x-data="{ open: false }">
                                    @if($context)
                                        <button @click="open = !open" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-xs font-medium transition-colors">
                                            <span x-text="open ? 'Hide' : 'Show'">Show</span> context
                                        </button>
                                        <div x-show="open" x-cloak x-transition class="mt-2">
                                            <pre class="bg-gray-100 dark:bg-gray-900 rounded p-2 text-xs text-gray-800 dark:text-gray-200 overflow-x-auto max-w-lg">{{ json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 text-xs">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" title="{{ $loggedAt?->toDateTimeString() }}">
                                    {{ $loggedAt?->diffForHumans() ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->links('log-hole::pagination') }}
            </div>
        @endif
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
