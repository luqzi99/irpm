<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '../api'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const showRegister = ref(false)

// Register form
const name = ref('')
const confirmPassword = ref('')
const registerSuccess = ref(false)

// Error states
const requiresVerification = ref(false)
const subscriptionExpired = ref(false)
const expiredAt = ref('')

// Forgot password
const showForgotPassword = ref(false)
const forgotEmail = ref('')
const forgotSent = ref(false)

async function handleLogin() {
  error.value = ''
  subscriptionExpired.value = false
  loading.value = true
  
  try {
    const data = await auth.login(email.value, password.value)
    const user = data.user
    // Navigation guard will redirect unverified users to verification page
    router.push(user.role === 'admin' ? '/admin' : '/dashboard')
  } catch (e) {
    // Check for specific error types
    if (e.subscription_expired) {
      subscriptionExpired.value = true
      expiredAt.value = e.expired_at
      error.value = e.message
    } else if (e.account_disabled) {
      error.value = e.message
    } else {
      error.value = e.message || 'Log masuk gagal'
    }
  } finally {
    loading.value = false
  }
}

async function handleRegister() {
  error.value = ''
  loading.value = true
  
  try {
    const data = await auth.register(name.value, email.value, password.value, confirmPassword.value)
    
    if (data.requires_verification) {
      registerSuccess.value = true
      showRegister.value = false
    } else {
      // Bypass mode - auto logged in
      router.push('/dashboard')
    }
  } catch (e) {
    error.value = e.errors?.email?.[0] || e.message || 'Pendaftaran gagal'
  } finally {
    loading.value = false
  }
}

async function resendVerification() {
  loading.value = true
  try {
    await auth.resendVerification(email.value)
    error.value = 'Emel pengesahan telah dihantar semula! Sila semak inbox anda.'
    requiresVerification.value = false
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

async function handleForgotPassword() {
  if (!forgotEmail.value) return
  
  loading.value = true
  try {
    await auth.forgotPassword(forgotEmail.value)
    forgotSent.value = true
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

function closeForgotModal() {
  showForgotPassword.value = false
  forgotSent.value = false
  forgotEmail.value = ''
}
</script>

<template>
  <div class="login-page">
    <div class="login-container fade-in">
      <div class="login-header">
        <h1>🎓 iRPM</h1>
        <p>Rekod Penilaian Murid</p>
      </div>

      <!-- Success message after registration -->
      <div v-if="registerSuccess" class="success-box">
        <p>✅ Pendaftaran berjaya!</p>
        <p>Sila semak emel anda untuk pengesahan.</p>
        <button @click="registerSuccess = false" class="btn btn-secondary btn-full">
          Kembali ke Log Masuk
        </button>
      </div>

      <!-- Login Form -->
      <form v-else-if="!showRegister" @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label>Emel</label>
          <input 
            v-model="email" 
            type="email" 
            placeholder="nama@moe.gov.my"
            required
            autofocus
          />
        </div>
        
        <div class="form-group">
          <label>Kata Laluan</label>
          <input 
            v-model="password" 
            type="password" 
            placeholder="••••••••"
            required
          />
        </div>
        
        <!-- Verification Required Error -->
        <div v-if="requiresVerification" class="warning-box">
          <p>⚠️ {{ error }}</p>
          <button type="button" @click="resendVerification" class="btn btn-secondary btn-sm">
            Hantar Semula Emel
          </button>
        </div>

        <!-- Subscription Expired Error -->
        <div v-else-if="subscriptionExpired" class="error-box">
          <p>⏰ {{ error }}</p>
          <p class="small">Tamat: {{ expiredAt }}</p>
        </div>

        <!-- Generic Error -->
        <p v-else-if="error" class="error">{{ error }}</p>
        
        <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
          <span v-if="loading" class="spinner-small"></span>
          <span v-else>Log Masuk</span>
        </button>

        <button type="button" @click="showRegister = true" class="btn btn-secondary btn-full">
          Daftar Akaun Baru
        </button>
      </form>

      <!-- Register Form -->
      <form v-else @submit.prevent="handleRegister" class="login-form">
        <div class="form-group">
          <label>Nama Penuh</label>
          <input v-model="name" type="text" placeholder="Cikgu Ahmad" required />
        </div>
        
        <div class="form-group">
          <label>Emel KPM</label>
          <input v-model="email" type="email" placeholder="nama@moe.gov.my" required />
          <small>Hanya emel @moe.gov.my dibenarkan</small>
        </div>
        
        <div class="form-group">
          <label>Kata Laluan</label>
          <input v-model="password" type="password" placeholder="Minimum 6 aksara" required />
        </div>
        
        <div class="form-group">
          <label>Sahkan Kata Laluan</label>
          <input v-model="confirmPassword" type="password" placeholder="Taip semula kata laluan" required />
        </div>
        
        <p v-if="error" class="error">{{ error }}</p>
        
        <button type="submit" class="btn btn-primary btn-full" :disabled="loading || password !== confirmPassword">
          <span v-if="loading" class="spinner-small"></span>
          <span v-else>Daftar</span>
        </button>

        <button type="button" @click="showRegister = false; error = ''" class="btn btn-secondary btn-full">
          Kembali ke Log Masuk
        </button>
      </form>
      
      <div class="login-footer" v-if="!showRegister && !registerSuccess">
        <button type="button" @click="showForgotPassword = true" class="link-btn">
          Lupa Kata Laluan?
        </button>
        <p class="test-hint">Ujian: <strong>test@test.irpm.my</strong></p>
      </div>

      <!-- Forgot Password Modal -->
      <div v-if="showForgotPassword" class="modal-overlay" @click.self="closeForgotModal">
        <div class="modal fade-in">
          <div v-if="forgotSent">
            <div class="icon">📧</div>
            <h3>Emel Dihantar!</h3>
            <p>Jika akaun wujud, pautan tetapan semula telah dihantar.</p>
            <button @click="closeForgotModal" class="btn btn-primary btn-full">Tutup</button>
          </div>
          <form v-else @submit.prevent="handleForgotPassword">
            <h3>🔐 Lupa Kata Laluan</h3>
            <p class="modal-desc">Masukkan emel anda untuk menerima pautan tetapan semula.</p>
            <div class="form-group">
              <input v-model="forgotEmail" type="email" placeholder="nama@moe.gov.my" required />
            </div>
            <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
              {{ loading ? 'Menghantar...' : 'Hantar Pautan' }}
            </button>
            <button type="button" @click="closeForgotModal" class="btn btn-secondary btn-full">
              Batal
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
  padding: 1rem;
}

.login-container {
  background: var(--bg-card);
  border-radius: 1.5rem;
  padding: 2.5rem;
  width: 100%;
  max-width: 400px;
  border: 1px solid var(--border);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  font-size: 2.5rem;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 0.5rem;
}

.login-header p {
  color: var(--text-muted);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.login-form small {
  color: var(--text-muted);
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.btn-full {
  width: 100%;
  padding: 1rem;
  font-size: 1rem;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.error {
  color: var(--danger);
  font-size: 0.875rem;
  text-align: center;
}

.error-box {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid var(--danger);
  border-radius: 0.5rem;
  padding: 1rem;
  text-align: center;
}

.error-box p {
  margin: 0;
  color: var(--danger);
}

.error-box .small {
  font-size: 0.75rem;
  margin-top: 0.5rem;
  color: var(--text-muted);
}

.warning-box {
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid #f59e0b;
  border-radius: 0.5rem;
  padding: 1rem;
  text-align: center;
}

.warning-box p {
  margin: 0 0 0.75rem;
  color: #f59e0b;
}

.success-box {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid var(--success);
  border-radius: 0.5rem;
  padding: 1.5rem;
  text-align: center;
}

.success-box p {
  margin: 0 0 0.75rem;
  color: var(--success);
}

.login-footer {
  margin-top: 1.5rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.login-footer strong {
  color: var(--text);
}

.spinner-small {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal {
  background: var(--bg-card);
  border-radius: 1rem;
  padding: 2rem;
  width: 100%;
  max-width: 400px;
  border: 1px solid var(--border);
  text-align: center;
}

.modal h3 {
  margin: 0 0 1rem;
}

.modal-desc {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.modal .form-group {
  margin-bottom: 1rem;
}

.modal .btn {
  margin-top: 0.5rem;
}

.icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.link-btn {
  background: none;
  border: none;
  color: var(--primary);
  cursor: pointer;
  font-size: 0.875rem;
  text-decoration: underline;
  display: block;
  margin: 0 auto 0.5rem;
}

.link-btn:hover {
  color: var(--secondary);
}

.test-hint {
  margin: 0;
}
</style>


