<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Top 50 users (excluding suspended).
            </div>

            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Sort by</span>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="sort">
                        <option value="total_points">Total points</option>
                        <option value="discipline_score">Discipline score</option>
                        <option value="current_streak">Current streak</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-950">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">User</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Points</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Discipline</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Streak</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Longest</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($this->users() as $index => $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-950">
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $user->name }}
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ '@' . $user->username }} • {{ $user->email }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-200">{{ $user->total_points }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-200">{{ number_format((float) $user->discipline_score, 1) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-200">{{ $user->current_streak }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-200">{{ $user->longest_streak }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>

