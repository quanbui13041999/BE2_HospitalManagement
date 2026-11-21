<style>
    :root {
        --hc-font-sans: Inter, "DM Sans", "Be Vietnam Pro", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        --hc-text-base: 16px;
        --hc-line-base: 1.5;
    }

    :where(html) {
        font-size: var(--hc-text-base);
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    :where(body) {
        font-family: var(--hc-font-sans);
        font-size: 1rem;
        line-height: var(--hc-line-base);
        letter-spacing: 0;
        text-rendering: optimizeLegibility;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    :where(input, select, textarea, button) {
        font: inherit;
        letter-spacing: inherit;
    }

    :where(label, input, select, textarea, button, table, th, td) {
        line-height: 1.45;
    }

    :where(img, svg, video, canvas) {
        max-width: 100%;
    }

    :where(a, button, input, select, textarea) {
        -webkit-tap-highlight-color: transparent;
    }

    :where(.form-control, .form-select, .btn, .nav-link, .dropdown-item) {
        font-family: var(--hc-font-sans);
    }
</style>
