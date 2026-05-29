/**
 * Production Security & Content Protection Script
 * Protects against basic inspection and content copying.
 */
document.addEventListener('contextmenu', event => event.preventDefault());

document.onkeydown = function(e) {
    // Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
    if (e.keyCode == 123 || 
        (e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) || e.keyCode == 'J'.charCodeAt(0))) || 
        (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0))) {
        return false;
    }
    
    // Disable Copy (Ctrl+C)
    if (e.ctrlKey && e.keyCode == 'C'.charCodeAt(0)) {
        return false;
    }

    // Disable Print (Ctrl+P)
    if (e.ctrlKey && e.keyCode == 'P'.charCodeAt(0)) {
        return false;
    }
};

// Disable Image Dragging
document.addEventListener('dragstart', function(e) {
    if (e.target.nodeName === 'IMG') {
        e.preventDefault();
    }
}, false);

console.log('%c STOP! ', 'background: #ff0000; color: #ffffff; font-size: 50px; font-weight: bold;');
console.log('%cThis is a protected area. Unauthorized use of developer tools is prohibited.', 'font-size: 20px; color: #ff0000;');
