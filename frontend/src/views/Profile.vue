<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '../api'

const router = useRouter()
const user = ref(null)
const loading = ref(true)
const toast = ref('')
const toastType = ref('success')

// Password change form
const showPasswordModal = ref(false)
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const changingPassword = ref(false)

onMounted(async () => {
  try {
    user.value = await auth.getMe()
  } catch (e) {
    console.error(e)
    // Fallback to stored user
    user.value = auth.getUser()
  } finally {
    loading.value = false
  }
})

async function handleChangePassword() {
  if (newPassword.value !== confirmPassword.value) {
    showToast('Kata laluan baru tidak sepadan', 'error')
    return
  }
  
  if (newPassword.value.length < 6) {
    showToast('Kata laluan mestilah sekurang-kurangnya 6 aksara', 'error')
    return
  }

  changingPassword.value = true
  try {
    await auth.changePassword(currentPassword.value, newPassword.value)
    showToast('Kata laluan berjaya ditukar! ✅', 'success')
    showPasswordModal.value = false
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
  } catch (e) {
    showToast(e.message || 'Gagal menukar kata laluan', 'error')
  } finally {
    changingPassword.value = false
  }
}

function showToast(message, type = 'success') {
  toast.value = message
  toastType.value = type
  setTimeout(() => toast.value = '', 3000)
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

function goBack() {
  router.push('/dashboard')
}
</script>

<template>
  <div class="profile-page">
    <!-- Header -->
    <header class="header">
      <button @click="goBack" class="btn btn-secondary">← Kembali</button>
      <h1>👤 Profil</h1>
      <button @click="handleLogout" class="btn btn-secondary">🚪 Keluar</button>
    </header>

    <main class="container">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else class="fade-in">
        <!-- Profile Card -->
        <div class="profile-card card">
          <div class="profile-header">
            <div class="avatar">👤</div>
            <div class="profile-info">
              <h2>{{ user?.name }}</h2>
              <p class="email">{{ user?.email }}</p>
              <span class="role-badge">{{ user?.role === 'admin' ? '👑 Admin' : '📚 Guru' }}</span>
            </div>
          </div>
        </div>

        <!-- Account Settings -->
        <section class="section">
          <h3>⚙️ Tetapan Akaun</h3>
          
          <div class="settings-card card">
            <div class="setting-item">
              <div class="setting-info">
                <strong>Tukar Kata Laluan</strong>
                <p>Kemas kini kata laluan anda untuk keselamatan</p>
              </div>
              <button @click="showPasswordModal = true" class="btn btn-primary">Tukar</button>
            </div>
          </div>
        </section>

        <!-- Account Info -->
        <section class="section">
          <h3>📋 Maklumat Akaun</h3>
          
          <div class="info-card card">
            <div class="info-row">
              <span class="info-label">Nama</span>
              <span class="info-value">{{ user?.name }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Email</span>
              <span class="info-value">{{ user?.email }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Peranan</span>
              <span class="info-value">{{ user?.role === 'admin' ? 'Pentadbir' : 'Guru' }}</span>
            </div>
            <div class="info-row" v-if="user?.created_at">
              <span class="info-label">Tarikh Daftar</span>
              <span class="info-value">{{ new Date(user.created_at).toLocaleDateString('ms-MY') }}</span>
            </div>
          </div>
        </section>
      </div>

      <!-- Password Change Modal -->
      <div v-if="showPasswordModal" class="modal-overlay" @click.self="showPasswordModal = false">
        <div class="modal fade-in">
          <h3>🔐 Tukar Kata Laluan</h3>
          <form @submit.prevent="handleChangePassword">
            <div class="form-group">
              <label>Kata Laluan Semasa</label>
              <input 
                v-model="currentPassword" 
                type="password" 
                placeholder="Masukkan kata laluan semasa"
                required 
              />
            </div>
            <div class="form-group">
              <label>Kata Laluan Baru</label>
              <input 
                v-model="newPassword" 
                type="password" 
                placeholder="Minimum 6 aksara"
                required 
                minlength="6"
              />
            </div>
            <div class="form-group">
              <label>Sahkan Kata Laluan Baru</label>
              <input 
                v-model="confirmPassword" 
                type="password" 
                placeholder="Ulang kata laluan baru"
                required 
              />
            </div>
            <div class="modal-actions">
              <button type="button" @click="showPasswordModal = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="changingPassword">
                {{ changingPassword ? 'Menukar...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Toast -->
      <div v-if="toast" class="toast" :class="toastType">{{ toast }}</div>
    </main>
  </div>
</template>

<style scoped>
.profile-page {
  min-height: 100vh;
}

.profile-card {
  margin-bottom: 2rem;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
}

.profile-info h2 {
  margin-bottom: 0.25rem;
}

.email {
  color: var(--text-muted);
  margin-bottom: 0.5rem;
}

.role-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: var(--bg-hover);
  border-radius: 1rem;
  font-size: 0.875rem;
}

.section {
  margin-top: 2rem;
}

.section h3 {
  font-size: 1rem;
  margin-bottom: 1rem;
  color: var(--text-muted);
}

.settings-card,
.info-card {
  padding: 0;
}

.setting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
}

.setting-info p {
  font-size: 0.875rem;
  color: var(--text-muted);
  margin-top: 0.25rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  color: var(--text-muted);
}

.info-value {
  font-weight: 500;
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
}

.modal h3 {
  margin-bottom: 1.5rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.modal-actions .btn {
  flex: 1;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  z-index: 1000;
  animation: fadeIn 0.3s ease-out;
}

.toast.success {
  background: var(--success);
  color: white;
}

.toast.error {
  background: var(--danger);
  color: white;
}

/* Mobile */
@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
  }
  
  .setting-item {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .setting-item .btn {
    width: 100%;
  }
}
</style>
