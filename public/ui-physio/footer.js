'use strict';

(function initPhysioSphereFooter() {
  const renderFooter = () => {
    if (document.getElementById('globalFooter')) return;

    const footer = document.createElement('footer');
    footer.className = 'global-footer reveal-up';
    footer.id = 'globalFooter';
    footer.innerHTML = `
      <div class="footer-ambient" aria-hidden="true">
        <span></span><span></span><span></span>
      </div>
      <div class="footer-grid-overlay" aria-hidden="true"></div>
      <div class="footer-shell">
        <div class="footer-brand-card">
          <div class="footer-logo-wrap">
            <div class="footer-logo">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3L4 7.2v9.2L12 21l8-4.6V7.2L12 3Z" stroke="currentColor" stroke-width="1.7"/><path d="M8.2 12h7.6M12 8.2v7.6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
            </div>
            <span>Student Driven Platform</span>
          </div>
          <h2>Physio<span>Academy</span></h2>
          <p>A modern academic ecosystem designed for physiotherapy students to learn smarter, navigate syllabus easily, and access structured educational support.</p>
          <div class="footer-status-row">
            <span></span>
            Academic resources syncing across the learning academy
          </div>
        </div>

        <nav class="footer-column footer-links-group" aria-label="Quick links">
          <h3>Quick Links</h3>
          <a href="/">Home</a>
          <a href="/about">About</a>
          <a href="/topics">Topics</a>
          <a href="/exam-aid">Exam Aid</a>
          <a href="/search">Search</a>
          <a href="/bookmarks">Bookmarks</a>
          <a href="#ask-doubt">Ask Doubt</a>
          <a href="#request-topic">Request Topic</a>
        </nav>

        <div class="footer-column footer-support">
          <h3>Support</h3>
          <a href="#">FAQ</a>
          <a href="#">Feedback</a>
          <a href="#">Help Center</a>
          <a href="#">Contact Support</a>
          <a href="#">Privacy Policy</a>
          <a href="#">Terms & Conditions</a>
        </div>

        <div class="footer-column footer-contact">
          <h3>Contact</h3>
          <a href="https://www.physioacademy.com">
            <span>URL</span>
            www.physioacademy.com
          </a>
          <a href="mailto:support@physioacademy.com">
            <span>Email</span>
            support@physioacademy.com
          </a>
          <a href="tel:+910000000000">
            <span>Phone</span>
            +91 XXXXX XXXXX
          </a>
          <div class="footer-socials" aria-label="Social links">
            <a href="#" aria-label="Instagram">IG</a>
            <a href="#" aria-label="LinkedIn">in</a>
            <a href="#" aria-label="YouTube">YT</a>
            <a href="#" aria-label="Facebook">f</a>
          </div>
        </div>

        <div class="footer-newsletter">
          <h3>Subscribe Now</h3>
          <p>Get updates about important topics, exam resources, and new learning materials.</p>
          <form class="footer-subscribe-form" id="footerSubscribeForm">
            <label for="footerEmail" class="sr-only">Email address</label>
            <input id="footerEmail" type="email" placeholder="Enter your email" required>
            <button type="submit"><span>Subscribe</span></button>
          </form>
          <small>Join our YouTube & academic community.</small>
          <div class="footer-subscribe-message" id="footerSubscribeMessage" role="status" aria-live="polite"></div>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2026 Physio Academy || All rights reserved || Designed & Developed by <a href="https://www.rootflashtechnology.com/" target="_blank" rel="noopener noreferrer">Rootflash Technology</a>.</span>
        <span>Built for focused physiotherapy learning.</span>
      </div>
    `;

    document.body.appendChild(footer);
    initFooterSubscribe(footer);
    initFooterReveal(footer);
  };

  const initFooterSubscribe = footer => {
    const form = footer.querySelector('#footerSubscribeForm');
    const message = footer.querySelector('#footerSubscribeMessage');
    if (!form || !message) return;

    form.addEventListener('submit', event => {
      event.preventDefault();
      const button = form.querySelector('button');
      const input = form.querySelector('input');
      if (!input.value.trim()) return;

      button.classList.add('is-loading');
      button.disabled = true;
      message.textContent = '';

      setTimeout(() => {
        button.classList.remove('is-loading');
        button.classList.add('is-success');
        button.disabled = false;
        message.textContent = 'Subscribed successfully. Welcome to Physio Academy updates.';
        input.value = '';
        setTimeout(() => button.classList.remove('is-success'), 1500);
      }, 850);
    });
  };

  const initFooterReveal = footer => {
    if (!('IntersectionObserver' in window)) {
      footer.classList.add('visible');
      return;
    }

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    observer.observe(footer);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderFooter);
  } else {
    renderFooter();
  }
})();
