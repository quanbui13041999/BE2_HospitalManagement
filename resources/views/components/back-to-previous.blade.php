@once
    <style>
        .app-back-button {
            position: fixed;
            left: 18px;
            bottom: 18px;
            z-index: 2147483000;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(37, 99, 235, 0.22);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.94);
            color: #1d4ed8;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 10px 15px;
            font: 700 13px/1.2 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .app-back-button:hover {
            transform: translateY(-1px);
            background: #fff;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.2);
        }

        .app-back-button[hidden] {
            display: none !important;
        }

        @media (max-width: 640px) {
            .app-back-button {
                left: 12px;
                bottom: 12px;
                padding: 9px 12px;
                font-size: 12px;
            }
        }

        @media print {
            .app-back-button {
                display: none !important;
            }
        }
    </style>

    <button type="button" id="app-back-button" class="app-back-button" hidden aria-label="Quay lại trang trước">
        <span aria-hidden="true">←</span>
        <span>Quay lại</span>
    </button>

    @php
        $homeUrl = route('home');
        $stopPaths = [parse_url($homeUrl, PHP_URL_PATH) ?: '/'];

        if (\Illuminate\Support\Facades\Route::has('Home.trangchu')) {
            $trangChuPath = parse_url(route('Home.trangchu'), PHP_URL_PATH);
            if ($trangChuPath) {
                $stopPaths[] = $trangChuPath;
            }
        }

        $stopPaths = array_values(array_unique($stopPaths));
    @endphp

    <script type="application/json" id="app-back-config">
        {!! json_encode(['homeUrl' => $homeUrl, 'stopPaths' => $stopPaths], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <script>
        (function () {
            const configEl = document.getElementById('app-back-config');
            const config = configEl ? JSON.parse(configEl.textContent || '{}') : {};
            const homeUrl = config.homeUrl || '/';
            const stopPaths = config.stopPaths || ['/'];
            const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
            const button = document.getElementById('app-back-button');

            const normalizedStopPaths = stopPaths.map(path => (path || '/').replace(/\/+$/, '') || '/');

            if (!button || normalizedStopPaths.includes(currentPath)) {
                return;
            }

            button.hidden = false;
            button.addEventListener('click', function () {
                const referrer = document.referrer ? new URL(document.referrer) : null;

                if (referrer && referrer.origin === window.location.origin && referrer.href !== window.location.href) {
                    window.history.back();
                    return;
                }

                window.location.href = homeUrl;
            });
        })();
    </script>
@endonce
