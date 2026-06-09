/* auth.js */
const originalFetch = window.fetch;
window.fetch = async function() {
  let [resource, config] = arguments;
  if (!config) config = {};
  if (config.method && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(config.method.toUpperCase())) {
    const match = document.cookie.match(new RegExp('(^| )XSRF-TOKEN=([^;]+)'));
    if (match) {
      config.headers = config.headers || {};
      config.headers['X-XSRF-TOKEN'] = match[2];
    }
  }
  return originalFetch(resource, config);
};
const Auth = (() => {
  let _user = null;

  async function getUser() {
    if (_user !== null) return _user;
    try {
      const res = await fetch('api/auth/me.php');
      _user = res.ok ? await res.json() : null;
    } catch { _user = null; }
    return _user;
  }

  async function login(email, password) {
    const res  = await fetch('api/auth/login.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, password }) });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Login failed');
    _user = data; return data;
  }

  async function register(email, password, full_name, role) {
    const res  = await fetch('api/auth/register.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, password, full_name, role }) });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Registration failed');
    _user = data; return data;
  }

  async function logout() {
    await fetch('api/auth/logout.php', { method: 'POST' });
    _user = null; window.location.href = 'index.html';
  }

  return { getUser, login, register, logout };
})();

function escHtml(s) { const d = document.createElement('div'); d.textContent = String(s ?? ''); return d.innerHTML; }

async function updateNav() {
  const user = await Auth.getUser();
  const navAuth = document.getElementById('nav-auth');
  const navLinks = document.getElementById('nav-links');
  
  if (navLinks) {
    let links = `<li><a href="index.html">Home</a></li>`;
    if (user) {
      links += `<li><a href="report.html">Reports</a></li>
                <li><a href="map.html">Map</a></li>`;
      if (user.role === 'decision_maker' || user.role === 'admin') {
        links += `<li><a href="dashboard.html">Dashboard</a></li>`;
      }
      if (user.role === 'admin') {
        links += `<li><a href="admin.html">Admin</a></li>`;
      }
    }
    navLinks.innerHTML = links;
    
    // Highlight active
    const current = window.location.pathname.split('/').pop() || 'index.html';
    navLinks.querySelectorAll('a').forEach(a => {
      if (a.getAttribute('href') === current) a.classList.add('active');
    });
  }

  if (!navAuth) return;
  if (user) {
    navAuth.innerHTML = `<span class="nav-user"><strong>${escHtml(user.full_name)}</strong> (${escHtml(user.role)})</span>
                         <button class="btn btn-sm btn-ghost" onclick="Auth.logout()">Log out</button>`;
                         
    // Hide Login/Register button on index page
    const btnHero = document.getElementById('btn-login-register');
    if (btnHero) btnHero.style.display = 'none';

    // Redirect away from login/register pages if already logged in
    const path = window.location.pathname;
    if (path.endsWith('login.html') || path.endsWith('register.html')) {
        window.location.href = 'index.html';
    }
  } else {
    navAuth.innerHTML = `<a href="login.html" class="btn btn-sm btn-ghost">Log in</a>
                         <a href="register.html" class="btn btn-sm btn-primary">Sign up</a>`;
  }
}

function initLoginForm() {
  const form = document.getElementById('login-form');
  if (!form) return;
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const alert = document.getElementById('login-alert');
    const btn   = form.querySelector('button[type=submit]');
    btn.disabled = true;
    try {
      await Auth.login(form.email.value.trim(), form.password.value);
      window.location.href = 'report.html';
    } catch (err) {
      alert.textContent = err.message; alert.className = 'alert alert-error visible';
      btn.disabled = false;
    }
  });
}

function initRegisterForm() {
  const form = document.getElementById('register-form');
  if (!form) return;
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const alert = document.getElementById('register-alert');
    const btn   = form.querySelector('button[type=submit]');
    if (form.password.value !== form.password2.value) {
      alert.textContent = 'Passwords do not match.'; alert.className = 'alert alert-error visible'; return;
    }
    btn.disabled = true;
    try {
      await Auth.register(form.email.value.trim(), form.password.value, form.full_name.value.trim(), 'citizen');
      window.location.href = 'report.html';
    } catch (err) {
      alert.textContent = err.message; alert.className = 'alert alert-error visible';
      btn.disabled = false;
    }
  });
}

document.addEventListener('DOMContentLoaded', () => { updateNav(); initLoginForm(); initRegisterForm(); });
