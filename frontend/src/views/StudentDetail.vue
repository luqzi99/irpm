<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { teacher, subjects } from '../api'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const studentData = ref(null)
const subjectsList = ref([])
const selectedSubject = ref(null)
const progressData = ref(null)

// TP Colors
const tpColors = {
  1: '#ef4444',
  2: '#f97316',
  3: '#eab308',
  4: '#84cc16',
  5: '#22c55e',
  6: '#10b981',
  null: '#334155'
}

// Get TP background color style
function getTpStyle(tp) {
  return { background: tpColors[tp] || tpColors[null] }
}

onMounted(async () => {
  try {
    const subjs = await subjects.getAll()
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
    const data = await teacher.getStudentProgress(route.params.studentId, selectedSubject.value)
    studentData.value = data.student
    progressData.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function onSubjectChange() {
  await loadProgress()
}

function goBack() {
  // Go back to class progress
  if (route.params.classId) {
    router.push(`/class/${route.params.classId}/progress`)
  } else {
    router.back()
  }
}

// Computed: TP badge color based on overall TP
const tpBadgeStyle = computed(() => {
  const tp = progressData.value?.summary?.overall_tp
  if (!tp) return { background: '#334155' }
  
  // Gradient for overall TP
  return {
    background: 'linear-gradient(135deg, var(--primary), var(--secondary))'
  }
})
</script>

<template>
  <div class="student-detail-page">
    <!-- Header -->
    <header class="header">
      <button @click="goBack" class="btn btn-secondary">← Kembali</button>
      <div class="header-center" v-if="studentData">
        <h1>👤 {{ studentData.name }}</h1>
      </div>
      <div class="tp-badge" :style="tpBadgeStyle" v-if="progressData?.summary?.overall_tp">
        TP{{ progressData.summary.overall_tp }}
      </div>
      <div class="tp-badge empty" v-else>-</div>
    </header>
    
    <main class="container">
      <div v-if="loading && !studentData" class="loading">
        <div class="spinner"></div>
      </div>
      
      <div v-else class="fade-in">
        <!-- Summary Cards + Subject Selector -->
        <div class="summary-section">
          <div class="subject-selector">
            <label>Subjek:</label>
            <select v-model="selectedSubject" @change="onSubjectChange">
              <option v-for="s in subjectsList" :key="s.id" :value="s.id">
                {{ s.name }}
              </option>
            </select>
          </div>
          
          <div class="summary-cards" v-if="progressData?.summary">
            <div class="stat-card">
              <span class="stat-value">{{ progressData.summary.total_evaluations }}</span>
              <span class="stat-label">Penilaian</span>
            </div>
            <div class="stat-card highlight">
              <span class="stat-value">TP{{ progressData.summary.overall_tp || '-' }}</span>
              <span class="stat-label">Purata</span>
            </div>
            <div class="stat-card">
              <span class="stat-value level">{{ progressData.summary.performance_level }}</span>
              <span class="stat-label">Tahap</span>
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
        
        <!-- Topic Progress Table -->
        <div class="progress-table" v-if="progressData?.topics?.length">
          <table>
            <thead>
              <tr>
                <th class="topic-col">Topik</th>
                <th class="avg-col">Purata</th>
                <th class="subtopics-header">Standard Pembelajaran</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="topic in progressData.topics" :key="topic.id">
                <td class="topic-name">
                  {{ topic.sequence }}. {{ topic.title }}
                </td>
                <td class="topic-avg">
                  <div 
                    class="tp-cell avg-cell"
                    :style="getTpStyle(topic.average_tp ? Math.round(topic.average_tp) : null)"
                  >
                    {{ topic.average_tp ? `TP${topic.average_tp}` : '-' }}
                  </div>
                </td>
                <td class="subtopics-row">
                  <div class="subtopics-grid">
                    <div 
                      v-for="st in topic.subtopics" 
                      :key="st.id"
                      class="subtopic-item"
                    >
                      <span class="subtopic-code">{{ st.code }}</span>
                      <div 
                        class="tp-cell"
                        :style="getTpStyle(st.tp)"
                        :title="`${st.description}\n${st.date || 'Belum dinilai'}`"
                      >
                        {{ st.tp || '-' }}
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div v-else-if="!loading" class="empty-state">
          <p>📊 Tiada penilaian untuk murid ini</p>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.student-detail-page {
  min-height: 100vh;
}

.header-center {
  flex: 1;
  text-align: center;
}

.header-center h1 {
  font-size: 1.25rem;
}

.tp-badge {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 700;
  font-size: 1.25rem;
  color: white;
}

.tp-badge.empty {
  background: var(--bg-hover);
  color: var(--text-muted);
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
  min-width: 100px;
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

.stat-value.level {
  font-size: 1rem;
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

/* Progress Table */
.progress-table {
  background: var(--bg-card);
  border-radius: 1rem;
  overflow: hidden;
}

.progress-table table {
  width: 100%;
  border-collapse: collapse;
}

.progress-table th {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border);
  font-weight: 600;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.topic-col {
  width: 200px;
}

.avg-col {
  width: 80px;
  text-align: center !important;
}

.subtopics-header {
  text-align: center !important;
}

.progress-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--border);
  vertical-align: middle;
}

.progress-table tr:last-child td {
  border-bottom: none;
}

.topic-name {
  font-weight: 500;
}

.topic-avg {
  text-align: center;
}

.tp-cell {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 36px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  color: white;
}

.avg-cell {
  min-width: 60px;
  font-size: 0.75rem;
}

.subtopics-row {
  padding: 0.5rem 1rem !important;
}

.subtopics-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.subtopic-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.subtopic-code {
  font-size: 0.625rem;
  color: var(--text-muted);
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
}

/* Mobile */
@media (max-width: 768px) {
  .summary-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .summary-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
  }
  
  .stat-card {
    padding: 0.75rem;
    min-width: auto;
  }
  
  .stat-value {
    font-size: 1.25rem;
  }
  
  .legend {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .progress-table {
    overflow-x: auto;
  }
  
  .progress-table table {
    min-width: 600px;
  }
}
</style>
