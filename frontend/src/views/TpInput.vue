<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { teacher, subjects } from '../api'

const router = useRouter()

// State
const loading = ref(true)
const saving = ref(false)
const toast = ref('')
const selectorsCollapsed = ref(false)

// Dropdown data
const classes = ref([])
const subjectsList = ref([])
const topics = ref([])
const subtopicsList = ref([])

// Selected values
const selectedClass = ref(null)
const selectedSubject = ref(null)
const selectedTopic = ref(null)
const selectedSubtopic = ref(null)

// Student TP data
const students = ref([])

// Computed: check if all selections are made
const allSelected = computed(() => {
  return selectedClass.value && selectedSubject.value && selectedTopic.value && selectedSubtopic.value
})

// Computed: selection summary text
const selectionSummary = computed(() => {
  if (!allSelected.value) return ''
  const cls = classes.value.find(c => c.id === selectedClass.value)
  const subj = subjectsList.value.find(s => s.id === selectedSubject.value)
  const st = subtopicsList.value.find(s => s.id === selectedSubtopic.value)
  return `${cls?.name} • ${subj?.name} • ${st?.code}`
})

// Load initial data
onMounted(async () => {
  try {
    const [classData, subjectData] = await Promise.all([
      teacher.getClasses(),
      subjects.getAll()
    ])
    classes.value = classData
    subjectsList.value = subjectData.filter(s => s.level === 'primary')
    
    // Auto-populate from current schedule
    try {
      const currentSchedule = await teacher.getCurrentSchedule()
      if (currentSchedule) {
        selectedClass.value = currentSchedule.class_id
        selectedSubject.value = currentSchedule.subject_id
        // Show toast notification
        toast.value = `📅 ${currentSchedule.class_name} - ${currentSchedule.subject_name}`
        setTimeout(() => toast.value = '', 3000)
      }
    } catch (e) {
      // No current schedule, that's okay
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

// When subject changes, load topics
watch(selectedSubject, async (subjectId) => {
  if (!subjectId) return
  topics.value = []
  subtopicsList.value = []
  selectedTopic.value = null
  selectedSubtopic.value = null
  
  try {
    const year = new Date().getFullYear()
    topics.value = await subjects.getTopics(subjectId, year)
  } catch (e) {
    console.error(e)
  }
})

// When topic changes, show subtopics
watch(selectedTopic, (topicId) => {
  if (!topicId) return
  const topic = topics.value.find(t => t.id == topicId)
  subtopicsList.value = topic?.subtopics || []
  selectedSubtopic.value = null
})

// When class + subtopic ready, load students with latest TP
watch([selectedClass, selectedSubtopic], async ([classId, subtopicId]) => {
  if (!classId || !subtopicId || !selectedSubject.value) return
  
  loading.value = true
  try {
    const data = await teacher.getLatestTp(classId, selectedSubject.value, subtopicId)
    students.value = data.map(s => ({
      ...s,
      newTp: s.tp || null,
      changed: false
    }))
    // Auto-collapse on mobile when students loaded
    if (window.innerWidth <= 768) {
      selectorsCollapsed.value = true
    }
  } catch (e) {
    console.error(e)
    // Fallback: load students without TP
    const studentList = await teacher.getStudents(classId)
    students.value = studentList.map(s => ({
      student_id: s.id,
      student_name: s.name,
      tp: null,
      newTp: null,
      changed: false
    }))
    if (window.innerWidth <= 768) {
      selectorsCollapsed.value = true
    }
  } finally {
    loading.value = false
  }
})

// Set TP for student
function setTp(student, tp) {
  student.newTp = tp
  student.changed = true
}

// Save all changed TPs
async function saveAll() {
  const changedStudents = students.value.filter(s => s.changed && s.newTp)
  
  if (!changedStudents.length) {
    toast.value = 'Tiada perubahan untuk disimpan'
    setTimeout(() => toast.value = '', 3000)
    return
  }

  saving.value = true
  try {
    await teacher.saveTp(
      selectedClass.value,
      selectedSubject.value,
      selectedSubtopic.value,
      null, // assessment_method_id (optional for now)
      changedStudents.map(s => ({
        student_id: s.student_id,
        tp: s.newTp
      }))
    )
    
    // Update UI
    changedStudents.forEach(s => {
      s.tp = s.newTp
      s.changed = false
    })
    
    toast.value = `✅ ${changedStudents.length} penilaian disimpan!`
    setTimeout(() => toast.value = '', 3000)
  } catch (e) {
    toast.value = '❌ Gagal menyimpan'
    setTimeout(() => toast.value = '', 3000)
  } finally {
    saving.value = false
  }
}

function goBack() {
  router.push('/dashboard')
}

function toggleSelectors() {
  selectorsCollapsed.value = !selectorsCollapsed.value
}
</script>

<template>
  <div class="tp-input-page">
    <!-- Header -->
    <header class="header">
      <button @click="goBack" class="btn btn-secondary">← Kembali</button>
      <h1>📝 Isi TP</h1>
      <button 
        @click="saveAll" 
        class="btn btn-primary"
        :disabled="saving"
      >
        {{ saving ? 'Menyimpan...' : '💾 Simpan' }}
      </button>
    </header>
    
    <!-- Collapsible Selectors -->
    <div class="selectors" :class="{ collapsed: selectorsCollapsed }">
      <!-- Toggle button (mobile only) -->
      <button 
        v-if="allSelected" 
        class="selector-toggle"
        @click="toggleSelectors"
      >
        <span v-if="selectorsCollapsed">
          📋 {{ selectionSummary }} <span class="expand-icon">▼</span>
        </span>
        <span v-else>
          <span class="collapse-icon">▲</span> Tutup
        </span>
      </button>
      
      <!-- Selector content -->
      <div class="selector-content" v-show="!selectorsCollapsed">
        <div class="selector-row">
          <select v-model="selectedClass">
            <option :value="null" disabled>Pilih Kelas</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">
              {{ c.name }} ({{ c.year }})
            </option>
          </select>
          
          <select v-model="selectedSubject">
            <option :value="null" disabled>Pilih Subjek</option>
            <option v-for="s in subjectsList" :key="s.id" :value="s.id">
              {{ s.name }}
            </option>
          </select>
        </div>
        
        <div class="selector-row" v-if="topics.length">
          <select v-model="selectedTopic">
            <option :value="null" disabled>Pilih Topik</option>
            <option v-for="t in topics" :key="t.id" :value="t.id">
              {{ t.sequence }}. {{ t.title }}
            </option>
          </select>
          
          <select v-model="selectedSubtopic" v-if="subtopicsList.length">
            <option :value="null" disabled>Pilih Standard Pembelajaran</option>
            <option v-for="st in subtopicsList" :key="st.id" :value="st.id">
              {{ st.code }} - {{ st.description.substring(0, 50) }}...
            </option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Students List -->
    <main class="student-list">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
      </div>
      
      <div v-else-if="!selectedSubtopic" class="empty-state">
        <p>👆 Sila pilih kelas, subjek, topik dan standard pembelajaran</p>
      </div>
      
      <div v-else-if="!students.length" class="empty-state">
        <p>Tiada murid dalam kelas ini</p>
      </div>
      
      <div v-else class="students fade-in">
        <!-- Legend -->
        <div class="legend">
          <span class="legend-item"><span class="dot tp-1"></span> TP1 Perlu bimbingan</span>
          <span class="legend-item"><span class="dot tp-6"></span> TP6 Cemerlang</span>
        </div>
        
        <!-- Student Rows -->
        <div 
          v-for="student in students" 
          :key="student.student_id" 
          class="student-row"
          :class="{ changed: student.changed }"
        >
          <div class="student-info">
            <span class="student-name">{{ student.student_name }}</span>
            <span v-if="student.tp && !student.changed" class="current-tp">
              Semasa: TP{{ student.tp }}
            </span>
          </div>
          
          <div class="tp-buttons">
            <button 
              v-for="tp in [1,2,3,4,5,6]" 
              :key="tp"
              class="tp-btn"
              :class="[`tp-${tp}`, { active: student.newTp === tp }]"
              @click="setTp(student, tp)"
            >
              {{ tp }}
            </button>
          </div>
        </div>
      </div>
    </main>
    
    <!-- Toast -->
    <div v-if="toast" class="toast">{{ toast }}</div>
  </div>
</template>

<style scoped>
.tp-input-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  position: sticky;
  top: 0;
  z-index: 10;
}

.selectors {
  background: var(--bg-card);
  border-bottom: 1px solid var(--border);
}

.selector-toggle {
  display: none;
  width: 100%;
  padding: 0.75rem 1rem;
  background: var(--bg-hover);
  border: none;
  color: var(--text);
  font-size: 0.875rem;
  cursor: pointer;
  text-align: left;
}

.selector-toggle:hover {
  background: var(--border);
}

.expand-icon, .collapse-icon {
  float: right;
  font-size: 0.75rem;
}

.selector-content {
  padding: 1rem;
}

.selector-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.selector-row:last-child {
  margin-bottom: 0;
}

.selector-row select {
  flex: 1;
}

.student-list {
  flex: 1;
  padding: 1rem;
}

.legend {
  display: flex;
  gap: 2rem;
  padding: 1rem;
  background: var(--bg-card);
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: var(--text-muted);
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.dot.tp-1 { background: var(--tp1); }
.dot.tp-6 { background: var(--tp6); }

.students {
  background: var(--bg-card);
  border-radius: 1rem;
  overflow: hidden;
}

.student-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
  transition: background 0.2s;
}

.student-row:last-child {
  border-bottom: none;
}

.student-row.changed {
  background: rgba(99, 102, 241, 0.1);
}

.student-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.student-name {
  font-weight: 500;
}

.current-tp {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.tp-buttons {
  display: flex;
  gap: 0.5rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
}

.empty-state p {
  font-size: 1.25rem;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .selector-toggle {
    display: block;
  }
  
  .selectors.collapsed .selector-content {
    display: none;
  }
  
  .selector-row {
    flex-direction: column;
  }
  
  .student-row {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .tp-buttons {
    width: 100%;
    justify-content: space-between;
  }
  
  .tp-btn {
    flex: 1;
  }
  
  .legend {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>
