<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { admin } from '../../api'

const router = useRouter()
const loading = ref(true)
const users = ref([])
const pagination = ref(null)
const search = ref('')
const filterSubscription = ref('')

// Subscription modal
const showSubscriptionModal = ref(false)
const selectedUser = ref(null)
const newPlan = ref('free')
const months = ref(1)
const updating = ref(false)

const planLabels = {
  free: 'Percuma',
  basic: 'Asas',
  pro: 'Pro',
}

const planColors = {
  free: '#6b7280',
  basic: '#3b82f6',
  pro: '#f59e0b',
}

onMounted(async () => {
  await loadUsers()
})

async function loadUsers() {
  loading.value = true
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (filterSubscription.value) params.subscription = filterSubscription.value
    
    const data = await admin.getUsers(params)
    users.value = data.data
    pagination.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openSubscriptionModal(user) {
  selectedUser.value = user
  newPlan.value = user.subscription_plan
  months.value = 1
  showSubscriptionModal.value = true
}

async function updateSubscription() {
  updating.value = true
  try {
    await admin.updateSubscription(selectedUser.value.id, newPlan.value, newPlan.value !== 'free' ? months.value : null)
    
    // Update local state
    const idx = users.value.findIndex(u => u.id === selectedUser.value.id)
    if (idx !== -1) {
      users.value[idx].subscription_plan = newPlan.value
    }
    
    showSubscriptionModal.value = false
    alert('Langganan dikemas kini!')
  } catch (e) {
    alert(e.message || 'Gagal mengemas kini langganan')
  } finally {
    updating.value = false
  }
}

async function toggleActive(user) {
  if (!confirm(`${user.is_active ? 'Nyahaktifkan' : 'Aktifkan'} ${user.name}?`)) return
  
  try {
    const result = await admin.toggleActive(user.id)
    user.is_active = result.is_active
  } catch (e) {
    alert(e.message || 'Gagal')
  }
}

function goBack() {
  router.push('/admin')
}

function handleSearch() {
  loadUsers()
}
</script>

<template>
  <div class="users-page">
    <header class="header">
      <button @click="goBack" class="btn btn-secondary">← Kembali</button>
      <h1>👥 Pengurusan Pengguna</h1>
      <div></div>
    </header>

    <main class="container">
      <!-- Filters -->
      <div class="filters">
        <input 
          v-model="search" 
          @keyup.enter="handleSearch"
          placeholder="🔍 Cari nama atau email..."
          class="search-input"
        />
        <select v-model="filterSubscription" @change="loadUsers">
          <option value="">Semua Langganan</option>
          <option value="free">Percuma</option>
          <option value="basic">Asas</option>
          <option value="pro">Pro</option>
        </select>
        <button @click="loadUsers" class="btn btn-primary">Cari</button>
      </div>

      <div v-if="loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else class="fade-in">
        <!-- Users Table -->
        <div class="table-container">
          <table class="users-table">
            <thead>
              <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Langganan</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Tindakan</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id" :class="{ inactive: !user.is_active }">
                <td class="user-name">
                  <span>{{ user.name }}</span>
                  <span v-if="user.role === 'admin'" class="admin-tag">Admin</span>
                </td>
                <td>{{ user.email }}</td>
                <td>
                  <span 
                    class="plan-badge" 
                    :style="{ background: planColors[user.subscription_plan] }"
                  >
                    {{ planLabels[user.subscription_plan] }}
                  </span>
                </td>
                <td>{{ user.classes_count }}</td>
                <td>
                  <span class="status" :class="user.is_active ? 'active' : 'inactive'">
                    {{ user.is_active ? '✅ Aktif' : '❌ Tidak Aktif' }}
                  </span>
                </td>
                <td class="actions">
                  <button 
                    @click="openSubscriptionModal(user)" 
                    class="btn btn-sm btn-secondary"
                    title="Tukar Langganan"
                  >
                    📝
                  </button>
                  <button 
                    @click="toggleActive(user)" 
                    class="btn btn-sm"
                    :class="user.is_active ? 'btn-danger' : 'btn-success'"
                    :title="user.is_active ? 'Nyahaktif' : 'Aktifkan'"
                  >
                    {{ user.is_active ? '🚫' : '✅' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="!users.length" class="empty-state">
          <p>Tiada pengguna dijumpai.</p>
        </div>
      </div>

      <!-- Subscription Modal -->
      <div v-if="showSubscriptionModal" class="modal-overlay" @click.self="showSubscriptionModal = false">
        <div class="modal fade-in">
          <h3>📝 Tukar Langganan</h3>
          <p class="modal-user">{{ selectedUser?.name }}</p>
          
          <form @submit.prevent="updateSubscription">
            <div class="form-group">
              <label>Pelan</label>
              <div class="plan-options">
                <label class="plan-option" :class="{ selected: newPlan === 'free' }">
                  <input type="radio" v-model="newPlan" value="free" />
                  <span class="plan-name">Percuma</span>
                  <span class="plan-price">RM0</span>
                </label>
                <label class="plan-option" :class="{ selected: newPlan === 'basic' }">
                  <input type="radio" v-model="newPlan" value="basic" />
                  <span class="plan-name">Asas</span>
                  <span class="plan-price">RM15/bln</span>
                </label>
                <label class="plan-option" :class="{ selected: newPlan === 'pro' }">
                  <input type="radio" v-model="newPlan" value="pro" />
                  <span class="plan-name">Pro</span>
                  <span class="plan-price">RM30/bln</span>
                </label>
              </div>
            </div>
            
            <div class="form-group" v-if="newPlan !== 'free'">
              <label>Tempoh (bulan)</label>
              <select v-model="months">
                <option :value="1">1 bulan</option>
                <option :value="3">3 bulan</option>
                <option :value="6">6 bulan</option>
                <option :value="12">12 bulan</option>
              </select>
            </div>
            
            <div class="modal-actions">
              <button type="button" @click="showSubscriptionModal = false" class="btn btn-secondary">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="updating">
                {{ updating ? 'Menyimpan...' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.users-page {
  min-height: 100vh;
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.table-container {
  background: var(--bg-card);
  border-radius: 1rem;
  overflow: hidden;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th,
.users-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border);
}

.users-table th {
  background: var(--bg-hover);
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--text-muted);
}

.users-table tr.inactive {
  opacity: 0.5;
}

.user-name {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.admin-tag {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  padding: 0.125rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.625rem;
  text-transform: uppercase;
}

.plan-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.status.active {
  color: var(--success);
}

.status.inactive {
  color: var(--danger);
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-sm {
  padding: 0.5rem;
  font-size: 0.875rem;
}

.btn-danger {
  background: var(--danger);
  color: white;
}

.btn-success {
  background: var(--success);
  color: white;
}

.empty-state {
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
  max-width: 450px;
  border: 1px solid var(--border);
}

.modal h3 {
  margin-bottom: 0.5rem;
}

.modal-user {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
}

.plan-options {
  display: flex;
  gap: 0.5rem;
}

.plan-option {
  flex: 1;
  padding: 1rem;
  background: var(--bg-hover);
  border: 2px solid var(--border);
  border-radius: 0.5rem;
  cursor: pointer;
  text-align: center;
  transition: all 0.2s;
}

.plan-option:hover {
  border-color: var(--primary);
}

.plan-option.selected {
  border-color: var(--primary);
  background: rgba(99, 102, 241, 0.1);
}

.plan-option input {
  display: none;
}

.plan-name {
  display: block;
  font-weight: 600;
}

.plan-price {
  display: block;
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 0.25rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.modal-actions .btn {
  flex: 1;
}

@media (max-width: 768px) {
  .users-table {
    font-size: 0.875rem;
  }
  
  .users-table th,
  .users-table td {
    padding: 0.75rem 0.5rem;
  }
  
  .plan-options {
    flex-direction: column;
  }
}
</style>
