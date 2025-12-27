<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { auth } from '../api'

const router = useRouter()
const route = useRoute()
const token = ref('')
const password = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const success = ref(false)
const error = ref('')

onMounted(() => {
  token.value = route.query.token || ''
  
  if (!token.value) {
    error.value = 'Token tidak dijumpai. Sila minta pautan baharu.'
  }
})

async function handleReset() {
  if (password.value !== confirmPassword.value) {
    error.value = 'Kata laluan tidak sepadan.'
    return
  }
  
  error.value = ''
  loading.value = true
  
  try {
    await auth.resetPassword(token.value, password.value, confirmPassword.value)
    success.value = true
    
    // Redirect to login after 3 seconds
    setTimeout(() => {
      router.push('/login')
    }, 3000)
  } catch (e) {
    error.value = e.message || 'Gagal menetapkan semula kata laluan.'
  } finally {
    loading.value = false
  }
}

function goToLogin() {
  router.push('/login')
}
</script>

<template>
  <div class="reset-page">
    <div class="reset-card fade-in">
      <div class="header">
        <h1>🔐 iRPM</h1>
        <p>Tetapkan Semula Kata Laluan</p>
      </div>

      <!-- Success -->
      <div v-if="success" class="success-state">
        <div class="icon">✅</div>
        <h2>Berjaya!</h2>
        <p>Kata laluan anda telah ditetapkan semula.</p>
        <p class="redirect">Anda akan dialihkan ke halaman log masuk...</p>
      </div>

      <!-- Reset Form -->
      <form v-else-if="token" @submit.prevent="handleReset" class="reset-form">
        <div class="form-group">
          <label>Kata Laluan Baru</label>
          <input 
            v-model="password" 
            type="password" 
            placeholder="Minimum 6 aksara"
            required
            minlength="6"
          />
        </div>
        
        <div class="form-group">
          <label>Sahkan Kata Laluan</label>
          <input 
            v-model="confirmPassword" 
            type="password" 
            placeholder="Taip semula kata laluan"
            required
          />
        </div>
        
        <p v-if="error" class="error">{{ error }}</p>
        
        <button 
          type="submit" 
          class="btn btn-primary btn-full" 
          :disabled="loading || password !== confirmPassword"
        >
          <span v-if="loading" class="spinner-small"></span>
          <span v-else>Tetapkan Kata Laluan</span>
        </button>
        
        <button type="button" @click="goToLogin" class="btn btn-secondary btn-full">
          Kembali ke Log Masuk
        </button>
      </form>

      <!-- No Token Error -->
      <div v-else class="error-state">
        <div class="icon">❌</div>
        <p>{{ error }}</p>
        <button @click="goToLogin" class="btn btn-primary">
          Kembali ke Log Masuk
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.reset-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--bg) 0%, #1e1b4b 100%);
  padding: 1rem;
}

.reset-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  padding: 2.5rem;
  text-align: center;
  max-width: 400px;
  width: 100%;
}

.header h1 {
  font-size: 2rem;
  background: linear-gradient(135deg, #f59e0b, #ef4444);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 0.5rem;
}

.header p {
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.reset-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  text-align: left;
}

.btn-full {
  width: 100%;
  padding: 1rem;
}

.error {
  color: var(--danger);
  text-align: center;
  font-size: 0.875rem;
}

.success-state h2 {
  color: var(--success);
  margin-bottom: 0.5rem;
}

.success-state p {
  color: var(--text-muted);
}

.redirect {
  font-size: 0.875rem;
  margin-top: 1rem;
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
</style>
