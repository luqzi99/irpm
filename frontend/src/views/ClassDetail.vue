<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { teacher } from '../api'

const router = useRouter()
const route = useRoute()

const classData = ref(null)
const students = ref([])
const loading = ref(true)
const showAddStudent = ref(false)

// New student form
const newStudentIc = ref('')
const newStudentName = ref('')
const existingStudent = ref(null)
const lookingUp = ref(false)

// Debounce timer
let lookupTimer = null

// Watch IC input for auto-lookup
watch(newStudentIc, (ic) => {
  existingStudent.value = null
  
  if (ic.length === 12) {
    // Debounce the lookup
    clearTimeout(lookupTimer)
    lookupTimer = setTimeout(async () => {
      lookingUp.value = true
      try {
        const result = await teacher.lookupStudentByIc(ic)
        if (result.found) {
          existingStudent.value = result
          newStudentName.value = result.name
        }
      } catch (e) {
        console.error(e)
      } finally {
        lookingUp.value = false
      }
    }, 300)
  }
})

onMounted(async () => {
  try {
    const [cls, studentList] = await Promise.all([
      teacher.getClass(route.params.id),
      teacher.getStudents(route.params.id)
    ])
    classData.value = cls
    students.value = studentList
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

async function addStudent() {
  if (!newStudentIc.value || !newStudentName.value) return
  
  try {
    const student = await teacher.addStudent(
      route.params.id,
      newStudentIc.value,
      newStudentName.value
    )
    students.value.push(student)
    newStudentIc.value = ''
    newStudentName.value = ''
    existingStudent.value = null
    showAddStudent.value = false
  } catch (e) {
    alert(e.message)
  }
}

function goBack() {
  router.push('/dashboard')
}

function goToTpInput() {
  router.push('/tp-input')
}
</script>

<template>
  <div class="class-detail">
    <!-- Header -->
    <header class="header">
      <button @click="goBack" class="btn btn-secondary">← Kembali</button>
      <h1 v-if="classData">{{ classData.name }}</h1>
      <button @click="goToTpInput" class="btn btn-primary">📝 Isi TP</button>
    </header>
    
    <main class="container">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
      </div>
      
      <div v-else class="fade-in">
        <!-- Class Info Card -->
        <div class="card info-card">
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Tahun</span>
              <span class="info-value">{{ classData?.year }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Bilangan Murid</span>
              <span class="info-value">{{ students.length }}</span>
            </div>
          </div>
        </div>
        
        <!-- Students Section -->
        <section class="section">
          <div class="section-header">
            <h2>👥 Senarai Murid</h2>
            <button @click="showAddStudent = true" class="btn btn-primary">+ Tambah Murid</button>
          </div>
          
          <div class="students-list card">
            <div v-if="!students.length" class="empty-state">
              <p>Tiada murid. Tambah murid pertama!</p>
            </div>
            
            <div v-for="student in students" :key="student.id" class="student-item">
              <span class="student-name">{{ student.name }}</span>
              <span class="student-school" v-if="student.school_name">{{ student.school_name }}</span>
            </div>
          </div>
        </section>
      </div>
      
      <!-- Add Student Modal -->
      <div v-if="showAddStudent" class="modal-overlay" @click.self="showAddStudent = false">
        <div class="modal fade-in">
          <h3>Tambah Murid</h3>
          <form @submit.prevent="addStudent">
            <div class="form-group">
              <label>No. Kad Pengenalan (12 digit)</label>
              <input 
                v-model="newStudentIc" 
                placeholder="123456789012"
                maxlength="12"
                required 
              />
              <div v-if="lookingUp" class="lookup-status">
                🔍 Mencari...
              </div>
              <div v-if="existingStudent" class="existing-student">
                ✅ Murid dijumpai: <strong>{{ existingStudent.name }}</strong>
              </div>
            </div>
            <div class="form-group">
              <label>Nama Murid</label>
              <input 
                v-model="newStudentName" 
                placeholder="Ahmad bin Abu" 
                required 
                :readonly="!!existingStudent"
                :class="{ 'readonly': existingStudent }"
              />
            </div>
            <div class="modal-actions">
              <button type="button" @click="showAddStudent = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.class-detail {
  min-height: 100vh;
}

.info-card {
  margin-bottom: 2rem;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-label {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.info-value {
  font-size: 1.5rem;
  font-weight: 600;
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

.students-list {
  padding: 0;
}

.student-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
}

.student-item:last-child {
  border-bottom: none;
}

.student-name {
  font-weight: 500;
}

.student-school {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.empty-state {
  padding: 3rem;
  text-align: center;
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

/* IC Lookup styles */
.lookup-status {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: var(--text-muted);
}

.existing-student {
  margin-top: 0.5rem;
  padding: 0.5rem;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 0.25rem;
  font-size: 0.875rem;
  color: var(--success);
}

.readonly {
  background: var(--bg-hover) !important;
  cursor: not-allowed;
}
</style>
