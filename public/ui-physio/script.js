/* ═══════════════════════════════════════════════════════════════════
   PHYSIO ACADEMY — PREMIUM JAVASCRIPT
   ═══════════════════════════════════════════════════════════════════ */

'use strict';

// ── DOM READY ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initScrollProgress();
  initNavbar();
  initNavbarDropdowns();
  initHamburger();
  initRevealAnimations();
  initCounters();
  initParticles();
  initTiltCards();
  initSearchOverlay();
  initAuthModal();
  initDoubtForm();
  initBackToTop();
  initActiveNav();
  initSaveButtons();
  initSearchInput();
  initExamAidPage();
  initCurriculumCardRedirect();
});

// ── CUSTOM CURSOR ────────────────────────────────────────────────────
function initCursor() {
  const dot = document.getElementById('cursorDot');
  const ring = document.getElementById('cursorRing');
  if (!dot || !ring) return;

  let mouseX = 0, mouseY = 0;
  let ringX = 0, ringY = 0;
  let rafId;

  const hoverTargets = 'a, button, [data-tilt], .trending-card, .curriculum-card, .support-card, .resource-card, input, select, textarea';

  document.addEventListener('mousemove', e => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    dot.style.left = mouseX + 'px';
    dot.style.top = mouseY + 'px';
  });

  document.addEventListener('mouseover', e => {
    if (e.target.closest(hoverTargets)) {
      ring.classList.add('hover');
    }
  });
  document.addEventListener('mouseout', e => {
    if (e.target.closest(hoverTargets)) {
      ring.classList.remove('hover');
    }
  });

  function animateRing() {
    ringX += (mouseX - ringX) * 0.12;
    ringY += (mouseY - ringY) * 0.12;
    ring.style.left = ringX + 'px';
    ring.style.top = ringY + 'px';
    rafId = requestAnimationFrame(animateRing);
  }
  animateRing();

  // Hide on mobile
  if (window.matchMedia('(hover: none)').matches) {
    dot.style.display = 'none';
    ring.style.display = 'none';
  }
}

// ── SCROLL PROGRESS ──────────────────────────────────────────────────
function initScrollProgress() {
  const bar = document.getElementById('scrollProgress');
  if (!bar) return;
  window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    bar.style.width = (scrollTop / docHeight * 100) + '%';
  }, { passive: true });
}

// ── NAVBAR ────────────────────────────────────────────────────────────
function initNavbar() {
  const navbar = document.getElementById('navbar');
  if (!navbar) return;
  let lastScroll = 0;

  window.addEventListener('scroll', () => {
    const scroll = window.scrollY;
    if (scroll > 20) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    lastScroll = scroll;
  }, { passive: true });
}

// ── NAVBAR DROPDOWNS ──────────────────────────────────────────────────
function initNavbarDropdowns() {
  const dropdowns = document.querySelectorAll('.nav-dropdown');
  
  dropdowns.forEach(dropdown => {
    const toggle = dropdown.querySelector('.nav-dropdown-toggle');
    const menu = dropdown.querySelector('.nav-dropdown-menu');
    
    if (!toggle || !menu) return;
    
    // Toggle dropdown on click
    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      // Close other dropdowns
      dropdowns.forEach(other => {
        if (other !== dropdown) {
          other.classList.remove('active');
        }
      });
      // Toggle current dropdown
      dropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking on menu items
    menu.querySelectorAll('.nav-dropdown-item').forEach(item => {
      item.addEventListener('click', () => {
        dropdown.classList.remove('active');
      });
    });
  });
  
  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-dropdown')) {
      dropdowns.forEach(dropdown => {
        dropdown.classList.remove('active');
      });
    }
  });
  
  // Close dropdowns on escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      dropdowns.forEach(dropdown => {
        dropdown.classList.remove('active');
      });
    }
  });
}

// ── HAMBURGER MENU ────────────────────────────────────────────────────
function initHamburger() {
  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('navLinks');
  if (!hamburger || !navLinks) return;

  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navLinks.classList.toggle('open');
    document.body.style.overflow = navLinks.classList.contains('open') ? 'hidden' : '';
  });

  // Close on nav link click
  navLinks.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      hamburger.classList.remove('active');
      navLinks.classList.remove('open');
      document.body.style.overflow = '';
    });
  });
}

// ── REVEAL ANIMATIONS (IntersectionObserver) ─────────────────────────
function initRevealAnimations() {
  const revealEls = document.querySelectorAll('.reveal-up, .reveal-left, .reveal-right');
  const staggerEls = document.querySelectorAll('.reveal-stagger');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

  revealEls.forEach(el => observer.observe(el));
  staggerEls.forEach(el => observer.observe(el));
}

// ── ANIMATED COUNTERS ─────────────────────────────────────────────────
function initCounters() {
  const counters = document.querySelectorAll('[data-count]');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.getAttribute('data-count'));
        animateCounter(el, target);
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(el => observer.observe(el));
}

function animateCounter(el, target) {
  const duration = 2000;
  const start = performance.now();
  const easeOut = t => 1 - Math.pow(1 - t, 3);

  function update(now) {
    const elapsed = Math.min((now - start) / duration, 1);
    const value = Math.round(easeOut(elapsed) * target);
    el.textContent = value.toLocaleString();
    if (elapsed < 1) requestAnimationFrame(update);
    else el.textContent = target.toLocaleString();
  }
  requestAnimationFrame(update);
}

// ── PARTICLES ─────────────────────────────────────────────────────────
function initParticles() {
  const field = document.getElementById('particleField');
  if (!field) return;

  const count = window.innerWidth < 768 ? 15 : 30;

  for (let i = 0; i < count; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    const size = Math.random() * 2 + 1;
    const x = Math.random() * 100;
    const delay = Math.random() * 15;
    const duration = Math.random() * 20 + 15;
    const drift = (Math.random() - 0.5) * 200;

    p.style.cssText = `
      left: ${x}%;
      width: ${size}px; height: ${size}px;
      animation-duration: ${duration}s;
      animation-delay: -${delay}s;
      --drift: ${drift}px;
      opacity: ${Math.random() * 0.5 + 0.1};
    `;
    field.appendChild(p);
  }
}

// ── TILT EFFECT ────────────────────────────────────────────────────────
function initTiltCards() {
  const cards = document.querySelectorAll('[data-tilt]');
  if (window.matchMedia('(hover: none)').matches) return;

  cards.forEach(card => {
    card.addEventListener('mousemove', e => {
      const rect = card.getBoundingClientRect();
      const x = (e.clientX - rect.left - rect.width / 2) / rect.width;
      const y = (e.clientY - rect.top - rect.height / 2) / rect.height;
      card.style.transform = `perspective(800px) rotateY(${x * 8}deg) rotateX(${-y * 8}deg) translateY(-4px)`;
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
  });
}

// ── CURRICULUM CARD REDIRECT ────────────────────────────────────────────
function initCurriculumCardRedirect() {
  document.addEventListener('click', (e) => {
    const card = e.target.closest('.curriculum-card');
    if (!card) return;

    const link = card.querySelector('.cc-btn');
    if (link && link.href) {
      e.preventDefault();
      window.location.href = link.href;
    }
  });

  const cards = document.querySelectorAll('.curriculum-card');
  cards.forEach(card => {
    card.style.cursor = 'pointer';
    card.style.pointerEvents = 'auto';
  });
}

// ── SEARCH OVERLAY ─────────────────────────────────────────────────────
function initSearchOverlay() {
  const overlay = document.getElementById('searchOverlay');
  const toggle = document.getElementById('searchToggle');
  const close = document.getElementById('searchClose');
  const input = document.getElementById('mainSearch');
  if (!overlay || !toggle) return;

  function openSearch() {
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => input && input.focus(), 200);
  }
  function closeSearch() {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
    if (input) {
      input.value = '';
      showSuggestions();
    }
  }

  toggle.addEventListener('click', openSearch);
  close && close.addEventListener('click', closeSearch);

  // Close on backdrop click
  overlay.querySelector('.search-overlay-bg')?.addEventListener('click', closeSearch);

  // Keyboard shortcut
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      overlay.classList.contains('active') ? closeSearch() : openSearch();
    }
    if (e.key === 'Escape') closeSearch();
  });
}

// ── SEARCH INPUT ────────────────────────────────────────────────────────
function initSearchInput() {
  const input = document.getElementById('mainSearch');
  const suggestionsEl = document.getElementById('searchSuggestions');
  const noResult = document.getElementById('searchNoResult');
  const noResultQuery = document.getElementById('noResultQuery');
  if (!input) return;

  const topics = ['Brachial Plexus', 'Gait Cycle', 'UMN vs LMN Lesions', 'Muscle Contraction', 'Reflex Arc', 'Spinal Cord Tracts', 'Muscle Spindle', 'Cerebellum', 'Anatomy', 'Physiology', 'Biomechanics', 'Electrotherapy', 'Neurology', 'Orthopaedics', 'Paediatrics', 'Sports Physiotherapy'];

  function showSuggestions() {
    if (suggestionsEl) suggestionsEl.style.display = 'block';
    if (noResult) noResult.style.display = 'none';
  }
  function showNoResult(query) {
    if (suggestionsEl) suggestionsEl.style.display = 'none';
    if (noResult) noResult.style.display = 'block';
    if (noResultQuery) noResultQuery.textContent = query;
  }

  window.showSuggestions = showSuggestions;

  let debounceTimer;
  input.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const query = input.value.trim().toLowerCase();
      if (!query) { showSuggestions(); return; }
      const found = topics.some(t => t.toLowerCase().includes(query));
      if (found) showSuggestions();
      else showNoResult(input.value.trim());
    }, 300);
  });
}

// ── AUTH MODAL ─────────────────────────────────────────────────────────
function initAuthModal() {
  const overlay = document.getElementById('authOverlay');
  const openLogin = document.getElementById('openLogin');
  const openRegister = document.getElementById('openRegister');
  const closeBtn = document.getElementById('authClose');
  const tabs = document.querySelectorAll('.auth-tab');
  const slider = document.getElementById('authTabSlider');
  const loginPanel = document.getElementById('loginPanel');
  const registerPanel = document.getElementById('registerPanel');
  if (!overlay) return;

  function openModal(tab = 'login') {
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    switchTab(tab);
  }
  function closeModal() {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }
  function switchTab(tab) {
    tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
    if (slider) slider.classList.toggle('right', tab === 'register');
    if (loginPanel) loginPanel.classList.toggle('hidden', tab !== 'login');
    if (registerPanel) registerPanel.classList.toggle('hidden', tab !== 'register');
  }

  closeBtn?.addEventListener('click', closeModal);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

  tabs.forEach(tab => {
    tab.addEventListener('click', () => switchTab(tab.dataset.tab));
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && overlay.classList.contains('active')) closeModal();
  });
}

// Global password toggle
window.togglePass = function(id) {
  const el = document.getElementById(id);
  if (el) el.type = el.type === 'password' ? 'text' : 'password';
};

// ── DOUBT FORM ─────────────────────────────────────────────────────────
function initDoubtForm() {
  const btn = document.getElementById('submitDoubt');
  const success = document.getElementById('formSuccess');
  if (!btn) return;

  btn.addEventListener('click', () => {
    const year = document.getElementById('doubtYear')?.value;
    const subject = document.getElementById('doubtSubject')?.value;
    const topic = document.getElementById('doubtTopic')?.value?.trim();
    const message = document.getElementById('doubtMessage')?.value?.trim();

    if (!year || !subject || !topic || !message) {
      // Shake effect on missing fields
      btn.style.animation = 'none';
      setTimeout(() => {
        btn.style.animation = 'shakeX 0.4s ease';
      }, 10);
      return;
    }

    if (success) success.style.display = 'flex';
    setTimeout(() => {
      if (success) success.style.display = 'none';
      ['doubtYear', 'doubtSubject', 'doubtTopic', 'doubtMessage'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });
    }, 3500);
  });
}

// ── BACK TO TOP ─────────────────────────────────────────────────────────
function initBackToTop() {
  const btn = document.getElementById('backToTop');
  if (!btn) return;

  window.addEventListener('scroll', () => {
    btn.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

// ── ACTIVE NAV HIGHLIGHTING ─────────────────────────────────────────────
function initActiveNav() {
  // Robust active nav detection using resolved URLs
  const navAnchors = Array.from(document.querySelectorAll('.nav-link')).filter(n => n.tagName.toLowerCase() === 'a');
  const navToggles = Array.from(document.querySelectorAll('.nav-dropdown-toggle'));

  const currentUrl = new URL(window.location.href);
  const currentPageName = currentUrl.pathname.split('/').pop() || 'index.html';

  // Clear existing active states
  document.querySelectorAll('.nav-link.active, .nav-dropdown-toggle.active').forEach(el => el.classList.remove('active'));

  // Mark direct anchor links active when their resolved filename matches current page
  navAnchors.forEach(a => {
    const href = a.getAttribute('href');
    if (!href) return;
    try {
      const resolved = new URL(href, currentUrl);
      const name = resolved.pathname.split('/').pop();
      if (name === currentPageName) a.classList.add('active');
    } catch (err) {
      // ignore invalid URLs
    }
  });

  // For dropdowns, mark toggle active if any dropdown-item resolves to current page
  document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
    const toggle = dropdown.querySelector('.nav-dropdown-toggle');
    const items = dropdown.querySelectorAll('.nav-dropdown-item');
    let matched = false;
    items.forEach(item => {
      const href = item.getAttribute('href');
      if (!href) return;
      try {
        const resolved = new URL(href, currentUrl);
        const name = resolved.pathname.split('/').pop();
        if (name === currentPageName) matched = true;
      } catch (err) {}
    });
    if (matched && toggle) toggle.classList.add('active');
  });

  // If we're on the home page path, also observe sections for in-page highlighting
  const sections = document.querySelectorAll('section[id]');
  if (sections.length > 0 && currentPageName === 'index.html') {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.id;
          document.querySelectorAll('.nav-link[data-section]').forEach(link => {
            link.classList.toggle('active', link.dataset.section === id);
          });
        }
      });
    }, { threshold: 0.4 });
    sections.forEach(s => observer.observe(s));
  }
}

// ── SAVE / BOOKMARK BUTTONS ─────────────────────────────────────────────
function initSaveButtons() {
  document.querySelectorAll('.tc-save-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.classList.toggle('saved');
      const isSaved = btn.classList.contains('saved');
      btn.style.transform = 'scale(1.3)';
      btn.style.color = isSaved ? 'var(--cyan)' : '';
      btn.style.borderColor = isSaved ? 'rgba(37,99,235,0.4)' : '';
      setTimeout(() => { btn.style.transform = ''; }, 200);

      // Mini toast
      showToast(isSaved ? 'Saved to Bookmarks' : 'Removed from Bookmarks');
    });
  });
}

// ── TOAST NOTIFICATION ──────────────────────────────────────────────────
function showToast(message) {
  const existing = document.querySelector('.toast-notification');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = 'toast-notification';
  toast.textContent = message;
  toast.style.cssText = `
    position: fixed; bottom: 90px; left: 50%; transform: translateX(-50%);
    background: rgba(255,255,255,0.85); border: 1px solid rgba(37,99,235,0.25);
    color: var(--text-primary); padding: 10px 20px; border-radius: 99px;
    font-size: 0.85rem; font-weight: 500; z-index: 9998;
    backdrop-filter: blur(20px); box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    animation: fadeInUp 0.3s ease;
  `;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s';
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}

// ── EXAM AID PAGE ───────────────────────────────────────────────────────
function initExamAidPage() {
  const page = document.querySelector('.exam-aid-page');
  if (!page) return;

  const college = document.getElementById('examCollegeFilter');
  const year = document.getElementById('examYearFilter');
  const semester = document.getElementById('examSemesterFilter');
  const subjectSearch = document.getElementById('examSubjectSearch');
  const navSearch = document.getElementById('examNavSearch');
  const cards = Array.from(document.querySelectorAll('.exam-subject-card'));
  const empty = document.getElementById('examEmptyState');
  const quickButtons = document.querySelectorAll('[data-exam-filter]');

  const setLoading = () => {
    const grid = document.getElementById('examSubjectGrid');
    if (!grid) return;
    grid.classList.add('is-loading');
    setTimeout(() => grid.classList.remove('is-loading'), 320);
  };

  const filterCards = () => {
    const collegeValue = college?.value || 'all';
    const yearValue = year?.value || 'all';
    const semesterValue = semester?.value || 'all';
    const query = ((subjectSearch?.value || navSearch?.value || '')).trim().toLowerCase();
    let visibleCount = 0;

    cards.forEach(card => {
      const matchesCollege = collegeValue === 'all' || card.dataset.college.includes(collegeValue);
      const matchesYear = yearValue === 'all' || card.dataset.year === yearValue;
      const matchesSemester = semesterValue === 'all' || card.dataset.semester === semesterValue;
      const matchesQuery = !query || card.dataset.subject.includes(query) || card.textContent.toLowerCase().includes(query);
      const isVisible = matchesCollege && matchesYear && matchesSemester && matchesQuery;

      card.hidden = !isVisible;
      if (isVisible) visibleCount += 1;
    });

    if (empty) empty.classList.toggle('is-visible', visibleCount === 0);
  };

  [college, year, semester, subjectSearch, navSearch].forEach(control => {
    if (!control) return;
    control.addEventListener('input', () => {
      setLoading();
      filterCards();
    });
    control.addEventListener('change', () => {
      setLoading();
      filterCards();
    });
  });

  quickButtons.forEach(button => {
    button.addEventListener('click', () => {
      quickButtons.forEach(item => item.classList.remove('active'));
      button.classList.add('active');
      showToast(`Loading ${button.textContent.replace(/[0-9]/g, '').trim()}...`);
      document.getElementById('exam-resources')?.scrollIntoView({ behavior: 'smooth' });
    });
  });

  document.querySelectorAll('.exam-accordion-toggle').forEach(toggle => {
    toggle.addEventListener('click', () => {
      const card = toggle.closest('.exam-subject-card');
      const panel = card?.querySelector('.exam-accordion-panel');
      const icon = toggle.querySelector('span');
      const open = card?.classList.toggle('is-open');
      if (panel) panel.style.maxHeight = open ? `${panel.scrollHeight}px` : '0px';
      if (icon) icon.textContent = open ? '-' : '+';
    });
  });

  document.querySelectorAll('.exam-card-actions button, .exam-quick-grid button, .exam-discussion-grid button').forEach(button => {
    button.addEventListener('click', () => {
      button.classList.add('is-loading');
      const label = button.textContent.trim();
      setTimeout(() => {
        button.classList.remove('is-loading');
        showToast(`${label || 'Resource'} is opening...`);
      }, 450);
    });
  });

  filterCards();
}

// ── TRENDING REQUEST COUNTS ─────────────────────────────────────────────
document.querySelectorAll('.request-count[data-count]').forEach(el => {
  const target = parseInt(el.dataset.count);
  const observer = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) {
      animateCounter(el, target);
      observer.disconnect();
    }
  }, { threshold: 0.5 });
  observer.observe(el);
});

// ── EXPLORE TOPIC BUTTONS (demo) ────────────────────────────────────────
document.querySelectorAll('.tc-explore-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    const card = e.target.closest('.trending-card');
    const subjectText = card?.querySelector('.tc-subject')?.textContent || '';

    // Try to extract year from the subject label (e.g. "Anatomy • Year 1")
    const yearMatch = subjectText.match(/Year\s*(\d+)/i);
    let yearParam = null;
    if (yearMatch) {
      yearParam = yearMatch[1];
    } else if (/internship/i.test(subjectText)) {
      yearParam = 'internship';
    }

    if (yearParam) {
      // Navigate to the By Year page and open the selected year view
      window.location.href = `topics-year.html?year=${encodeURIComponent(yearParam)}`;
      return;
    }

    const title = card?.querySelector('.tc-title')?.textContent;
    if (title) showToast(`Opening: ${title}`);
  });
});

// ── SMOOTH SECTION NAVIGATION ───────────────────────────────────────────
document.querySelectorAll('[href^="#"]').forEach(link => {
  link.addEventListener('click', e => {
    const id = link.getAttribute('href').slice(1);
    const target = document.getElementById(id);
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

// ── KEYBOARD ACCESSIBILITY ───────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key === 'Tab') {
    document.body.classList.add('keyboard-nav');
  }
});
document.addEventListener('mousedown', () => {
  document.body.classList.remove('keyboard-nav');
});

// ── FOOTER LINK INTERACTIONS ─────────────────────────────────────────────
document.querySelectorAll('.footer-links-group a').forEach(link => {
  link.addEventListener('click', e => {
    const href = link.getAttribute('href');
    if (!href || href === '#') {
      e.preventDefault();
      showToast('Coming soon!');
    }
  });
});

// ── EXAM BUTTONS ──────────────────────────────────────────────────────────
document.querySelectorAll('.exam-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const title = btn.closest('.exam-card')?.querySelector('h3')?.textContent;
    showToast(`Opening ${title || 'module'}...`);
  });
});

// ── RESOURCE CARD BUTTONS ─────────────────────────────────────────────────
document.querySelectorAll('.rc-btn:not(:disabled)').forEach(btn => {
  btn.addEventListener('click', () => {
    const title = btn.closest('.resource-card')?.querySelector('h3')?.textContent;
    showToast(`Loading ${title || 'resources'}...`);
  });
});

// ── COMMUNITY FEED INTERACTIONS ──────────────────────────────────────────
document.querySelectorAll('.feed-item').forEach(item => {
  item.addEventListener('click', () => {
    const title = item.querySelector('.feed-item-title')?.textContent;
    if (title) showToast(`${title.substring(0, 40)}...`);
  });
});

// ── CSS INJECTION for shake and keyboard focus ────────────────────────────
const dynamicStyles = document.createElement('style');
dynamicStyles.textContent = `
  @keyframes shakeX {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-6px); }
    40%, 80% { transform: translateX(6px); }
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateX(-50%) translateY(10px); }
    to { opacity: 1; transform: translateX(-50%) translateY(0); }
  }
  .keyboard-nav *:focus {
    outline: 2px solid rgba(37,99,235,0.6) !important;
    outline-offset: 3px;
  }
`;
document.head.appendChild(dynamicStyles);

console.log('%cPhysio Academy Loaded', 'color:#2563eb;font-size:1.2rem;font-weight:bold;background:#f8fbff;padding:8px 16px;border-radius:8px;');
