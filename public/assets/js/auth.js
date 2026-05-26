/* auth.js — login / register / logout helpers */

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

  function csrfHeaders() {
    const token = _user?.csrf_token;
    return token ? { 'Content-Type': 'application/json', 'X-CSRF-Token': token }
                 : { 'Content-Type': 'application/json' };
  }

  async function login(email, password) {
    const res = await fetch('api/auth/login.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, password }) });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Login failed');
    _user = null; // re-fetch to get csrf_token
    return data;
  }

  async function register(email, password, full_name, role) {
    const res = await fetch('api/auth/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password, full_name, role }),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Registration failed');
    _user = data;
    return data;
  }

  async function logout() {
    await fetch('api/auth/logout.php', { method: 'POST' });
    _user = null;
    window.location.href = 'index.html';
  }

  return { getUser, login, register, logout, csrfHeaders };
})();

/* Update nav based on session */
async function updateNav() {
  const user = await Auth.getUser();
  const navAuth = document.getElementById('nav-auth');
  const navLinks = document.getElementById('nav-links');
  if (!navAuth) return;

  if (user) {
    navAuth.innerHTML = `
      <span class="nav-user">👋 <strong>${escHtml(user.full_name)}</strong> (${escHtml(user.role)})</span>
      <button class="btn btn-ghost btn-sm" id="btn-logout">Logout</button>`;
    document.getElementById('btn-logout').addEventListener('click', () => Auth.logout());

    if (navLinks) {
      const roleLinks = {
        citizen:        '<li><a href="report.html">My Reports</a></li>',
        staff:          '<li><a href="report.html">Reports</a></li>',
        decision_maker: '<li><a href="report.html">Reports</a></li><li><a href="dashboard.html">Dashboard</a></li>',
        admin:          '<li><a href="report.html">Reports</a></li><li><a href="admin.html">Admin</a></li>',
      };
      navLinks.innerHTML = `<li><a href="index.html">Home</a></li>` + (roleLinks[user.role] || '');
    }
  } else {
    navAuth.innerHTML = `
      <a class="btn btn-ghost btn-sm" href="login.html">Login</a>
      <a class="btn btn-secondary btn-sm" href="register.html" style="background:#fff;color:var(--clr-primary)">Register</a>`;
  }
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

/* Handle login form */
function initLoginForm() {
  const form  = document.getElementById('login-form');
  const alert = document.getElementById('login-alert');
  const btn   = document.getElementById('btn-login');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    alert.className = 'alert';
    btn.disabled = true;
    btn.textContent = 'Signing in…';
    try {
      await Auth.login(
        form.email.value.trim(),
        form.password.value
      );
      window.location.href = 'report.html';
    } catch (err) {
      alert.textContent = err.message;
      alert.className = 'alert alert-error visible';
      btn.disabled = false;
      btn.textContent = 'Sign in';
    }
  });
}

/* Handle register form */
function initRegisterForm() {
  const form  = document.getElementById('register-form');
  const alert = document.getElementById('register-alert');
  const btn   = document.getElementById('btn-register');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    alert.className = 'alert';
    if (form.password.value !== form.password2.value) {
      alert.textContent = 'Passwords do not match.';
      alert.className = 'alert alert-error visible';
      return;
    }
    btn.disabled = true;
    btn.textContent = 'Creating account…';
    try {
      await Auth.register(
        form.email.value.trim(),
        form.password.value,
        form.full_name.value.trim(),
        form.role.value
      );
      window.location.href = 'report.html';
    } catch (err) {
      alert.textContent = err.message;
      alert.className = 'alert alert-error visible';
      btn.disabled = false;
      btn.textContent = 'Create account';
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  updateNav();
  initLoginForm();
  initRegisterForm();
});
