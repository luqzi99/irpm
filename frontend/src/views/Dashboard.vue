<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { auth, teacher } from '../api'

const router = useRouter()
const user = ref(auth.getUser())
const classes = ref([])
const showNewClass = ref(false)
const newClassName = ref('')
const newClassYear = ref(new Date().getFullYear())
const loading = ref(true)

onMounted(async () => {
  try {
    classes.value = await teacher.getClasses()
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

async function createClass() {
  if (!newClassName.value) return
  
  try {
    const newClass = await teacher.createClass(newClassName.value, newClassYear.value)
    classes.value.push({ ...newClass, students_count: 0 })
    newClassName.value = ''
    showNewClass.value = false
  } catch (e) {
    alert(e.message)
  }
}

function goToClass(id) {
  router.push(`/class/${id}`)
}

function goToProgress(id) {
  router.push(`/class/${id}/progress`)
}

function goToTpInput() {
  router.push('/tp-input')
}

function goToProfile() {
  router.push('/profile')
}
</script>

<template>
  <div class="dashboard">
    <!-- Header -->
    <header class="header">
      <h1>🎓 iRPM</h1>
      <div class="header-right">
        <button @click="goToProfile" class="user-btn">
          <span class="user-avatar">👤</span>
          <span class="user-name">{{ user?.name }}</span>
        </button>
        <button @click="handleLogout" class="btn btn-secondary btn-sm">🚪 Keluar</button>
      </div>
    </header>
    
    <!-- Main Content -->
    <main class="container">
      <!-- Quick Action -->
      <div class="quick-action fade-in">
        <button @click="goToTpInput" class="btn-hero">
          <span class="hero-icon">📝</span>
          <span class="hero-text">
            <strong>Isi TP Sekarang</strong>
            <small>Satu tap satu penilaian</small>
          </span>
        </button>
      </div>
      
      <!-- Classes Section -->
      <section class="section fade-in">
        <div class="section-header">
          <h2>📚 Kelas Saya</h2>
          <button @click="showNewClass = true" class="btn btn-primary">+ Tambah Kelas</button>
        </div>
        
        <!-- Loading -->
        <div v-if="loading" class="loading">
          <div class="spinner"></div>
        </div>
        
        <!-- Classes Grid -->
        <div v-else class="grid grid-3">
          <div 
            v-for="cls in classes" 
            :key="cls.id" 
            class="class-card"
          >
            <h3 @click="goToClass(cls.id)">{{ cls.name }}</h3>
            <p class="class-meta">
              <span>📅 {{ cls.year }}</span>
              <span>👥 {{ cls.students_count }} murid</span>
            </p>
            <div class="class-actions">
              <button @click="goToClass(cls.id)" class="btn btn-secondary btn-sm">👥 Murid</button>
              <button @click="goToProgress(cls.id)" class="btn btn-secondary btn-sm">📊 Kemajuan</button>
            </div>
          </div>
          
          <!-- Empty state -->
          <div v-if="!classes.length && !loading" class="empty-state">
            <p>Tiada kelas. Tambah kelas pertama anda!</p>
          </div>
        </div>
      </section>
      
      <!-- New Class Modal -->
      <div v-if="showNewClass" class="modal-overlay" @click.self="showNewClass = false">
        <div class="modal fade-in">
          <h3>Tambah Kelas Baru</h3>
          <form @submit.prevent="createClass">
            <div class="form-group">
              <label>Nama Kelas</label>
              <input v-model="newClassName" placeholder="cth: 6 Amanah" required />
            </div>
            <div class="form-group">
              <label>Tahun</label>
              <input v-model="newClassYear" type="number" min="2020" max="2030" required />
            </div>
            <div class="modal-actions">
              <button type="button" @click="showNewClass = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.dashboard {
  min-height: 100vh;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  color: var(--text-muted);
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: var(--bg-hover);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  color: var(--text);
  cursor: pointer;
  transition: all 0.2s;
}

.user-btn:hover {
  background: var(--border);
  border-color: var(--primary);
}

.user-avatar {
  font-size: 1.25rem;
}

.quick-action {
  margin: 2rem 0;
}

.btn-hero {
  width: 100%;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border: none;
  border-radius: 1rem;
  color: white;
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-hero:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
}

.hero-icon {
  font-size: 2.5rem;
}

.hero-text {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.hero-text strong {
  font-size: 1.25rem;
}

.hero-text small {
  opacity: 0.8;
}

.section {
  margin-top: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.section-header h2 {
  font-size: 1.25rem;
}

.class-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.class-card:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
}

.class-card h3 {
  margin-bottom: 0.5rem;
}

.class-meta {
  display: flex;
  gap: 1rem;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.class-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn-sm {
  padding: 0.5rem 0.75rem;
  font-size: 0.75rem;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 3rem;
  color: var(--text-muted);
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
</style>
