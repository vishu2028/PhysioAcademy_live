/**
 * Physio Academy Content Protection System
 * Provides multi-layer security for sensitive medical/academic content.
 */

(function() {
    "use strict";

    // Configuration
    const PROTECT_CLASS = 'v-protected-page';
    
    // Check if current page is protected
    function isProtected() {
        return document.body.classList.contains(PROTECT_CLASS);
    }

    // 1. Context Menu Protection
    document.addEventListener('contextmenu', event => {
        if (isProtected()) {
            event.preventDefault();
            return false;
        }
    });

    // 2. Keyboard Shortcut Protection
    document.onkeydown = function(e) {
        if (!isProtected()) return true;

        // F12 (123)
        // Ctrl+Shift+I (73)
        // Ctrl+Shift+J (74)
        // Ctrl+U (85)
        // Ctrl+C (67)
        // Ctrl+P (80)
        // Ctrl+S (83)
        const ForbiddenKeys = [123, 73, 74, 85, 67, 80, 83];
        
        if (ForbiddenKeys.includes(e.keyCode) && (e.ctrlKey || e.metaKey || e.keyCode === 123)) {
            if (e.keyCode === 67) {
                // Flash a small notice if they try to copy
                showSecurityNotice("Content Copying is Disabled");
            }
            return false;
        }
    };

    // 3. Image Drag Protection
    document.addEventListener('dragstart', function(e) {
        if (isProtected() && e.target.nodeName === 'IMG') {
            e.preventDefault();
        }
    }, false);

    // 4. Dynamic Watermark Injection
    function injectWatermark() {
        if (!isProtected() || document.getElementById('pa-watermark')) return;

        const watermark = document.createElement('div');
        watermark.id = 'pa-watermark';
        watermark.innerHTML = `
            <div class="watermark-inner">
                ${Array(50).fill('<span>Physio Academy Protection</span>').join('')}
            </div>
        `;
        document.body.appendChild(watermark);
    }

    // 5. Notice System
    function showSecurityNotice(msg) {
        let notice = document.getElementById('pa-security-notice');
        if (!notice) {
            notice = document.createElement('div');
            notice.id = 'pa-security-notice';
            document.body.appendChild(notice);
        }
        notice.innerText = msg;
        notice.classList.add('show');
        setTimeout(() => notice.classList.remove('show'), 2000);
    }

    // Initialize
    window.addEventListener('load', () => {
        if (isProtected()) {
            injectWatermark();
            console.log('%cContent Protected by Physio Academy Security Layer', 'color: #2563eb; font-weight: bold;');
        }
    });

})();
