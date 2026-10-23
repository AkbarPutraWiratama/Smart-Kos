@props([
    'pageTitle' => 'Page',
    'breadcrumbs' => [],
])

@php
    $defaultHomeUrl = match(auth()->user()?->role) {
        'admin' => route('admin.dashboard'),
        'staff' => route('staff.dashboard'),
        'penyewa' => route('penyewa.dashboard'),
        default => url('/'),
    };
    $defaultHomeLabel = 'Dashboard';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
        {{ $pageTitle }}
    </h2>
    <nav aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5 flex-wrap">
            <li>
                <a
                    class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
                    href="{{ $defaultHomeUrl }}"
                >
                    {{ $defaultHomeLabel }}
                    <svg
                        class="stroke-current rtl:rotate-180"
                        width="17"
                        height="16"
                        viewBox="0 0 17 16"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                            stroke=""
                            stroke-width="1.2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </a>
            </li>

            @foreach ($breadcrumbs as $crumb)
                <li>
                    @if (!empty($crumb['url']))
                        <a
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
                            href="{{ $crumb['url'] }}"
                        >
                            {{ $crumb['label'] }}
                            <svg
                                class="stroke-current rtl:rotate-180"
                                width="17"
                                height="16"
                                viewBox="0 0 17 16"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                                    stroke=""
                                    stroke-width="1.2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                            {{ $crumb['label'] }}
                            <svg
                                class="stroke-current rtl:rotate-180"
                                width="17"
                                height="16"
                                viewBox="0 0 17 16"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                                    stroke=""
                                    stroke-width="1.2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </span>
                    @endif
                </li>
            @endforeach

            <li class="text-sm font-medium text-gray-800 dark:text-white/90">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>
</div>
