<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { auth } from '../api'

const router = useRouter()
const route = useRoute()
const loading = ref(true)
const success = ref(false)
const error = ref('')

onMounted(async () => {
  const token = route.query.token
  
  if (!token) {
    error.value = 'Token pengesahan tidak dijumpai.'
    loading.value = false
    return
  }

  try {
    const res = await auth.verifyEmail(token)
    success.value = true
    
    // Redirect to dashboard after 2 seconds
    setTimeout(() => {
      router.push('/dashboard')
    }, 2000)
  } catch (e) {
    error.value = e.message || 'Token tidak sah atau telah tamat tempoh.'
  } finally {
    loading.value = false
  }
})

function goToLogin() {
  router.push('/login')
}
</script>

<template>
  <div class="verify-page">
    <div class="verify-card">
      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Mengesahkan emel anda...</p>
      </div>

      <!-- Success -->
      <div v-else-if="success" class="success-state">
        <div class="icon">✅</div>
        <h2>Emel Berjaya Disahkan!</h2>
        <p>Anda akan dialihkan ke papan pemuka...</p>
        <div class="spinner small"></div>
      </div>

      <!-- Error -->
      <div v-else class="error-state">
        <div class="icon">❌</div>
        <h2>Pengesahan Gagal</h2>
        <p>{{ error }}</p>
        <button @click="goToLogin" class="btn btn-primary">
          Kembali ke Log Masuk
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.verify-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--bg) 0%, #1e1b4b 100%);
  padding: 2rem;
}

.verify-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  padding: 3rem;
  text-align: center;
  max-width: 400px;
  width: 100%;
}

.icon {
  font-size: 4rem;
  margin-bottom: 1.5rem;
}

h2 {
  margin-bottom: 1rem;
}

p {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
}

.loading-state,
.success-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.spinner.small {
  width: 24px;
  height: 24px;
  border-width: 3px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
