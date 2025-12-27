<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { admin } from '../../api'

const router = useRouter()
const loading = ref(true)
const levels = ref([])
const selectedLevel = ref(null)
const subjects = ref([])
const selectedSubject = ref(null)
const subjectDetail = ref(null)

// Modals
const showAddSubject = ref(false)
const showAddTopic = ref(false)
const showAddSubtopic = ref(false)
const showImport = ref(false)
const showEditTopic = ref(false)
const showEditSubtopic = ref(false)

// Forms
const newSubject = ref({ name: '', code: '' })
const newTopic = ref({ code: '', name: '' })
const newSubtopic = ref({ code: '', name: '', tp1: '', tp2: '', tp3: '', tp4: '', tp5: '', tp6: '' })
const selectedTopicId = ref(null)
const importFile = ref(null)
const importing = ref(false)
const importResult = ref(null)
const importPreview = ref(null)
const previewing = ref(false)

// Edit state
const editingTopic = ref(null)
const editingSubtopic = ref(null)

onMounted(async () => {
  try {
    levels.value = await admin.getLevels()
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

async function selectLevel(level) {
  selectedLevel.value = level
  selectedSubject.value = null
  subjectDetail.value = null
  try {
    subjects.value = await admin.getSubjectsByLevel(level.id)
  } catch (e) {
    console.error(e)
  }
}

async function selectSubject(subject) {
  selectedSubject.value = subject
  try {
    subjectDetail.value = await admin.getSubjectDetail(subject.id)
  } catch (e) {
    console.error(e)
  }
}

async function handleAddSubject() {
  try {
    await admin.storeSubject({
      ...newSubject.value,
      education_level: selectedLevel.value.id,
    })
    subjects.value = await admin.getSubjectsByLevel(selectedLevel.value.id)
    showAddSubject.value = false
    newSubject.value = { name: '', code: '' }
  } catch (e) {
    alert(e.message)
  }
}

async function handleDeleteSubject(subject) {
  if (!confirm(`Padam subjek "${subject.name}" dan semua DSKP?`)) return
  try {
    await admin.deleteSubject(subject.id)
    subjects.value = subjects.value.filter(s => s.id !== subject.id)
    if (selectedSubject.value?.id === subject.id) {
      selectedSubject.value = null
      subjectDetail.value = null
    }
  } catch (e) {
    alert(e.message)
  }
}

async function handleAddTopic() {
  try {
    await admin.storeTopic({
      ...newTopic.value,
      subject_id: selectedSubject.value.id,
    })
    subjectDetail.value = await admin.getSubjectDetail(selectedSubject.value.id)
    showAddTopic.value = false
    newTopic.value = { code: '', name: '' }
  } catch (e) {
    alert(e.message)
  }
}

async function handleAddSubtopic() {
  try {
    await admin.storeSubtopic({
      ...newSubtopic.value,
      topic_id: selectedTopicId.value,
    })
    subjectDetail.value = await admin.getSubjectDetail(selectedSubject.value.id)
    showAddSubtopic.value = false
    newSubtopic.value = { code: '', name: '', tp1: '', tp2: '', tp3: '', tp4: '', tp5: '', tp6: '' }
  } catch (e) {
    alert(e.message)
  }
}

function openAddSubtopic(topicId) {
  selectedTopicId.value = topicId
  showAddSubtopic.value = true
}

// Edit topic
function openEditTopic(topic) {
  editingTopic.value = { ...topic }
  showEditTopic.value = true
}

async function handleEditTopic() {
  try {
    await admin.updateTopic(editingTopic.value.id, {
      title: editingTopic.value.name,
    })
    subjectDetail.value = await admin.getSubjectDetail(selectedSubject.value.id)
    showEditTopic.value = false
  } catch (e) {
    alert(e.message)
  }
}

async function handleDeleteTopic(topic) {
  if (!confirm(`Padam topik "${topic.name}"?`)) return
  try {
    await admin.deleteTopic(topic.id)
    subjectDetail.value = await admin.getSubjectDetail(selectedSubject.value.id)
  } catch (e) {
    alert(e.message)
  }
}

// Edit subtopic
function openEditSubtopic(st) {
  editingSubtopic.value = { ...st }
  showEditSubtopic.value = true
}

async function handleEditSubtopic() {
  try {
    await admin.updateSubtopic(editingSubtopic.value.id, {
      description: editingSubtopic.value.name,
      tp_max: editingSubtopic.value.tp_max,
    })
    subjectDetail.value = await admin.getSubjectDetail(selectedSubject.value.id)
    showEditSubtopic.value = false
  } catch (e) {
    alert(e.message)
  }
}

async function handleDeleteSubtopic(st) {
  if (!confirm(`Padam subtopik "${st.code}"?`)) return
  try {
    await admin.deleteSubtopic(st.id)
    subjectDetail.value = await admin.getSubjectDetail(selectedSubject.value.id)
  } catch (e) {
    alert(e.message)
  }
}

function openImport() {
  importFile.value = null
  importResult.value = null
  importPreview.value = null
  showImport.value = true
}

function closeImport() {
  showImport.value = false
  importPreview.value = null
  importFile.value = null
  importResult.value = null
}

function resetImport() {
  importPreview.value = null
  importResult.value = null
}

function handleFileSelect(e) {
  importFile.value = e.target.files[0]
}

async function handlePreview() {
  if (!importFile.value) return
  
  previewing.value = true
  
  try {
    importPreview.value = await admin.previewDskp(importFile.value)
  } catch (e) {
    alert('Gagal memproses fail: ' + e.message)
  } finally {
    previewing.value = false
  }
}

async function handleImport() {
  if (!importFile.value || !selectedSubject.value) return
  
  importing.value = true
  importResult.value = null
  
  try {
    const result = await admin.importDskp(selectedSubject.value.id, importFile.value)
    importResult.value = { success: true, message: result.message }
    // Refresh subject detail
    subjectDetail.value = await admin.getSubjectDetail(selectedSubject.value.id)
  } catch (e) {
    importResult.value = { success: false, message: e.message }
  } finally {
    importing.value = false
  }
}

function downloadTemplate() {
  window.open(admin.getTemplateUrl(), '_blank')
}

function goBack() {
  if (subjectDetail.value) {
    subjectDetail.value = null
    selectedSubject.value = null
  } else if (selectedLevel.value) {
    selectedLevel.value = null
    subjects.value = []
  } else {
    router.push('/admin')
  }
}
</script>

<template>
  <div class="dskp-page">
    <header class="header">
      <button @click="goBack" class="btn btn-secondary">← Kembali</button>
      <h1>📚 Pengurusan DSKP</h1>
      <div></div>
    </header>

    <main class="container">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else class="fade-in">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
          <span @click="selectedLevel = null; subjects = []; subjectDetail = null; selectedSubject = null" class="crumb">📁 Tahap</span>
          <span v-if="selectedLevel" class="separator">/</span>
          <span v-if="selectedLevel" @click="subjectDetail = null; selectedSubject = null" class="crumb">{{ selectedLevel.name }}</span>
          <span v-if="selectedSubject" class="separator">/</span>
          <span v-if="selectedSubject" class="crumb active">{{ selectedSubject.name }}</span>
        </div>

        <!-- Level 1: Education Levels -->
        <div v-if="!selectedLevel" class="grid">
          <div 
            v-for="level in levels" 
            :key="level.id" 
            class="folder-card"
            :class="{ primary: level.type === 'primary', secondary: level.type === 'secondary' }"
            @click="selectLevel(level)"
          >
            <span class="folder-icon">📁</span>
            <span class="folder-name">{{ level.name }}</span>
            <span class="folder-count">{{ level.subjects_count }} subjek</span>
          </div>
        </div>

        <!-- Level 2: Subjects -->
        <div v-else-if="!subjectDetail" class="subjects-list">
          <div class="list-header">
            <h3>Subjek - {{ selectedLevel.name }}</h3>
            <button @click="showAddSubject = true" class="btn btn-primary btn-sm">+ Tambah Subjek</button>
          </div>
          
          <div v-if="subjects.length === 0" class="empty">
            <p>Tiada subjek. Tambah subjek pertama!</p>
          </div>
          
          <div v-for="subj in subjects" :key="subj.id" class="subject-card" @click="selectSubject(subj)">
            <div class="subject-info">
              <strong>{{ subj.name }}</strong>
              <span class="subject-code">{{ subj.code }}</span>
            </div>
            <div class="subject-meta">
              <span>{{ subj.topics_count }} topik</span>
              <button @click.stop="handleDeleteSubject(subj)" class="delete-btn">🗑️</button>
            </div>
          </div>
        </div>

        <!-- Level 3: Subject Detail (Topics & Subtopics) -->
        <div v-else class="subject-detail">
          <div class="list-header">
            <h3>{{ subjectDetail.subject.name }} - DSKP</h3>
            <div class="header-buttons">
              <button @click="openImport" class="btn btn-secondary btn-sm">📥 Import CSV</button>
              <button @click="showAddTopic = true" class="btn btn-primary btn-sm">+ Tambah Topik</button>
            </div>
          </div>

          <div v-if="!subjectDetail.sections || subjectDetail.sections.length === 0" class="empty">
            <p>Tiada DSKP. Import dari fail untuk bermula!</p>
          </div>

          <!-- Sections (Theme > Title) -->
          <div v-for="section in subjectDetail.sections" :key="section.id" class="title-group">
            <div class="title-header">
              <span class="title-badge">📚</span>
              <span class="title-name">{{ section.full_title }}</span>
              <span class="theme-badge">{{ section.theme }}</span>
            </div>

            <div v-for="topic in section.topics" :key="topic.id" class="topic-card">
              <div class="topic-header">
                <span class="topic-code">{{ topic.code }}</span>
                <span class="topic-name">{{ topic.name }}</span>
                <div class="topic-actions">
                  <button @click="openEditTopic(topic)" class="btn-icon" title="Edit">✏️</button>
                  <button @click="handleDeleteTopic(topic)" class="btn-icon danger" title="Padam">🗑️</button>
                  <button @click="openAddSubtopic(topic.id)" class="btn btn-sm">+ Subtopik</button>
                </div>
              </div>
              
              <div class="subtopics">
                <div v-for="st in topic.subtopics" :key="st.id" class="subtopic-row">
                  <span class="st-code">{{ st.code }}</span>
                  <span class="st-name">{{ st.name }}</span>
                  <div class="tp-badges">
                    <span 
                      v-for="tp in (st.tp_max || 6)" 
                      :key="tp" 
                      class="tp-badge"
                      :class="{ filled: st.tp_descriptions && st.tp_descriptions[tp] }"
                      :title="st.tp_descriptions && st.tp_descriptions[tp] ? st.tp_descriptions[tp] : ''"
                    >TP{{ tp }}</span>
                  </div>
                  <div class="st-actions">
                    <button @click="openEditSubtopic(st)" class="btn-icon" title="Edit">✏️</button>
                    <button @click="handleDeleteSubtopic(st)" class="btn-icon danger" title="Padam">🗑️</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Subject Modal -->
      <div v-if="showAddSubject" class="modal-overlay" @click.self="showAddSubject = false">
        <div class="modal fade-in">
          <h3>➕ Tambah Subjek</h3>
          <form @submit.prevent="handleAddSubject">
            <div class="form-group">
              <label>Kod Subjek</label>
              <input v-model="newSubject.code" placeholder="e.g. BM" required />
            </div>
            <div class="form-group">
              <label>Nama Subjek</label>
              <input v-model="newSubject.name" placeholder="e.g. Bahasa Melayu" required />
            </div>
            <div class="modal-actions">
              <button type="button" @click="showAddSubject = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Topic Modal -->
      <div v-if="showAddTopic" class="modal-overlay" @click.self="showAddTopic = false">
        <div class="modal fade-in">
          <h3>➕ Tambah Topik</h3>
          <form @submit.prevent="handleAddTopic">
            <div class="form-group">
              <label>Kod Topik</label>
              <input v-model="newTopic.code" placeholder="e.g. 1.0" required />
            </div>
            <div class="form-group">
              <label>Nama Topik</label>
              <input v-model="newTopic.name" placeholder="e.g. Kemahiran Mendengar" required />
            </div>
            <div class="modal-actions">
              <button type="button" @click="showAddTopic = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Subtopic Modal -->
      <div v-if="showAddSubtopic" class="modal-overlay" @click.self="showAddSubtopic = false">
        <div class="modal fade-in large">
          <h3>➕ Tambah Subtopik</h3>
          <form @submit.prevent="handleAddSubtopic">
            <div class="form-row">
              <div class="form-group">
                <label>Kod</label>
                <input v-model="newSubtopic.code" placeholder="e.g. 1.1" required />
              </div>
              <div class="form-group flex-2">
                <label>Nama Subtopik</label>
                <input v-model="newSubtopic.name" placeholder="Nama subtopik" required />
              </div>
            </div>
            <div class="tp-grid">
              <div class="form-group">
                <label>TP1</label>
                <textarea v-model="newSubtopic.tp1" rows="2"></textarea>
              </div>
              <div class="form-group">
                <label>TP2</label>
                <textarea v-model="newSubtopic.tp2" rows="2"></textarea>
              </div>
              <div class="form-group">
                <label>TP3</label>
                <textarea v-model="newSubtopic.tp3" rows="2"></textarea>
              </div>
              <div class="form-group">
                <label>TP4</label>
                <textarea v-model="newSubtopic.tp4" rows="2"></textarea>
              </div>
              <div class="form-group">
                <label>TP5</label>
                <textarea v-model="newSubtopic.tp5" rows="2"></textarea>
              </div>
              <div class="form-group">
                <label>TP6</label>
                <textarea v-model="newSubtopic.tp6" rows="2"></textarea>
              </div>
            </div>
            <div class="modal-actions">
              <button type="button" @click="showAddSubtopic = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Import CSV Modal -->
      <div v-if="showImport" class="modal-overlay" @click.self="closeImport">
        <div class="modal modal-lg fade-in">
          <h3>📥 Import DSKP dari Fail</h3>
          
          <!-- Step 1: File Selection -->
          <div v-if="!importPreview">
            <div class="import-info">
              <p>Format (tab-separated, 4 lajur):</p>
              <code>Theme | Title | Topic (SK) | Subtopic (SP)</code>
              <p class="format-example">Contoh: SAINS HAYAT | 2.0 MANUSIA | 2.1 Sistem Rangka | 2.1.1 Memerihalkan...</p>
            </div>
            
            <div class="form-group">
              <label>Pilih fail CSV</label>
              <input type="file" accept=".csv,.txt" @change="handleFileSelect" />
            </div>
            
            <div class="modal-actions">
              <button type="button" @click="closeImport" class="btn btn-secondary">Tutup</button>
              <button @click="handlePreview" :disabled="!importFile || previewing" class="btn btn-primary">
                {{ previewing ? 'Memproses...' : '👁️ Pratonton' }}
              </button>
            </div>
          </div>
          
          <!-- Step 2: Preview -->
          <div v-else>
            <div class="preview-summary">
              <div class="summary-card">
                <span class="summary-number">{{ importPreview.summary.total_sections }}</span>
                <span class="summary-label">Seksyen</span>
              </div>
              <div class="summary-card">
                <span class="summary-number">{{ importPreview.summary.total_topics }}</span>
                <span class="summary-label">Topik</span>
              </div>
              <div class="summary-card">
                <span class="summary-number">{{ importPreview.summary.total_subtopics }}</span>
                <span class="summary-label">Subtopik</span>
              </div>
              <div v-if="importPreview.summary.total_errors > 0" class="summary-card error">
                <span class="summary-number">{{ importPreview.summary.total_errors }}</span>
                <span class="summary-label">Error</span>
              </div>
            </div>
            
            <!-- Errors -->
            <div v-if="importPreview.errors.length > 0" class="preview-errors">
              <h4>⚠️ Ralat</h4>
              <ul>
                <li v-for="(err, i) in importPreview.errors.slice(0, 5)" :key="i">{{ err }}</li>
              </ul>
            </div>
            
            <!-- Preview Table -->
            <div class="preview-table-wrapper">
              <h4>📋 Contoh Data (20 baris pertama)</h4>
              <table class="preview-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Theme</th>
                    <th>Title</th>
                    <th>Topic (SK)</th>
                    <th>Subtopic (SP)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in importPreview.raw_rows" :key="row.row">
                    <td>{{ row.row }}</td>
                    <td>{{ row.theme }}</td>
                    <td>{{ row.title }}</td>
                    <td>{{ row.topic }}</td>
                    <td>{{ row.subtopic?.substring(0, 50) }}{{ row.subtopic?.length > 50 ? '...' : '' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <div v-if="importResult" class="import-result" :class="{ success: importResult.success, error: !importResult.success }">
              {{ importResult.message }}
            </div>
            
            <div class="modal-actions">
              <button type="button" @click="resetImport" class="btn btn-secondary">← Kembali</button>
              <button @click="handleImport" :disabled="importing" class="btn btn-primary">
                {{ importing ? 'Mengimport...' : '✅ Sahkan Import' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Topic Modal -->
      <div v-if="showEditTopic && editingTopic" class="modal-overlay" @click.self="showEditTopic = false">
        <div class="modal fade-in">
          <h3>✏️ Edit Topik</h3>
          <form @submit.prevent="handleEditTopic">
            <div class="form-group">
              <label>Nama Topik</label>
              <input v-model="editingTopic.name" required />
            </div>
            <div class="modal-actions">
              <button type="button" @click="showEditTopic = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Subtopic Modal -->
      <div v-if="showEditSubtopic && editingSubtopic" class="modal-overlay" @click.self="showEditSubtopic = false">
        <div class="modal fade-in">
          <h3>✏️ Edit Subtopik</h3>
          <form @submit.prevent="handleEditSubtopic">
            <div class="form-group">
              <label>Deskripsi</label>
              <input v-model="editingSubtopic.name" required />
            </div>
            <div class="form-group">
              <label>TP Max (1-6)</label>
              <select v-model="editingSubtopic.tp_max">
                <option :value="4">4</option>
                <option :value="5">5</option>
                <option :value="6">6</option>
              </select>
            </div>
            <div class="modal-actions">
              <button type="button" @click="showEditSubtopic = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.dskp-page { min-height: 100vh; }

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.crumb {
  color: var(--text-muted);
  cursor: pointer;
}

.crumb:hover { color: var(--primary); }
.crumb.active { color: var(--text); cursor: default; }
.separator { color: var(--text-muted); }

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
}

.folder-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.folder-card:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
}

.folder-card.primary { border-left: 4px solid var(--primary); }
.folder-card.secondary { border-left: 4px solid #f59e0b; }

.folder-icon { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; }
.folder-name { display: block; font-weight: 600; margin-bottom: 0.25rem; }
.folder-count { font-size: 0.75rem; color: var(--text-muted); }

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.list-header h3 { margin: 0; }

.subject-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
  margin-bottom: 0.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s;
}

.subject-card:hover { border-color: var(--primary); }
.subject-code { font-size: 0.75rem; color: var(--text-muted); margin-left: 0.5rem; }
.subject-meta { display: flex; align-items: center; gap: 1rem; }
.subject-meta span { font-size: 0.75rem; color: var(--text-muted); }

.delete-btn {
  background: none;
  border: none;
  cursor: pointer;
  opacity: 0.5;
  transition: opacity 0.2s;
}

.delete-btn:hover { opacity: 1; }

.topic-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  margin-bottom: 1rem;
  overflow: hidden;
}

.topic-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-hover);
}

.topic-code {
  background: var(--primary);
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.topic-name { flex: 1; font-weight: 500; }

.subtopics { padding: 0.5rem; }

.subtopic-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  border-bottom: 1px solid var(--border);
}

.subtopic-row:last-child { border-bottom: none; }

.st-code {
  font-size: 0.75rem;
  color: var(--text-muted);
  min-width: 50px;
}

.st-name { flex: 1; font-size: 0.875rem; }

.tp-badges { display: flex; gap: 0.25rem; }

.tp-badge {
  background: var(--bg-hover);
  color: var(--text-muted);
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.625rem;
  font-weight: 600;
  cursor: default;
}

.tp-badge.filled {
  background: var(--success);
  color: white;
}

/* Title Group */
.title-group {
  margin-bottom: 2rem;
}

.title-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: linear-gradient(135deg, var(--primary-dark), var(--primary));
  border-radius: 0.75rem 0.75rem 0 0;
  margin-bottom: 0;
}

.title-badge {
  font-size: 1.5rem;
}

.title-name {
  font-size: 1.125rem;
  font-weight: 700;
  color: white;
  flex: 1;
}

.theme-badge {
  background: rgba(255,255,255,0.2);
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: rgba(255,255,255,0.9);
}

.title-group .topic-card {
  border-radius: 0;
  margin-bottom: 0;
  border-top: none;
}

.title-group .topic-card:last-child {
  border-radius: 0 0 0.75rem 0.75rem;
}

.empty {
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
  padding: 1rem;
}

.modal {
  background: var(--bg-card);
  border-radius: 1rem;
  padding: 1.5rem;
  width: 100%;
  max-width: 400px;
  border: 1px solid var(--border);
}

.modal.large { max-width: 600px; }
.modal.modal-lg { 
  max-width: 900px; 
  max-height: 85vh;
  overflow-y: auto;
}
.modal h3 { margin: 0 0 1rem; }

/* Preview Summary */
.preview-summary {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.summary-card {
  flex: 1;
  text-align: center;
  background: var(--bg-hover);
  padding: 1rem;
  border-radius: 0.5rem;
}

.summary-card.error {
  background: rgba(239, 68, 68, 0.2);
  color: var(--danger);
}

.summary-number {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary);
}

.summary-card.error .summary-number {
  color: var(--danger);
}

.summary-label {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.preview-errors {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid var(--danger);
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
}

.preview-errors h4 {
  margin: 0 0 0.5rem;
  color: var(--danger);
}

.preview-errors ul {
  margin: 0;
  padding-left: 1.5rem;
}

.preview-table-wrapper {
  margin-bottom: 1rem;
}

.preview-table-wrapper h4 {
  margin: 0 0 0.5rem;
  color: var(--text);
}

.preview-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.75rem;
}

.preview-table th,
.preview-table td {
  padding: 0.5rem;
  text-align: left;
  border: 1px solid var(--border);
}

.preview-table th {
  background: var(--bg-hover);
  font-weight: 600;
}

.preview-table tbody tr:hover {
  background: var(--bg-hover);
}

.form-row {
  display: flex;
  gap: 1rem;
}

.flex-2 { flex: 2; }

.tp-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  margin: 1rem 0;
}

.tp-grid textarea {
  font-size: 0.75rem;
  resize: vertical;
}

.modal-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.modal-actions .btn { flex: 1; }

.header-buttons {
  display: flex;
  gap: 0.5rem;
}

.import-info {
  background: var(--bg-hover);
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.import-info code {
  background: var(--bg-card);
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
}

.import-info p {
  margin: 0 0 0.75rem;
  color: var(--text-muted);
}

.import-result {
  padding: 0.75rem;
  border-radius: 0.5rem;
  margin: 1rem 0;
  font-size: 0.875rem;
}

.import-result.success {
  background: rgba(34, 197, 94, 0.1);
  color: var(--success);
  border: 1px solid var(--success);
}

.import-result.error {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid #ef4444;
}

@media (max-width: 768px) {
  .grid { grid-template-columns: repeat(2, 1fr); }
  .tp-grid { grid-template-columns: 1fr; }
  .header-buttons { flex-direction: column; }
}

.topic-actions, .st-actions {
  display: flex;
  gap: 0.25rem;
  align-items: center;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 0.25rem;
  opacity: 0.5;
  transition: all 0.2s;
}

.btn-icon:hover {
  opacity: 1;
  background: var(--bg-hover);
}

.btn-icon.danger:hover {
  background: rgba(239, 68, 68, 0.1);
}
</style>
