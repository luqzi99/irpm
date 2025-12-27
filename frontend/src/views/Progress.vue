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
      teacher.getMySubjects()  // Only subjects teacher is teaching
    ])
    classData.value = cls
    subjectsList.value = subjs
    
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

async function exportCsv() {
  if (!selectedSubject.value) return
  try {
    await teacher.exportCsv(route.params.classId, selectedSubject.value)
  } catch (e) {
    console.error('Export failed:', e)
    alert('Export gagal')
  }
}

function exportExcel() {
  if (!selectedSubject.value) return
  
  const token = localStorage.getItem('token')
  const url = `${import.meta.env.VITE_API_URL || 'http://localhost:8000/api'}/teacher/classes/${route.params.classId}/export-excel?subject_id=${selectedSubject.value}&token=${token}`
  
  // Use direct navigation - bypasses CORS and triggers immediate download
  window.location.href = url
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
        <button @click="exportExcel" class="btn btn-secondary" title="Download Excel">📥 Excel</button>
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
        
        <!-- Student Progress List -->
        <div class="student-list" v-if="progressData && progressData.students?.length">
          <table class="progress-table">
            <thead>
              <tr>
                <th class="name-col">Murid</th>
                <th class="tp-col">TP Purata</th>
                <th class="progress-col">Kemajuan</th>
                <th class="action-col">Tindakan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in progressData.students" :key="student.id">
                <td class="name-cell">
                  <span class="student-icon">👤</span>
                  {{ student.name }}
                </td>
                <td class="tp-cell">
                  <div 
                    class="tp-badge"
                    :style="{ background: tpColors[student.average_tp] }"
                  >
                    {{ student.average_tp ? `TP${student.average_tp}` : '-' }}
                  </div>
                </td>
                <td class="progress-cell">
                  <div class="progress-bar-container">
                    <div 
                      class="progress-bar-fill" 
                      :style="{ width: (student.completion_percentage || 0) + '%' }"
                    ></div>
                    <span class="progress-text">{{ student.completion_percentage || 0 }}%</span>
                  </div>
                </td>
                <td class="action-cell">
                  <button @click="goToStudent(student.id)" class="btn btn-sm btn-secondary">
                    📋 Lihat
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
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

/* Progress Table Styles */
.student-list {
  background: var(--bg-card);
  border-radius: 1rem;
  overflow: hidden;
}

.progress-table {
  width: 100%;
  border-collapse: collapse;
}

.progress-table th,
.progress-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--border);
  text-align: left;
}

.progress-table th {
  background: var(--bg-hover);
  font-weight: 600;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.name-col { width: 35%; }
.tp-col { width: 15%; text-align: center; }
.progress-col { width: 35%; }
.action-col { width: 15%; text-align: center; }

.name-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.student-icon {
  font-size: 1.25rem;
}

.tp-cell {
  text-align: center;
}

.tp-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 700;
  color: white;
  min-width: 60px;
  font-size: 0.875rem;
}

.progress-cell {
  padding-right: 1rem;
}

.progress-bar-container {
  position: relative;
  height: 24px;
  background: var(--bg-hover);
  border-radius: 0.5rem;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  transition: width 0.3s ease;
}

.progress-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text);
}

.action-cell {
  text-align: center;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.progress-table tbody tr:hover {
  background: var(--bg-hover);
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
