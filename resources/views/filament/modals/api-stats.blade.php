<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Calls in Current Second</div>
            <div class="text-2xl font-bold">{{ $stats['calls_in_current_second'] }} / 5</div>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Calls Today</div>
            <div class="text-2xl font-bold">{{ number_format($stats['calls_today']) }}</div>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Remaining Today</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['remaining_today']) }}</div>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Usage Percentage</div>
            <div class="text-2xl font-bold">{{ $stats['percentage_used'] }}%</div>
        </div>
    </div>

    <div class="text-sm text-gray-500 dark:text-gray-400">
        Daily limit: 432,000 requests
    </div>
</div>
