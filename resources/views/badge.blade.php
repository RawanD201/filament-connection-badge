@php
    use Illuminate\Support\Js;

    $pingUrl = config('filament-connection-badge.ping_url')
        ?: route('filament-connection-badge.ping');

    $config = [
        'pingUrl' => $pingUrl,
        'pingInterval' => (int) config('filament-connection-badge.ping_interval', 5000),
        'maxSamples' => (int) config('filament-connection-badge.max_samples', 30),
        'showOverlay' => (bool) config('filament-connection-badge.show_overlay', true),
        'thresholds' => [
            'full' => (int) config('filament-connection-badge.thresholds.full', 200),
            'medium' => (int) config('filament-connection-badge.thresholds.medium', 600),
        ],
        'labels' => [
            'full' => __('filament-connection-badge::messages.full'),
            'medium' => __('filament-connection-badge::messages.medium'),
            'low' => __('filament-connection-badge::messages.low'),
            'offline' => __('filament-connection-badge::messages.offline'),
        ],
    ];

    $showLabel = (bool) config('filament-connection-badge.show_label', true);
@endphp

<div
    x-data="filamentConnectionBadge({{ Js::from($config) }})"
    x-cloak
    class="fcb-wrapper"
    @mouseenter="showTooltip()"
    @mouseleave="hideTooltip()"
>
    <button
        type="button"
        class="fcb-badge"
        :class="'fcb-' + quality"
        :aria-label="label"
    >
        <svg class="fcb-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <rect
                x="2" y="15" width="4" height="7" rx="1"
                class="fcb-bar"
                :class="quality === 'offline' ? 'fcb-bar-inactive' : 'fcb-bar-active'"
            />
            <rect
                x="10" y="10" width="4" height="12" rx="1"
                class="fcb-bar"
                :class="['medium', 'full'].includes(quality) ? 'fcb-bar-active' : 'fcb-bar-inactive'"
            />
            <rect
                x="18" y="4" width="4" height="18" rx="1"
                class="fcb-bar"
                :class="quality === 'full' ? 'fcb-bar-active' : 'fcb-bar-inactive'"
            />
        </svg>

        @if ($showLabel)
            <span class="fcb-label" x-text="label"></span>
        @endif
    </button>

    <div
        class="fcb-tooltip"
        x-show="tooltipOpen"
        x-transition.opacity.duration.150ms
        x-cloak
    >
        <div class="fcb-tooltip-header">
            {{ __('filament-connection-badge::messages.tooltip_title') }}
        </div>

        <div class="fcb-tooltip-body">
            <div class="fcb-graph-wrap">
                <svg
                    class="fcb-graph"
                    viewBox="0 0 220 64"
                    preserveAspectRatio="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <line x1="6" y1="6"  x2="214" y2="6"  class="fcb-graph-grid" />
                    <line x1="6" y1="35" x2="214" y2="35" class="fcb-graph-grid" />
                    <line x1="6" y1="58" x2="214" y2="58" class="fcb-graph-grid" />
                    <path
                        :d="sparklinePath"
                        class="fcb-graph-line"
                        :class="'fcb-graph-line-' + quality"
                        fill="none"
                        stroke-width="1.75"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <span class="fcb-graph-max" x-text="sparklineMax + ' ms'"></span>
                <span class="fcb-graph-min">0</span>
            </div>

            <div class="fcb-stat fcb-stat-host">
                <span class="fcb-stat-value" x-text="host"></span>
            </div>

            <div class="fcb-stat">
                <span class="fcb-stat-label">{{ __('filament-connection-badge::messages.average_ping') }}</span>
                <span class="fcb-stat-value">
                    <template x-if="averagePing !== null"><span><span x-text="averagePing"></span> ms</span></template>
                    <template x-if="averagePing === null"><span>—</span></template>
                </span>
            </div>

            <div class="fcb-stat">
                <span class="fcb-stat-label">{{ __('filament-connection-badge::messages.last_ping') }}</span>
                <span class="fcb-stat-value">
                    <template x-if="lastPing !== null"><span><span x-text="lastPing"></span> ms</span></template>
                    <template x-if="lastPing === null"><span>—</span></template>
                </span>
            </div>

            <div class="fcb-stat">
                <span class="fcb-stat-label">{{ __('filament-connection-badge::messages.packet_loss') }}</span>
                <span
                    class="fcb-stat-value"
                    :class="packetLossRate > 10 ? 'fcb-stat-danger' : ''"
                    x-text="packetLossRate.toFixed(1) + '%'"
                ></span>
            </div>

            <p class="fcb-tooltip-help">{{ __('filament-connection-badge::messages.tooltip_help') }}</p>
        </div>
    </div>

    <template x-if="showOverlay && isOffline">
        <div class="fcb-overlay">
            <div class="fcb-overlay-card">
                <div class="fcb-overlay-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20h.01" />
                        <path d="M8.5 16.429a5 5 0 0 1 7 0" />
                        <path d="M5 12.859a10 10 0 0 1 5.17-2.69" />
                        <path d="M19 12.859a10 10 0 0 0-2.007-1.523" />
                        <path d="M2 8.82a15 15 0 0 1 4.177-2.643" />
                        <path d="M22 8.82a15 15 0 0 0-11.288-3.764" />
                        <path d="m2 2 20 20" />
                    </svg>
                </div>
                <h2 class="fcb-overlay-title">{{ __('filament-connection-badge::messages.overlay_title') }}</h2>
                <p class="fcb-overlay-text">{{ __('filament-connection-badge::messages.overlay_text') }}</p>
                <div class="fcb-overlay-spinner">
                    <svg class="fcb-spinner-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ __('filament-connection-badge::messages.overlay_reconnecting') }}</span>
                </div>
            </div>
        </div>
    </template>
</div>
