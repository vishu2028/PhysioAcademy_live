(() => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const registeredEmails = ['student@physioacademy.com', 'demo@physioacademy.com'];

  const qs = (selector, root = document) => root.querySelector(selector);
  const qsa = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  function setError(input, message = '') {
    const field = input.closest('.pauth-field');
    const error = qs(`[data-error-for="${input.id}"]`);
    input.classList.toggle('is-invalid', Boolean(message));
    input.classList.toggle('is-valid', Boolean(input.value.trim()) && !message);
    if (error) error.textContent = message;
    if (message && field) {
      field.classList.remove('shake');
      void field.offsetWidth;
      field.classList.add('shake');
    }
  }

  function passwordScore(value) {
    const rules = {
      length: value.length >= 8,
      upper: /[A-Z]/.test(value),
      lower: /[a-z]/.test(value),
      number: /\d/.test(value),
      special: /[^A-Za-z0-9]/.test(value)
    };
    return { rules, score: Object.values(rules).filter(Boolean).length };
  }

  function updateStrength(input) {
    if (!input) return { rules: {}, score: 0 };
    const { rules, score } = passwordScore(input.value);
    const strength = qs(`[data-strength-for="${input.id}"]`);
    const rulesWrap = qs(`[data-rules-for="${input.id}"]`);
    const labels = ['Waiting for input', 'Very weak', 'Weak', 'Good', 'Strong', 'Excellent'];
    const colors = ['#64748b', '#fb7185', '#f59e0b', '#38bdf8', '#22c55e', '#10b981'];

    if (strength) {
      const bar = qs('.pauth-strength-bar', strength);
      const label = qs('.pauth-strength-label', strength);
      if (bar) {
        bar.style.width = `${score * 20}%`;
        bar.style.background = colors[score];
      }
      if (label) label.textContent = `Password strength: ${labels[score]}`;
    }

    if (rulesWrap) {
      Object.entries(rules).forEach(([key, valid]) => {
        const rule = qs(`[data-rule="${key}"]`, rulesWrap);
        if (rule) rule.classList.toggle('valid', valid);
      });
    }
    return { rules, score };
  }

  function validateName(input) {
    const value = input.value.trim();
    if (!value) return 'Full name is required.';
    if (value.length < 3) return 'Use at least 3 characters.';
    if (!/^[A-Za-z\s]+$/.test(value)) return 'Only letters and spaces are allowed.';
    return '';
  }

  function validateEmail(input, simulateRegistered = false) {
    const value = input.value.trim().toLowerCase();
    if (!value) return 'Email address is required.';
    if (!emailRegex.test(value)) return 'Enter a valid email address.';
    if (simulateRegistered && registeredEmails.includes(value)) return 'This email is already registered. Try logging in.';
    return '';
  }

  function validatePassword(input, strict = false) {
    const value = input.value;
    if (!value) return 'Password is required.';
    const { score } = updateStrength(input);
    if (strict && score < 5) return 'Password must satisfy all security requirements.';
    if (!strict && value.length < 6) return 'Enter at least 6 characters.';
    return '';
  }

  function validateConfirm(input, passwordInput) {
    if (!input.value) return 'Please confirm your password.';
    if (input.value !== passwordInput.value) return 'Passwords do not match.';
    return '';
  }

  function setLoading(button, loading) {
    if (!button) return;
    button.classList.toggle('loading', loading);
    button.disabled = loading;
  }

  function showSuccess(form, title, message) {
    const panel = form.closest('.pauth-form-panel');
    form.style.display = 'none';
    const existing = qs('.pauth-success', panel);
    if (existing) existing.remove();
    const success = document.createElement('div');
    success.className = 'pauth-success is-visible';
    success.innerHTML = `
      <div class="pauth-success-icon">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h3>${title}</h3>
      <p>${message}</p>
    `;
    panel.appendChild(success);
  }

  qsa('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = document.getElementById(button.dataset.togglePassword);
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });

  qsa('[data-google-action]').forEach((button) => {
    button.addEventListener('click', () => {
      button.textContent = 'Connecting securely...';
      button.disabled = true;
      setTimeout(() => {
        button.textContent = 'Google authentication simulated';
        button.disabled = false;
      }, 1000);
    });
  });

  const loginForm = qs('#loginForm');
  if (loginForm) {
    const email = qs('#loginEmail');
    const password = qs('#loginPassword');
    email.addEventListener('input', () => setError(email, validateEmail(email)));
    password.addEventListener('input', () => setError(password, validatePassword(password)));
    loginForm.addEventListener('submit', (event) => {
      const errors = [
        [email, validateEmail(email)],
        [password, validatePassword(password)]
      ];
      errors.forEach(([input, error]) => setError(input, error));
      if (errors.some(([, error]) => error)) {
        event.preventDefault();
        return;
      }
      const button = qs('.pauth-submit', loginForm);
      setLoading(button, true);
    });
  }

  const signupForm = qs('#signupForm');
  if (signupForm) {
    const name = qs('#signupName');
    const email = qs('#signupEmail');
    const password = qs('#signupPassword');
    const confirm = qs('#signupConfirm');
    name.addEventListener('input', () => setError(name, validateName(name)));
    email.addEventListener('input', () => setError(email, validateEmail(email, true)));
    password.addEventListener('input', () => {
      setError(password, validatePassword(password, true));
      if (confirm.value) setError(confirm, validateConfirm(confirm, password));
    });
    confirm.addEventListener('input', () => setError(confirm, validateConfirm(confirm, password)));
    signupForm.addEventListener('submit', (event) => {
      const errors = [
        [name, validateName(name)],
        [email, validateEmail(email, true)],
        [password, validatePassword(password, true)],
        [confirm, validateConfirm(confirm, password)]
      ];
      errors.forEach(([input, error]) => setError(input, error));
      if (errors.some(([, error]) => error)) {
        event.preventDefault();
        return;
      }
      const button = qs('.pauth-submit', signupForm);
      setLoading(button, true);
    });
  }

  const forgotForm = qs('#forgotForm');
  if (forgotForm) {
    const email = qs('#forgotEmail');
    email.addEventListener('input', () => setError(email, validateEmail(email)));
    forgotForm.addEventListener('submit', (event) => {
      const error = validateEmail(email);
      setError(email, error);
      if (error) {
        event.preventDefault();
        return;
      }
      const button = qs('.pauth-submit', forgotForm);
      setLoading(button, true);
    });
  }

  const resetForm = qs('#resetForm');
  if (resetForm) {
    const password = qs('#resetPassword');
    const confirm = qs('#resetConfirm');
    password.addEventListener('input', () => {
      setError(password, validatePassword(password, true));
      if (confirm.value) setError(confirm, validateConfirm(confirm, password));
    });
    confirm.addEventListener('input', () => setError(confirm, validateConfirm(confirm, password)));
    resetForm.addEventListener('submit', (event) => {
      const errors = [
        [password, validatePassword(password, true)],
        [confirm, validateConfirm(confirm, password)]
      ];
      errors.forEach(([input, error]) => setError(input, error));
      if (errors.some(([, error]) => error)) {
        event.preventDefault();
        return;
      }
      const button = qs('.pauth-submit', resetForm);
      setLoading(button, true);
    });
  }
})();
