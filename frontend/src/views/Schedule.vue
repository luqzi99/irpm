<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { teacher, subjects } from '../api'

const router = useRouter()
const loading = ref(true)
const schedules = ref([])
const classes = ref([])
const subjectsList = ref([])

// Add schedule form
const showAddModal = ref(false)
const newSchedule = ref({
  class_id: null,
  subject_id: null,
  day_of_week: 1,
  start_time: '08:00',
})
const saving = ref(false)

const dayNames = [
  { value: 1, label: 'Isnin' },
  { value: 2, label: 'Selasa' },
  { value: 3, label: 'Rabu' },
  { value: 4, label: 'Khamis' },
  { value: 5, label: 'Jumaat' },
  { value: 6, label: 'Sabtu' },
  { value: 7, label: 'Ahad' },
]

onMounted(async () => {
  try {
    const [schedulesData, classesData, subjectsData] = await Promise.all([
      teacher.getSchedules(),
      teacher.getClasses(),
      subjects.getAll(),
    ])
    schedules.value = schedulesData
    classes.value = classesData
    subjectsList.value = subjectsData.filter(s => s.level === 'primary')
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

async function handleAddSchedule() {
  if (!newSchedule.value.class_id || !newSchedule.value.subject_id) {
    alert('Sila pilih kelas dan subjek')
    return
  }
  
  saving.value = true
  try {
    const created = await teacher.createSchedule(
      newSchedule.value.class_id,
      newSchedule.value.subject_id,
      newSchedule.value.day_of_week,
      newSchedule.value.start_time
    )
    schedules.value.push(created)
    // Sort by day and time
    schedules.value.sort((a, b) => {
      if (a.day_of_week !== b.day_of_week) return a.day_of_week - b.day_of_week
      return a.start_time.localeCompare(b.start_time)
    })
    showAddModal.value = false
    resetForm()
  } catch (e) {
    alert(e.message || 'Gagal menambah jadual')
  } finally {
    saving.value = false
  }
}

async function handleDelete(id) {
  if (!confirm('Padam jadual ini?')) return
  
  try {
    await teacher.deleteSchedule(id)
    schedules.value = schedules.value.filter(s => s.id !== id)
  } catch (e) {
    alert('Gagal memadam jadual')
  }
}

function resetForm() {
  newSchedule.value = {
    class_id: null,
    subject_id: null,
    day_of_week: 1,
    start_time: '08:00',
  }
}

function goBack() {
  router.push('/dashboard')
}

// Group schedules by day
function getSchedulesByDay(day) {
  return schedules.value.filter(s => s.day_of_week === day)
}
</script>

<template>
  <div class="schedule-page">
    <header class="header">
      <button @click="goBack" class="btn btn-secondary">← Kembali</button>
      <h1>📅 Jadual Mengajar</h1>
      <button @click="showAddModal = true" class="btn btn-primary">+ Tambah</button>
    </header>

    <main class="container">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else class="fade-in">
        <!-- Schedule Grid by Day -->
        <div class="schedule-grid">
          <div 
            v-for="day in dayNames.slice(0, 5)" 
            :key="day.value" 
            class="day-column"
          >
            <h3 class="day-header">{{ day.label }}</h3>
            
            <div class="day-schedules">
              <div 
                v-for="schedule in getSchedulesByDay(day.value)" 
                :key="schedule.id"
                class="schedule-card"
              >
                <div class="schedule-time">{{ schedule.start_time }}</div>
                <div class="schedule-info">
                  <strong>{{ schedule.class_name }}</strong>
                  <span>{{ schedule.subject_name }}</span>
                </div>
                <button 
                  @click="handleDelete(schedule.id)" 
                  class="delete-btn"
                  title="Padam"
                >×</button>
              </div>
              
              <div v-if="!getSchedulesByDay(day.value).length" class="empty-day">
                Tiada kelas
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!schedules.length" class="empty-state">
          <p>📅 Tiada jadual. Tambah jadual mengajar anda!</p>
          <button @click="showAddModal = true" class="btn btn-primary">+ Tambah Jadual</button>
        </div>
      </div>

      <!-- Add Schedule Modal -->
      <div v-if="showAddModal" class="modal-overlay" @click.self="showAddModal = false">
        <div class="modal fade-in">
          <h3>➕ Tambah Jadual</h3>
          <form @submit.prevent="handleAddSchedule">
            <div class="form-group">
              <label>Hari</label>
              <select v-model="newSchedule.day_of_week" required>
                <option v-for="day in dayNames" :key="day.value" :value="day.value">
                  {{ day.label }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Masa Mula</label>
              <input 
                v-model="newSchedule.start_time" 
                type="time" 
                required 
              />
            </div>
            <div class="form-group">
              <label>Kelas</label>
              <select v-model="newSchedule.class_id" required>
                <option :value="null" disabled>-- Pilih Kelas --</option>
                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                  {{ cls.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Subjek</label>
              <select v-model="newSchedule.subject_id" required>
                <option :value="null" disabled>-- Pilih Subjek --</option>
                <option v-for="subj in subjectsList" :key="subj.id" :value="subj.id">
                  {{ subj.name }}
                </option>
              </select>
            </div>
            <div class="modal-actions">
              <button type="button" @click="showAddModal = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                {{ saving ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.schedule-page {
  min-height: 100vh;
}

.schedule-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1rem;
  margin-top: 1rem;
}

.day-column {
  background: var(--bg-card);
  border-radius: 1rem;
  overflow: hidden;
}

.day-header {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  padding: 1rem;
  text-align: center;
  font-size: 1rem;
}

.day-schedules {
  padding: 0.5rem;
  min-height: 200px;
}

.schedule-card {
  background: var(--bg-hover);
  border-radius: 0.5rem;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  position: relative;
}

.schedule-time {
  background: var(--primary);
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.schedule-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.schedule-info strong {
  font-size: 0.875rem;
}

.schedule-info span {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.delete-btn {
  position: absolute;
  top: 0.25rem;
  right: 0.25rem;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: none;
  background: var(--danger);
  color: white;
  font-size: 0.875rem;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s;
}

.schedule-card:hover .delete-btn {
  opacity: 1;
}

.empty-day {
  text-align: center;
  padding: 2rem 1rem;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
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

/* Mobile */
@media (max-width: 768px) {
  .schedule-grid {
    grid-template-columns: 1fr;
  }
  
  .day-column {
    margin-bottom: 1rem;
  }
}
</style>
