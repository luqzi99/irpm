<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '../api'

const router = useRouter()
// Get user from localStorage (user is logged in but not verified)
const user = JSON.parse(localStorage.getItem('user') || '{}')
const userEmail = ref(user.email || '')
const sending = ref(false)
const sent = ref(false)
const error = ref('')

onMounted(() => {
  // If no user or email, redirect to login
  if (!userEmail.value) {
    router.push('/login')
  }
})

async function resendVerification() {
  sending.value = true
  error.value = ''
  
  try {
    await auth.resendVerification(userEmail.value)
    sent.value = true
  } catch (e) {
    error.value = e.message || 'Gagal menghantar email. Sila cuba lagi.'
  } finally {
    sending.value = false
  }
}

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>

<template>
  <div class="verify-page">
    <div class="verify-card fade-in">
      <div class="icon">📧</div>
      <h1>Pengesahan Email Diperlukan</h1>
      
      <p class="message">
        Akaun anda masih belum disahkan. Sila semak email anda untuk pautan pengesahan.
      </p>
      
      <div class="email-display">
        <span class="email">{{ userEmail }}</span>
      </div>
      
      <div v-if="sent" class="success-message fade-in">
        ✅ Email pengesahan telah dihantar! Sila semak inbox anda.
      </div>
      
      <div v-if="error" class="error-message">
        {{ error }}
      </div>
      
      <div class="actions">
        <button 
          @click="resendVerification" 
          :disabled="sending || sent"
          class="btn btn-primary"
        >
          {{ sending ? 'Menghantar...' : sent ? 'Email Dihantar' : '📤 Hantar Semula Email' }}
        </button>
        
        <button @click="logout" class="btn btn-secondary">
          🚪 Log Keluar
        </button>
      </div>
      
      <p class="hint">
        Tidak menerima email? Semak folder spam anda atau klik butang di atas untuk hantar semula.
      </p>
    </div>
  </div>
</template>

<style scoped>
.verify-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: linear-gradient(135deg, var(--bg) 0%, var(--bg-card) 100%);
}

.verify-card {
  background: var(--bg-card);
  border-radius: 1.5rem;
  padding: 3rem 2rem;
  width: 100%;
  max-width: 450px;
  text-align: center;
  border: 1px solid var(--border);
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

h1 {
  font-size: 1.5rem;
  margin: 0 0 1rem;
  color: var(--text);
}

.message {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.email-display {
  background: var(--bg-hover);
  padding: 1rem;
  border-radius: 0.75rem;
  margin-bottom: 1.5rem;
}

.email {
  font-weight: 600;
  color: var(--primary);
  font-size: 1.125rem;
}

.success-message {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid var(--success);
  color: var(--success);
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

.error-message {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid var(--danger);
  color: var(--danger);
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

.actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.btn {
  padding: 1rem 2rem;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-secondary {
  background: var(--bg-hover);
  color: var(--text);
}

.btn-secondary:hover {
  background: var(--border);
}

.hint {
  font-size: 0.875rem;
  color: var(--text-muted);
  line-height: 1.5;
}

.fade-in {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
