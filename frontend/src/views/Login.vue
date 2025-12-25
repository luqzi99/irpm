<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '../api'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true
  
  try {
    await auth.login(email.value, password.value)
    router.push('/dashboard')
  } catch (e) {
    error.value = e.message || 'Log masuk gagal'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-container fade-in">
      <div class="login-header">
        <h1>🎓 iRPM</h1>
        <p>Rekod Penilaian Murid</p>
      </div>
      
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label>Emel</label>
          <input 
            v-model="email" 
            type="email" 
            placeholder="cikgu@sekolah.edu.my"
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
        
        <p v-if="error" class="error">{{ error }}</p>
        
        <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
          <span v-if="loading" class="spinner-small"></span>
          <span v-else>Log Masuk</span>
        </button>
      </form>
      
      <div class="login-footer">
        <p>Demo: <strong>guru@irpm.my</strong> / <strong>guru123</strong></p>
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

.btn-full {
  width: 100%;
  padding: 1rem;
  font-size: 1rem;
}

.error {
  color: var(--danger);
  font-size: 0.875rem;
  text-align: center;
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
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
</style>
