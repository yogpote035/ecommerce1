<?php
require_once 'init.php';
$siteTitle = 'Account Access';
$csrf_token = SecurityHelper::generateCSRFToken();
include 'templates/header.php';
?>
<div class="row justify-content-center">
  <div class="col-xl-10 col-lg-11">
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
          <div>
            <h1 class="h3">Account Access</h1>
            <p class="text-muted mb-0">Use email and password for both Customer and Admin. Sign up once and the app generates your account ID automatically.</p>
          </div>
          <div class="btn-group auth-toggle mt-3 mt-md-0" role="group" aria-label="User type toggle">
            <button type="button" class="btn btn-outline-primary active" id="roleCustomer">Customer</button>
            <button type="button" class="btn btn-outline-primary" id="roleAdmin">Admin</button>
            <button type="button" class="btn btn-outline-primary" id="roleRetailer">Retailer</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="btn-group btn-group-toggle auth-toggle d-flex" role="group" aria-label="Auth mode toggle">
          <button type="button" class="btn btn-outline-secondary active flex-fill" id="modeLogin">Login</button>
          <button type="button" class="btn btn-outline-secondary flex-fill" id="modeSignup">Sign Up</button>
        </div>

        <div class="pt-4">
          <div class="auth-panel" data-role="customer" data-mode="login">
            <h2 class="h5 mb-3">Customer Login</h2>
            <div class="auth-login-method password-login">
              <form action="cvalidation.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                  <label for="customerEmail" class="form-label">Email</label>
                  <input id="customerEmail" name="Cemail" type="email" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="customerCpass" class="form-label">Password</label>
                  <div class="input-group">
                    <input id="customerCpass" name="Cpass" type="password" class="form-control " required>
                    <div class="input-group-append">
                      <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Toggle password visibility">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                          <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <label class="mb-0 small"><input type="checkbox" name="remember_me" value="1"> Remember me</label>
                  <a class="small" href="forgot_password.php?role=customer">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
                <button type="button" class="btn btn-link p-0 mt-2 toggle-otp-login" data-role="customer">Use OTP instead</button>
              </form>
            </div>

            <div class="auth-login-method otp-login d-none" id="customerOtpLogin">
              <form action="otp_handler.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="action" value="send_otp">
                <input type="hidden" name="user_type" value="customer">
                <div class="mb-3">
                  <label for="customerOtpEmail" class="form-label">Email</label>
                  <input id="customerOtpEmail" name="email" type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['otp_email'] ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn btn-secondary">Send OTP</button>
                <button type="button" class="btn btn-link p-0 mt-2 toggle-otp-login" data-role="customer">Use password login</button>
              </form>
              <form action="otp_handler.php" method="post" class="mt-3">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="action" value="verify_otp">
                <input type="hidden" name="user_type" value="customer">
                <div class="mb-3">
                  <label for="customerOtpCode" class="form-label">Enter OTP</label>
                  <input id="customerOtpCode" name="otp" type="text" maxlength="6" pattern="\d{6}" class="form-control" placeholder="123456" required>
                </div>
                <button type="submit" class="btn btn-primary">Verify OTP</button>
              </form>
            </div>
          </div>

          <div class="auth-panel d-none" data-role="customer" data-mode="signup">
            <h2 class="h5 mb-3">Customer Sign Up</h2>
            <form action="reg.php" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
              <div class="row gx-3">
                <div class="col-md-6 mb-3">
                  <label for="customerName" class="form-label">Name</label>
                  <input id="customerName" name="name" type="text" class="form-control" pattern="[a-zA-Z ]+" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="customerEmailSignup" class="form-label">Email</label>
                  <input id="customerEmailSignup" name="c_email" type="email" class="form-control" required>
                </div>
              </div>
              <div class="row gx-3">
                <div class="col-md-6 mb-3">
                  <label for="customerContact" class="form-label">Contact</label>
                  <input id="customerContact" name="c_contact" type="tel" class="form-control" pattern="[789][0-9]{9}" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="customerAddress" class="form-label">Address</label>
                  <input id="customerAddress" name="c_add" type="text" class="form-control" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="customerPass" class="form-label">Password</label>
                <div class="input-group">
                  <input id="customerPass" name="c_pass" type="password" class="form-control" required>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Toggle password visibility">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-success">Sign Up</button>
            </form>
          </div>

          <div class="auth-panel d-none" data-role="admin" data-mode="login">
            <h2 class="h5 mb-3">Admin Login</h2>
            <div class="auth-login-method password-login">
              <form action="validation.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-3">
                  <label for="adminEmail" class="form-label">Email</label>
                  <input id="adminEmail" name="email" type="email" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="adminPass" class="form-label">Password</label>
                  <div class="input-group">
                    <input id="adminPass" name="apass" type="password" class="form-control" required>
                    <div class="input-group-append">
                      <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Toggle password visibility">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                          <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <label class="mb-0 small"><input type="checkbox" name="remember_me" value="1"> Remember me</label>
                  <a class="small" href="forgot_password.php?role=admin">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
                <button type="button" class="btn btn-link p-0 mt-2 toggle-otp-login" data-role="admin">Use OTP instead</button>
              </form>
            </div>

            <div class="auth-login-method otp-login d-none" id="adminOtpLogin">
              <form action="otp_handler.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="action" value="send_otp">
                <input type="hidden" name="user_type" value="admin">
                <div class="mb-3">
                  <label for="adminOtpEmail" class="form-label">Email</label>
                  <input id="adminOtpEmail" name="email" type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['otp_email'] ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn btn-secondary">Send OTP</button>
                <button type="button" class="btn btn-link p-0 mt-2 toggle-otp-login" data-role="admin">Use password login</button>
              </form>
              <form action="otp_handler.php" method="post" class="mt-3">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="action" value="verify_otp">
                <input type="hidden" name="user_type" value="admin">
                <div class="mb-3">
                  <label for="adminOtpCode" class="form-label">Enter OTP</label>
                  <input id="adminOtpCode" name="otp" type="text" maxlength="6" pattern="\d{6}" class="form-control" placeholder="123456" required>
                </div>
                <button type="submit" class="btn btn-primary">Verify OTP</button>
              </form>
            </div>
          </div>

          <div class="auth-panel d-none" data-role="admin" data-mode="signup">
            <h2 class="h5 mb-3">Admin Sign Up</h2>
            <form action="All.php" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
              <div class="mb-3">
                <label for="adminRegisterName" class="form-label">Name</label>
                <input id="adminRegisterName" name="aname" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="adminRegisterEmail" class="form-label">Email</label>
                <input id="adminRegisterEmail" name="email" type="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="adminAddress" class="form-label">Address</label>
                <input id="adminAddress" name="aadd" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="adminRegisterPass" class="form-label">Password</label>
                <div class="input-group">
                  <input id="adminRegisterPass" name="apass" type="password" class="form-control" required>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Toggle password visibility">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-success">Sign Up</button>
            </form>
          </div>

          <div class="auth-panel d-none" data-role="retailer" data-mode="login">
            <h2 class="h5 mb-3">Retailer Login</h2>
            <p class="text-muted small">Sign in with your retailer account to manage catalog items.</p>
            <form action="Rlogin.php" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
              <input type="hidden" name="action" value="login">
              <div class="mb-3">
                <label for="retailerName" class="form-label">Retailer Name</label>
                <input id="retailerName" name="rname" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="retailerPass" class="form-label">Password</label>
                <div class="input-group">
                  <input id="retailerPass" name="rpass" type="password" class="form-control" required>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Toggle password visibility">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Login</button>
            </form>
          </div>

          <div class="auth-panel d-none" data-role="retailer" data-mode="signup">
            <h2 class="h5 mb-3">Retailer Sign Up</h2>
            <form action="Rlogin.php" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
              <input type="hidden" name="action" value="register">
              <div class="mb-3">
                <label for="registerRetailerName" class="form-label">Name</label>
                <input id="registerRetailerName" name="rname" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="registerRetailerAddress" class="form-label">Address</label>
                <input id="registerRetailerAddress" name="radd" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="registerRetailerPass" class="form-label">Password</label>
                <div class="input-group">
                  <input id="registerRetailerPass" name="rpass" type="password" class="form-control" required>
                  <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary password-toggle" aria-label="Toggle password visibility">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="registerRetailerConfirmPass" class="form-label">Confirm Password</label>
                <input id="registerRetailerConfirmPass" name="rconpass" type="password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-success">Sign Up</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const roleButtons = {
    customer: document.getElementById('roleCustomer'),
    admin: document.getElementById('roleAdmin'),
    retailer: document.getElementById('roleRetailer')
  };
  const modeButtons = {
    login: document.getElementById('modeLogin'),
    signup: document.getElementById('modeSignup')
  };
  const panels = Array.from(document.querySelectorAll('.auth-panel'));
  let selectedRole = 'customer';
  let selectedMode = 'login';

  function updatePanels() {
    panels.forEach(panel => {
      const role = panel.getAttribute('data-role');
      const mode = panel.getAttribute('data-mode');
      panel.classList.toggle('d-none', role !== selectedRole || mode !== selectedMode);
    });
  }

  function setActiveRole(role) {
    selectedRole = role;
    roleButtons.customer.classList.toggle('active', role === 'customer');
    roleButtons.admin.classList.toggle('active', role === 'admin');
    roleButtons.retailer.classList.toggle('active', role === 'retailer');
    updatePanels();
  }

  function setActiveMode(mode) {
    selectedMode = mode;
    modeButtons.login.classList.toggle('active', mode === 'login');
    modeButtons.signup.classList.toggle('active', mode === 'signup');
    updatePanels();
  }

  function toggleOtpLogin(role, show) {
    const loginSection = document.querySelector(`.auth-panel[data-role="${role}"][data-mode="login"]`);
    if (!loginSection) return;

    const passwordMethod = loginSection.querySelector('.auth-login-method.password-login');
    const otpMethod = loginSection.querySelector('.auth-login-method.otp-login');
    if (!passwordMethod || !otpMethod) return;

    passwordMethod.classList.toggle('d-none', show);
    otpMethod.classList.toggle('d-none', !show);
  }

  function initAuthActions() {
    document.querySelectorAll('.toggle-otp-login').forEach(button => {
      button.addEventListener('click', () => {
        const role = button.getAttribute('data-role');
        const otpPanel = document.getElementById(`${role}OtpLogin`);
        if (!otpPanel) return;
        const currentlyVisible = otpPanel.classList.contains('d-none');
        toggleOtpLogin(role, currentlyVisible);
      });
    });

    roleButtons.customer.addEventListener('click', () => setActiveRole('customer'));
    roleButtons.admin.addEventListener('click', () => setActiveRole('admin'));
    roleButtons.retailer.addEventListener('click', () => setActiveRole('retailer'));
    modeButtons.login.addEventListener('click', () => setActiveMode('login'));
    modeButtons.signup.addEventListener('click', () => setActiveMode('signup'));

    // Ensure the correct panel is visible on first load.
    updatePanels();
  }

  document.addEventListener('DOMContentLoaded', initAuthActions);

  function getInitialState() {
    const params = new URLSearchParams(window.location.search);
    const role = params.get('role');
    const mode = params.get('mode');
    const otp = params.get('otp') === '1';
    return {
      role: role === 'admin' ? 'admin' : role === 'retailer' ? 'retailer' : 'customer',
      mode: mode === 'signup' ? 'signup' : 'login',
      otp: otp
    };
  }

  function initPasswordToggles() {
    const showIcon = '<path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>';
    const hideIcon = '<path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/><path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12z"/>';

    document.querySelectorAll('.password-toggle').forEach(button => {
      button.addEventListener('click', () => {
        const input = button.closest('.input-group').querySelector('input');
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        const svg = button.querySelector('svg');
        if (svg) {
          svg.innerHTML = isPassword ? hideIcon : showIcon;
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const state = getInitialState();
    setActiveRole(state.role);
    setActiveMode(state.mode);
    if (state.otp) {
      toggleOtpLogin(state.role, true);
    }
    initPasswordToggles();
  });
</script>

<?php include 'templates/footer.php';
