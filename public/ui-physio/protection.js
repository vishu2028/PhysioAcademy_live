(function () {
    'use strict';

    /* ─── CONFIG ─────────────────────────────────────────────── */
    const BODY          = document.body;
    const IS_PROTECTED  = BODY.dataset.protected === 'true';
    const SITE_NAME     = BODY.dataset.siteName  || 'Physio Academy';
    const USER_EMAIL    = BODY.dataset.userEmail || '';
    const USER_ID       = BODY.dataset.userId    || '';

    // POLICY SETTINGS (from Admin Panel)
    const POLICY = {
        rightClick: BODY.dataset.protectionRightClick === '1',
        devtools:   BODY.dataset.protectionDevtools   === '1',
        copy:       BODY.dataset.protectionCopy       === '1',
        drag:       BODY.dataset.protectionDrag       === '1',
        watermark:  BODY.dataset.protectionWatermark  === '1'
    };

    if (!IS_PROTECTED) return;

    /* ─── 1. DISABLE RIGHT-CLICK ─────────────────────────────── */
    if (POLICY.rightClick) {
        window.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            _notice('Right-click is disabled for content protection.');
            return false;
        }, true);
    }

    /* ─── 2. BLOCK KEYBOARD SHORTCUTS ───────────────────────── */
    window.addEventListener('keydown', function (e) {
        const key   = e.keyCode || e.which;
        const ctrl  = e.ctrlKey || e.metaKey;
        const shift = e.shiftKey;
        const alt   = e.altKey;

        // F12 (DevTools)
        if (POLICY.devtools && key === 123) {
            _abort(e, 'Developer Tools are restricted.');
        }

        // Ctrl+Shift+I / J / C / K (DevTools)
        if (POLICY.devtools && ctrl && shift && [73, 74, 67, 75].includes(key)) {
            _abort(e, 'Inspector access is restricted.');
        }

        // Ctrl+U (View Source)
        if (POLICY.devtools && ctrl && key === 85) {
            _abort(e, 'Source viewing is restricted.');
        }

        // Ctrl+S (Save)
        if (ctrl && key === 83) {
            _abort(e, 'Page saving is restricted.');
        }

        // Ctrl+P (Print)
        if (POLICY.copy && ctrl && key === 80) {
            _abort(e, 'Printing is disabled.');
        }

        // Ctrl+C / Ctrl+X / Ctrl+V (Copy/Cut/Paste)
        if (POLICY.copy && ctrl && [67, 88].includes(key)) {
            _abort(e, 'Copying is disabled.');
        }
        
        // Ctrl+A (Select All)
        if (POLICY.copy && ctrl && key === 65) {
            _abort(e);
        }

        // Mac Shortcuts (Cmd+Option+I, etc)
        if (POLICY.devtools && ctrl && alt && [73, 74, 67].includes(key)) {
             _abort(e, 'Inspector access is restricted.');
        }
    }, true);

    function _abort(e, msg = null) {
        e.preventDefault();
        e.stopPropagation();
        if (msg) _notice(msg);
        return false;
    }

    /* ─── 3. DISABLE TEXT SELECTION ──────────────────────────── */
    if (POLICY.copy) {
        const noSelectStyle = document.createElement('style');
        noSelectStyle.textContent = `
            * {
                -webkit-user-select: none !important;
                -moz-user-select: none !important;
                -ms-user-select: none !important;
                user-select: none !important;
                -webkit-touch-callout: none !important;
            }
            input, textarea, [contenteditable="true"] {
                -webkit-user-select: text !important;
                user-select: text !important;
            }
        `;
        document.head.appendChild(noSelectStyle);

        document.addEventListener('selectstart', e => {
            if (!['INPUT', 'TEXTAREA'].includes(e.target.tagName)) e.preventDefault();
        });
    }

    /* ─── 4. DISABLE IMAGE DRAGGING ──────────────────────────── */
    if (POLICY.drag) {
        document.addEventListener('dragstart', e => {
            if (e.target.tagName === 'IMG') e.preventDefault();
        });
        const imgStyle = document.createElement('style');
        imgStyle.textContent = `img { -webkit-user-drag: none; pointer-events: none; }`;
        document.head.appendChild(imgStyle);
    }

    /* ─── 5. DYNAMIC WATERMARK ──────────────────────────── */
    function injectWatermark() {
        if (!POLICY.watermark) return;
        
        const wmContainer = document.createElement('div');
        wmContainer.id = 'physio-protected-watermark';
        
        const text = `${SITE_NAME} · ${USER_EMAIL || 'Protected Content'} · ${new Date().toLocaleDateString()}`;
        const tiles = Array(60).fill(`<span>${text}</span>`).join('');
        
        wmContainer.innerHTML = `<div class="wm-track">${tiles}</div>`;
        
        const style = document.createElement('style');
        style.textContent = `
            #physio-protected-watermark {
                position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                pointer-events: none; z-index: 999999; overflow: hidden; opacity: 0.05;
            }
            .wm-track {
                display: flex; flex-wrap: wrap; gap: 100px;
                transform: rotate(-25deg) scale(2); width: 250%; height: 250%;
                position: absolute; top: -50%; left: -50%;
                justify-content: center; align-content: center;
            }
            .wm-track span {
                font-size: 14px; font-weight: 900; color: #1e293b;
                white-space: nowrap; font-family: sans-serif;
            }
        `;
        document.head.appendChild(style);
        document.body.appendChild(wmContainer);
        
        // Anti-deletion logic
        const observer = new MutationObserver(() => {
            if (!document.getElementById('physio-protected-watermark')) document.body.appendChild(wmContainer);
        });
        observer.observe(document.body, { childList: true });
    }

    /* ─── 6. TOAST NOTICE ─────────────────────────────── */
    function _notice(msg) {
        let toast = document.getElementById('pa-security-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'pa-security-toast';
            const s = document.createElement('style');
            s.textContent = `
                #pa-security-toast {
                    position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px);
                    background: #0f172a; color: white; padding: 12px 24px; border-radius: 50px;
                    font-size: 0.8rem; font-weight: 700; z-index: 1000000; transition: transform 0.3s;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                }
                #pa-security-toast.active { transform: translateX(-50%) translateY(0); }
            `;
            document.head.appendChild(s);
            document.body.appendChild(toast);
        }
        toast.textContent = '🔒 ' + msg;
        toast.classList.add('active');
        clearTimeout(window._paToastTimer);
        window._paToastTimer = setTimeout(() => toast.classList.remove('active'), 2500);
    }

    if (document.readyState === 'complete') injectWatermark();
    else window.addEventListener('load', injectWatermark);

})();
