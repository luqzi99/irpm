<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { teacher, subjects } from '../api'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const classData = ref(null)
const subjectsList = ref([])
const selectedSubject = ref(null)
const progressData = ref(null)
const summaryData = ref(null)

// TP Colors mapping
const tpColors = {
  1: '#ef4444',
  2: '#f97316',
  3: '#eab308',
  4: '#84cc16',
  5: '#22c55e',
  6: '#10b981',
  null: '#334155'
}

onMounted(async () => {
  try {
    const [cls, subjs] = await Promise.all([
      teacher.getClass(route.params.classId),
      subjects.getAll()
    ])
    classData.value = cls
    subjectsList.value = subjs.filter(s => s.level === 'primary')
    
    // Auto-select first subject
    if (subjectsList.value.length) {
      selectedSubject.value = subjectsList.value[0].id
      await loadProgress()
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

async function loadProgress() {
  if (!selectedSubject.value) return
  
  loading.value = true
  try {
    const [progress, summary] = await Promise.all([
      teacher.getProgress(route.params.classId, selectedSubject.value),
      teacher.getSummary(route.params.classId, selectedSubject.value)
    ])
    progressData.value = progress
    summaryData.value = summary
  } catch (e) {
    console.error(e)
    progressData.value = null
    summaryData.value = null
  } finally {
    loading.value = false
  }
}

async function onSubjectChange() {
  await loadProgress()
}

function printReport() {
  window.print()
}

function exportCsv() {
  if (!selectedSubject.value) return
  const url = teacher.getExportCsvUrl(route.params.classId, selectedSubject.value)
  window.open(url, '_blank')
}

function goBack() {
  router.push('/dashboard')
}

function goToTpInput() {
  router.push('/tp-input')
}

function goToStudent(studentId) {
  router.push(`/class/${route.params.classId}/student/${studentId}`)
}

// Computed: average TP
const averageTp = computed(() => {
  if (!summaryData.value) return '-'
  return summaryData.value.average_tp?.toFixed(1) || '-'
})

// Computed: completion percentage
const completion = computed(() => {
  if (!summaryData.value) return 0
  return summaryData.value.completion_percentage || 0
})
</script>

<template>
  <div class="progress-page">
    <!-- Header -->
    <header class="header">
      <button @click="goBack" class="btn btn-secondary">← Kembali</button>
      <h1 v-if="classData">📊 {{ classData.name }}</h1>
      <div class="header-actions">
        <button @click="exportCsv" class="btn btn-secondary" title="Download Excel">📥 Excel</button>
        <button @click="printReport" class="btn btn-secondary" title="Cetak Laporan">🖨️ Cetak</button>
        <button @click="goToTpInput" class="btn btn-primary">📝 Isi TP</button>
      </div>
    </header>
    
    <main class="container">
      <div v-if="loading && !classData" class="loading">
        <div class="spinner"></div>
      </div>
      
      <div v-else class="fade-in">
        <!-- Subject Selector + Summary -->
        <div class="summary-section">
          <div class="subject-selector">
            <label>Subjek:</label>
            <select v-model="selectedSubject" @change="onSubjectChange">
              <option v-for="s in subjectsList" :key="s.id" :value="s.id">
                {{ s.name }}
              </option>
            </select>
          </div>
          
          <div class="summary-cards" v-if="summaryData">
            <div class="stat-card">
              <span class="stat-value">{{ summaryData.total_students }}</span>
              <span class="stat-label">Murid</span>
            </div>
            <div class="stat-card">
              <span class="stat-value">{{ summaryData.total_evaluations }}</span>
              <span class="stat-label">Penilaian</span>
            </div>
            <div class="stat-card highlight">
              <span class="stat-value">TP{{ averageTp }}</span>
              <span class="stat-label">Purata</span>
            </div>
            <div class="stat-card">
              <span class="stat-value">{{ completion }}%</span>
              <span class="stat-label">Selesai</span>
            </div>
          </div>
        </div>
        
        <!-- TP Legend -->
        <div class="legend">
          <span class="legend-title">Tahap Penguasaan:</span>
          <div class="legend-items">
            <span v-for="tp in [1,2,3,4,5,6]" :key="tp" class="legend-item">
              <span class="legend-dot" :style="{ background: tpColors[tp] }"></span>
              TP{{ tp }}
            </span>
            <span class="legend-item">
              <span class="legend-dot" :style="{ background: tpColors[null] }"></span>
              Belum
            </span>
          </div>
        </div>
        
        <!-- Progress Grid -->
        <div class="grid-container" v-if="progressData && progressData.students?.length">
          <div class="grid-wrapper">
            <table class="progress-grid">
              <thead>
                <tr>
                  <th class="student-header">Murid</th>
                  <th 
                    v-for="subtopic in progressData.subtopics" 
                    :key="subtopic.id"
                    class="subtopic-header"
                    :title="subtopic.description"
                  >
                    {{ subtopic.code }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="student in progressData.students" :key="student.id">
                  <td class="student-name" @click="goToStudent(student.id)">
                    <span class="student-link">👤 {{ student.name }}</span>
                  </td>
                  <td 
                    v-for="subtopic in progressData.subtopics" 
                    :key="subtopic.id"
                    class="tp-cell"
                  >
                    <div 
                      class="tp-box"
                      :style="{ background: tpColors[student.evaluations[subtopic.id]?.tp] }"
                      :title="`${student.name}: TP${student.evaluations[subtopic.id]?.tp || '-'}`"
                    >
                      {{ student.evaluations[subtopic.id]?.tp || '' }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <div v-else-if="!loading" class="empty-state">
          <p>📊 Tiada data penilaian untuk subjek ini</p>
          <button @click="goToTpInput" class="btn btn-primary">Mula Isi TP</button>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.progress-page {
  min-height: 100vh;
}

.summary-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.subject-selector {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.subject-selector label {
  font-weight: 500;
  color: var(--text-muted);
}

.subject-selector select {
  min-width: 200px;
}

.summary-cards {
  display: flex;
  gap: 1rem;
}

.stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem 1.5rem;
  text-align: center;
  min-width: 80px;
}

.stat-card.highlight {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border: none;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
}

.stat-label {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.stat-card.highlight .stat-label {
  color: rgba(255,255,255,0.8);
}

.legend {
  background: var(--bg-card);
  border-radius: 0.75rem;
  padding: 1rem;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.legend-title {
  font-weight: 500;
  color: var(--text-muted);
}

.legend-items {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.legend-dot {
  width: 16px;
  height: 16px;
  border-radius: 4px;
}

.grid-container {
  background: var(--bg-card);
  border-radius: 1rem;
  overflow: hidden;
}

.grid-wrapper {
  overflow-x: auto;
}

.progress-grid {
  width: 100%;
  border-collapse: collapse;
  min-width: 600px;
}

.progress-grid th,
.progress-grid td {
  padding: 0.5rem;
  border-bottom: 1px solid var(--border);
}

.student-header {
  text-align: left;
  font-weight: 600;
  padding-left: 1rem;
  position: sticky;
  left: 0;
  background: var(--bg-card);
  z-index: 1;
}

.subtopic-header {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--text-muted);
  text-align: center;
  min-width: 50px;
  cursor: help;
}

.student-name {
  font-weight: 500;
  padding-left: 1rem;
  white-space: nowrap;
  position: sticky;
  left: 0;
  background: var(--bg-card);
  z-index: 1;
  cursor: pointer;
}

.student-link {
  transition: color 0.2s;
}

.student-name:hover .student-link {
  color: var(--primary);
}

.tp-cell {
  text-align: center;
  padding: 0.25rem;
}

.tp-box {
  width: 36px;
  height: 36px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
  margin: 0 auto;
  cursor: pointer;
  transition: transform 0.15s;
}

.tp-box:hover {
  transform: scale(1.1);
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
}

.empty-state p {
  font-size: 1.25rem;
  margin-bottom: 1rem;
}

/* Mobile */
@media (max-width: 768px) {
  .summary-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .summary-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
  }
  
  .legend {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .header-actions {
    display: flex;
    gap: 0.5rem;
  }
  
  .header-actions .btn {
    padding: 0.5rem;
    font-size: 0.75rem;
  }
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

/* Print styles */
@media print {
  .header,
  .subject-selector,
  .header-actions {
    display: none !important;
  }
  
  .progress-page {
    background: white;
    color: black;
  }
  
  .grid-container,
  .summary-cards .stat-card,
  .legend {
    background: white;
    border: 1px solid #ccc;
  }
  
  .tp-box {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }
}
</style>
