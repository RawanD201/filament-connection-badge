document.addEventListener('alpine:init', () => {
    Alpine.data('filamentConnectionBadge', (config = {}) => ({
        // ── configuration ────────────────────────────────────────────
        pingUrl: config.pingUrl || '/favicon.ico',
        pingInterval: config.pingInterval || 5000,
        showOverlay: config.showOverlay !== false,
        labels: config.labels || {},
        maxSamples: config.maxSamples || 30,
        thresholds: {
            full: (config.thresholds && config.thresholds.full) || 200,
            medium: (config.thresholds && config.thresholds.medium) || 600,
        },

        // ── state ────────────────────────────────────────────────────
        quality: 'full', // 'full' | 'medium' | 'low' | 'offline'
        lastPing: null,
        samples: [],     // [{ ts, ping }] — ping = null on failure
        tooltipOpen: false,

        _pingTimer: null,
        _hideTimer: null,

        init() {
            this.updateFromNavigator();

            window.addEventListener('online', () => this.handleOnline());
            window.addEventListener('offline', () => this.handleOffline());

            if ('connection' in navigator && navigator.connection) {
                navigator.connection.addEventListener('change', () => this.updateFromNavigator());
            }

            this.ping();
            this._pingTimer = setInterval(() => this.ping(), this.pingInterval);
        },

        destroy() {
            if (this._pingTimer) clearInterval(this._pingTimer);
            if (this._hideTimer) clearTimeout(this._hideTimer);
        },

        updateFromNavigator() {
            if (!navigator.onLine) {
                this.quality = 'offline';
            }
        },

        handleOnline() {
            this.ping();
        },

        handleOffline() {
            this.quality = 'offline';
            this.recordSample(null);
        },

        async ping() {
            if (!navigator.onLine) {
                this.quality = 'offline';
                this.recordSample(null);
                return;
            }

            const start = performance.now();
            try {
                const res = await fetch(this.pingUrl + '?_=' + Date.now(), {
                    method: 'GET',
                    cache: 'no-store',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                });

                if (!res.ok) {
                    this.recordSample(null);
                    this.quality = 'offline';
                    return;
                }

                const latency = Math.round(performance.now() - start);
                this.recordSample(latency);
                this.lastPing = latency;
                this.updateQualityFromLatency(latency);
            } catch (e) {
                this.recordSample(null);
                this.quality = 'offline';
            }
        },

        recordSample(ping) {
            this.samples.push({ ts: Date.now(), ping });
            if (this.samples.length > this.maxSamples) {
                this.samples.shift();
            }
        },

        updateQualityFromLatency(latency) {
            if (latency < this.thresholds.full) {
                this.quality = 'full';
            } else if (latency < this.thresholds.medium) {
                this.quality = 'medium';
            } else {
                this.quality = 'low';
            }
        },

        showTooltip() {
            if (this._hideTimer) {
                clearTimeout(this._hideTimer);
                this._hideTimer = null;
            }
            this.tooltipOpen = true;
        },

        hideTooltip() {
            this._hideTimer = setTimeout(() => {
                this.tooltipOpen = false;
            }, 150);
        },

        // ── derived getters ──────────────────────────────────────────
        get label() {
            return this.labels[this.quality] || this.quality;
        },

        get isOffline() {
            return this.quality === 'offline';
        },

        get host() {
            try {
                return new URL(this.pingUrl, window.location.origin).host;
            } catch (e) {
                return window.location.host;
            }
        },

        get successfulSamples() {
            return this.samples.filter((s) => s.ping !== null);
        },

        get averagePing() {
            const ok = this.successfulSamples;
            if (ok.length === 0) return null;
            return Math.round(ok.reduce((sum, s) => sum + s.ping, 0) / ok.length);
        },

        get packetLossRate() {
            if (this.samples.length === 0) return 0;
            const failed = this.samples.filter((s) => s.ping === null).length;
            return (failed / this.samples.length) * 100;
        },

        get sparklineMax() {
            const ok = this.successfulSamples.map((s) => s.ping);
            if (ok.length === 0) return 100;
            return Math.max(100, Math.ceil(Math.max(...ok) / 50) * 50);
        },

        get sparklinePath() {
            if (this.successfulSamples.length < 2) return '';

            const width = 220;
            const height = 64;
            const padding = 6;
            const innerW = width - padding * 2;
            const innerH = height - padding * 2;

            const max = this.sparklineMax;
            const stepX = innerW / (this.maxSamples - 1);

            let d = '';
            let started = false;
            this.samples.forEach((s, i) => {
                if (s.ping === null) {
                    started = false; // break the line on failed samples
                    return;
                }
                const x = padding + i * stepX;
                const y = padding + (1 - s.ping / max) * innerH;
                d += (started ? ' L ' : ' M ') + x.toFixed(1) + ' ' + y.toFixed(1);
                started = true;
            });

            return d.trim();
        },
    }));
});
