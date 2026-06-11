<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Multi-Currency Payment API — Docs</title>
    <script type="module" src="https://unpkg.com/rapidoc@9.3.8/dist/rapidoc-min.js"></script>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; }
    </style>
</head>
<body>
    <rapi-doc
        id="api-docs"
        spec-url="{{ url('docs') }}"
        render-style="focused"
        nav-bg-color="#1f2937"
        bg-color="#111827"
        text-color="#e5e7eb"
        primary-color="#10b981"
        show-header="false"
        allow-authentication="true"
        allow-server-selection="false"
        allow-spec-url-load="false"
        allow-spec-file-load="false"
        allow-search="false"
        allow-advanced-search="false"
        schema-style="table"
        default-schema-tab="example"
        sort-endpoints-by="none"
        fetch-credentials="same-origin"
    >
    </rapi-doc>

    <script>
        const rapidoc = document.getElementById('api-docs');

        // Always request JSON so the API never falls back to an HTML response.
        rapidoc.addEventListener('before-try', (e) => {
            e.detail.request.headers.append('Accept', 'application/json');
        });

        // Hide RapiDoc's internal "collapse/expand all + OPERATIONS" toolbar
        // from the side navigation. It lives inside the component shadow DOM,
        // so it can only be reached after the component has rendered.

        function hideNavToolbar() {
            const root = rapidoc && rapidoc.shadowRoot;
            if (!root) {
                return false;
            }

            let done = false;

            // Hide the search/filter box at the top of the side navigation.
            const search = root.querySelector('.nav-bar-search, .nav-bar-search-wrapper');
            if (search) {
                const searchBox = search.closest('.nav-bar-info') ? search : (search.parentElement || search);
                searchBox.style.display = 'none';
                done = true;
            }

            // Hide the "collapse/expand all + OPERATIONS" toolbar.
            const collapseControl = root.querySelector('.nav-bar-collapse-all');
            if (collapseControl) {
                const toolbar = collapseControl.closest('.nav-bar-tag-and-paths') || collapseControl.parentElement;
                if (toolbar) {
                    toolbar.style.display = 'none';
                    done = true;
                }
            } else {
                // Fallback: match the "OPERATIONS" label row directly.
                const rows = root.querySelectorAll('nav *');
                for (const el of rows) {
                    if (el.children.length <= 3 && el.textContent.trim().toUpperCase() === 'OPERATIONS') {
                        el.style.display = 'none';
                        done = true;
                        break;
                    }
                }
            }

            return done;
        }

        rapidoc.addEventListener('spec-loaded', () => {
            // Retry briefly while RapiDoc finishes painting the nav.
            let attempts = 0;
            const timer = setInterval(() => {
                if (hideNavToolbar() || ++attempts > 20) {
                    clearInterval(timer);
                }
            }, 100);
        });
    </script>
</body>
</html>
