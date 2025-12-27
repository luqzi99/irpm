<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { admin, auth } from '../../api'

const router = useRouter()
const loading = ref(true)
const stats = ref(null)
const user = ref(auth.getUser())

onMounted(async () => {
  try {
    stats.value = await admin.getStats()
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

// Calculate max for growth chart
const maxGrowth = computed(() => {
  if (!stats.value?.monthly_growth) return 10
  return Math.max(...stats.value.monthly_growth.map(m => Math.max(m.users, m.evaluations))) || 10
})
</script>

<template>
  <div class="admin-page">
    <header class="header">
      <h1>👑 Panel Pentadbir</h1>
      <div class="header-right">
        <span class="admin-badge">{{ user?.name }}</span>
        <button @click="handleLogout" class="btn btn-secondary btn-sm">🚪 Keluar</button>
      </div>
    </header>

    <main class="container">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else class="fade-in">
        <!-- Main Stats Cards -->
        <div class="stats-grid" v-if="stats">
          <div class="stat-card">
            <span class="stat-icon">👥</span>
            <div class="stat-info">
              <span class="stat-value">{{ stats.total_users }}</span>
              <span class="stat-label">Jumlah Pengguna</span>
            </div>
          </div>
          <div class="stat-card">
            <span class="stat-icon">✅</span>
            <div class="stat-info">
              <span class="stat-value">{{ stats.verified_users || stats.active_users }}</span>
              <span class="stat-label">Disahkan</span>
            </div>
          </div>
          <div class="stat-card highlight">
            <span class="stat-icon">🆕</span>
            <div class="stat-info">
              <span class="stat-value">{{ stats.new_this_week || 0 }}</span>
              <span class="stat-label">Baru Minggu Ini</span>
            </div>
          </div>
          <div class="stat-card">
            <span class="stat-icon">📊</span>
            <div class="stat-info">
              <span class="stat-value">{{ stats.total_evaluations || 0 }}</span>
              <span class="stat-label">Jumlah Penilaian</span>
            </div>
          </div>
        </div>

        <!-- Activity Stats -->
        <section class="section" v-if="stats">
          <h3>📈 Aktiviti</h3>
          <div class="activity-grid">
            <div class="activity-card">
              <div class="activity-value">{{ stats.evaluations_this_week || 0 }}</div>
              <div class="activity-label">Penilaian Minggu Ini</div>
            </div>
            <div class="activity-card">
              <div class="activity-value">{{ stats.evaluations_this_month || 0 }}</div>
              <div class="activity-label">Penilaian Bulan Ini</div>
            </div>
            <div class="activity-card">
              <div class="activity-value">{{ stats.total_classes || 0 }}</div>
              <div class="activity-label">Jumlah Kelas</div>
            </div>
            <div class="activity-card">
              <div class="activity-value">{{ stats.total_students || 0 }}</div>
              <div class="activity-label">Jumlah Murid</div>
            </div>
          </div>
        </section>

        <!-- Monthly Growth Chart -->
        <section class="section" v-if="stats?.monthly_growth">
          <h3>📊 Pertumbuhan 6 Bulan</h3>
          <div class="chart-container">
            <div class="chart-bars">
              <div v-for="month in stats.monthly_growth" :key="month.month" class="chart-column">
                <div class="bar-group">
                  <div class="bar users" :style="{ height: (month.users / maxGrowth * 100) + '%' }" :title="'Pengguna: ' + month.users">
                    <span class="bar-value" v-if="month.users > 0">{{ month.users }}</span>
                  </div>
                  <div class="bar evaluations" :style="{ height: (month.evaluations / maxGrowth * 100) + '%' }" :title="'Penilaian: ' + month.evaluations">
                    <span class="bar-value" v-if="month.evaluations > 0">{{ month.evaluations }}</span>
                  </div>
                </div>
                <span class="chart-label">{{ month.month }}</span>
              </div>
            </div>
            <div class="chart-legend">
              <span class="legend-item"><span class="legend-dot users"></span> Pengguna Baru</span>
              <span class="legend-item"><span class="legend-dot evaluations"></span> Penilaian</span>
            </div>
          </div>
        </section>

        <!-- Top Subjects -->
        <section class="section" v-if="stats?.top_subjects?.length">
          <h3>🏆 Subjek Popular</h3>
          <div class="top-list">
            <div v-for="(subj, idx) in stats.top_subjects" :key="idx" class="top-item">
              <span class="top-rank">{{ idx + 1 }}</span>
              <span class="top-name">{{ subj.name }}</span>
              <span class="top-count">{{ subj.count }} penilaian</span>
            </div>
          </div>
        </section>

        <!-- Subscription Breakdown -->
        <section class="section" v-if="stats">
          <h3>💳 Langganan</h3>
          <div class="subscription-grid">
            <div class="sub-card free">
              <span class="sub-label">Percuma</span>
              <span class="sub-count">{{ stats.by_subscription.free }}</span>
            </div>
            <div class="sub-card basic">
              <span class="sub-label">Asas</span>
              <span class="sub-count">{{ stats.by_subscription.basic }}</span>
            </div>
            <div class="sub-card pro">
              <span class="sub-label">Pro</span>
              <span class="sub-count">{{ stats.by_subscription.pro }}</span>
            </div>
          </div>
        </section>

        <!-- Quick Links -->
        <section class="section">
          <h3>⚡ Akses Pantas</h3>
          <div class="quick-links">
            <router-link to="/admin/users" class="quick-link">
              <span class="link-icon">👥</span>
              <span>Pengurusan Pengguna</span>
            </router-link>
            <router-link to="/admin/dskp" class="quick-link">
              <span class="link-icon">📚</span>
              <span>Pengurusan DSKP</span>
            </router-link>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<style scoped>
.admin-page {
  min-height: 100vh;
}

.admin-badge {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-card.highlight {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border: none;
}

.stat-icon { font-size: 2rem; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 1.5rem; font-weight: 700; }
.stat-label { font-size: 0.75rem; color: var(--text-muted); }
.stat-card.highlight .stat-label { color: rgba(255,255,255,0.8); }

/* Activity Grid */
.activity-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.activity-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
  text-align: center;
}

.activity-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary);
}

.activity-label {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 0.25rem;
}

/* Chart */
.chart-container {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 1.5rem;
}

.chart-bars {
  display: flex;
  gap: 1rem;
  height: 150px;
  align-items: flex-end;
}

.chart-column {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
}

.bar-group {
  flex: 1;
  display: flex;
  gap: 4px;
  align-items: flex-end;
  width: 100%;
}

.bar {
  flex: 1;
  border-radius: 4px 4px 0 0;
  min-height: 4px;
  position: relative;
  transition: height 0.3s;
}

.bar.users { background: var(--primary); }
.bar.evaluations { background: var(--success); }

.bar-value {
  position: absolute;
  top: -20px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 0.625rem;
  color: var(--text-muted);
}

.chart-label {
  font-size: 0.625rem;
  color: var(--text-muted);
  margin-top: 0.5rem;
  text-align: center;
}

.chart-legend {
  display: flex;
  gap: 1.5rem;
  justify-content: center;
  margin-top: 1rem;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: var(--text-muted);
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 3px;
}

.legend-dot.users { background: var(--primary); }
.legend-dot.evaluations { background: var(--success); }

/* Top List */
.top-list {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  overflow: hidden;
}

.top-item {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid var(--border);
}

.top-item:last-child { border-bottom: none; }

.top-rank {
  width: 28px;
  height: 28px;
  background: var(--primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  margin-right: 1rem;
}

.top-name { flex: 1; }
.top-count { font-size: 0.875rem; color: var(--text-muted); }

/* Sections */
.section { margin-top: 2rem; }
.section h3 { margin-bottom: 1rem; font-size: 1rem; color: var(--text-muted); }

/* Subscription */
.subscription-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.sub-card {
  padding: 1.25rem;
  border-radius: 1rem;
  text-align: center;
}

.sub-card.free { background: var(--bg-card); border: 1px solid var(--border); }
.sub-card.basic { background: linear-gradient(135deg, #3b82f6, #6366f1); }
.sub-card.pro { background: linear-gradient(135deg, #f59e0b, #ef4444); }

.sub-label { display: block; font-size: 0.875rem; opacity: 0.8; }
.sub-count { display: block; font-size: 1.75rem; font-weight: 700; margin-top: 0.25rem; }

/* Quick Links */
.quick-links {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.quick-link {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 1rem;
  text-decoration: none;
  color: var(--text);
  transition: all 0.2s;
}

.quick-link:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
}

.link-icon { font-size: 1.5rem; }

@media (max-width: 768px) {
  .subscription-grid { grid-template-columns: 1fr; }
  .chart-bars { height: 120px; }
}
</style>
