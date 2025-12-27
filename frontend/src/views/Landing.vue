<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

function goToLogin() {
  router.push('/login')
}

function goToRegister() {
  router.push('/login?register=true')
}
</script>

<template>
  <div class="landing-page">
    <!-- SECTION 1: HERO -->
    <header class="hero-section">
      <nav class="nav-bar">
        <div class="logo">
          <span class="logo-icon">📊</span>
          <span class="logo-text">iRPM</span>
        </div>
        <div class="nav-actions">
          <button @click="goToLogin" class="btn btn-ghost">Log Masuk</button>
          <button @click="goToRegister" class="btn btn-primary">Cuba Percuma</button>
        </div>
      </nav>
      
      <div class="hero-content">
        <h1 class="seo-h1">iRPM – Sistem Rekod Perkembangan Murid & PBD Untuk Guru Malaysia</h1>
        
        <div class="hero-headline">
          <span class="headline-main">Rekod Tahap Penguasaan Murid</span>
          <span class="headline-sub">Tanpa Excel. Tanpa Tekanan.</span>
        </div>
        
        <p class="hero-description">
          Sistem digital untuk guru merekod <strong>Pentaksiran Bilik Darjah (PBD)</strong> dan 
          <strong>Tahap Penguasaan (TP1–TP6)</strong> secara pantas, selari dengan DSKP, dan sedia untuk audit.
        </p>
        
        <div class="hero-benefits">
          <span>👉 Rekod lebih cepat</span>
          <span>👉 Penilaian lebih adil</span>
          <span>👉 Bukti PBD sentiasa tersedia</span>
        </div>
        
        <div class="hero-cta">
          <button @click="goToRegister" class="btn btn-primary btn-lg">
            🚀 Cuba Percuma 30 Hari
          </button>
          <a href="#cara-guna" class="btn btn-ghost btn-lg">
            📖 Lihat Cara Berfungsi
          </a>
        </div>
      </div>
      
      <div class="hero-visual">
        <div class="app-preview">
          <div class="preview-header">
            <span class="preview-dot red"></span>
            <span class="preview-dot yellow"></span>
            <span class="preview-dot green"></span>
          </div>
          <div class="preview-content">
            <div class="preview-sidebar">
              <div class="preview-menu-item active">📊 Dashboard</div>
              <div class="preview-menu-item">📝 Input TP</div>
              <div class="preview-menu-item">📈 Progress</div>
            </div>
            <div class="preview-main">
              <div class="preview-card">
                <div class="tp-grid">
                  <div class="tp-badge tp1">TP1</div>
                  <div class="tp-badge tp2">TP2</div>
                  <div class="tp-badge tp3">TP3</div>
                  <div class="tp-badge tp4">TP4</div>
                  <div class="tp-badge tp5">TP5</div>
                  <div class="tp-badge tp6">TP6</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- SECTION 2: PAIN POINTS -->
    <section class="pain-section">
      <div class="container">
        <h2>Masalah Rekod PBD Yang Guru Hadapi Hari Ini</h2>
        <p class="section-intro">Ramai guru menghadapi cabaran yang sama:</p>
        
        <div class="pain-grid">
          <div class="pain-card">
            <span class="pain-icon">⏰</span>
            <p>Murid ramai, masa PdP terhad</p>
          </div>
          <div class="pain-card">
            <span class="pain-icon">📅</span>
            <p>Rekod PBD dibuat lewat atau ditangguhkan</p>
          </div>
          <div class="pain-card">
            <span class="pain-icon">📊</span>
            <p>Excel perlu isi satu per satu untuk setiap murid</p>
          </div>
          <div class="pain-card">
            <span class="pain-icon">🔍</span>
            <p>Sukar cari semula rekod bila diminta pentadbir</p>
          </div>
          <div class="pain-card">
            <span class="pain-icon">🎯</span>
            <p>Penilaian hujung tahun dibuat berdasarkan anggaran</p>
          </div>
        </div>
        
        <p class="pain-conclusion">
          <strong>Masalahnya bukan pada guru.</strong><br>
          Masalahnya pada cara rekod yang tidak praktikal.
        </p>
      </div>
    </section>

    <!-- SECTION 3: SOLUTION -->
    <section id="cara-guna" class="solution-section">
      <div class="container">
        <h2>iRPM – Sistem PBD Digital Dibina Ikut Cara Guru Bekerja</h2>
        <p class="section-intro">
          iRPM tidak mengubah cara guru mengajar.<br>
          Ia hanya memudahkan cara guru <strong>merekod dan menilai</strong>.
        </p>
        
        <div class="steps-flow">
          <div class="step-card">
            <div class="step-number">1</div>
            <div class="step-icon">📚</div>
            <h3>Pilih Kelas & Subtopik</h3>
            <p>Berdasarkan DSKP, auto-populate</p>
          </div>
          <div class="step-arrow">→</div>
          <div class="step-card">
            <div class="step-number">2</div>
            <div class="step-icon">👆</div>
            <h3>Tekan TP Murid</h3>
            <p>TP1–TP6, auto save serta-merta</p>
          </div>
          <div class="step-arrow">→</div>
          <div class="step-card">
            <div class="step-number">3</div>
            <div class="step-icon">📊</div>
            <h3>Sistem Jana Laporan</h3>
            <p>Ulasan, analitik & Excel automatik</p>
          </div>
        </div>
        
        <p class="time-highlight">
          ⏱️ <strong>Kurang 2 minit</strong> untuk satu kelas
        </p>
      </div>
    </section>

    <!-- SECTION 4: FEATURES -->
    <section class="features-section">
      <div class="container">
        <h2>Ciri Lengkap iRPM Untuk Rekod PBD Harian</h2>
        
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">🚀</div>
            <h3>Rekod TP Pantas</h3>
            <ul>
              <li>Satu sentuhan untuk satu murid</li>
              <li>Batch input untuk satu kelas</li>
              <li>Auto ikut jadual mengajar</li>
              <li>Tiada borang panjang</li>
            </ul>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">🧠</div>
            <h3>Ulasan Automatik</h3>
            <ul>
              <li>Ayat ulasan dijana automatik</li>
              <li>Berdasarkan Tahap Penguasaan</li>
              <li>Boleh edit jika perlu</li>
              <li>Menjimatkan masa laporan</li>
            </ul>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Analitik & Progress</h3>
            <ul>
              <li>Purata TP murid</li>
              <li>Peratus murid mencapai MTM</li>
              <li>Senarai murid perlu intervensi</li>
              <li>Progress mengikut masa</li>
            </ul>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">🧾</div>
            <h3>Laporan Excel Lengkap</h3>
            <ul>
              <li>Laporan TP per subtopik</li>
              <li>Analisa kelas automatik</li>
              <li>Graf Pie & Bar</li>
              <li>Sedia untuk audit</li>
            </ul>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">🔐</div>
            <h3>Data Selamat & Audit</h3>
            <ul>
              <li>Nama & IC murid dienkripsi</li>
              <li>Rekod disimpan dengan tarikh</li>
              <li>Sejarah tidak boleh diubah</li>
              <li>Sesuai untuk semakan</li>
            </ul>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>Mobile-Friendly</h3>
            <ul>
              <li>Guna di telefon atau laptop</li>
              <li>Installable sebagai app</li>
              <li>Akses bila-bila masa</li>
              <li>Tiada perlu download</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 5: SCREENSHOTS -->
    <section class="screenshots-section">
      <div class="container">
        <h2>Antara Muka Yang Mudah Digunakan</h2>
        <p class="section-intro">Tak perlu mahir IT. Direka untuk guru.</p>
        
        <div class="screenshot-gallery">
          <div class="screenshot-card">
            <div class="screenshot-placeholder dashboard">
              <span class="placeholder-icon">📊</span>
              <span class="placeholder-text">Dashboard</span>
            </div>
            <p>Paparan utama ringkas dengan kelas hari ini</p>
          </div>
          <div class="screenshot-card">
            <div class="screenshot-placeholder input">
              <span class="placeholder-icon">📝</span>
              <span class="placeholder-text">Input TP</span>
            </div>
            <p>Input TP pantas dengan satu sentuhan</p>
          </div>
          <div class="screenshot-card">
            <div class="screenshot-placeholder report">
              <span class="placeholder-icon">📈</span>
              <span class="placeholder-text">Laporan</span>
            </div>
            <p>Laporan Excel lengkap dengan graf</p>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 6: TARGET AUDIENCE -->
    <section class="audience-section">
      <div class="container">
        <h2>Untuk Siapa iRPM?</h2>
        
        <div class="audience-grid">
          <div class="audience-card">
            <span class="audience-icon">🏫</span>
            <h3>Guru Sekolah Rendah</h3>
          </div>
          <div class="audience-card">
            <span class="audience-icon">🎓</span>
            <h3>Guru Sekolah Menengah</h3>
          </div>
          <div class="audience-card">
            <span class="audience-icon">📚</span>
            <h3>Guru Subjek Teras & Elektif</h3>
          </div>
          <div class="audience-card">
            <span class="audience-icon">📊</span>
            <h3>Guru Yang Guna Excel / Manual</h3>
          </div>
        </div>
        
        <div class="audience-notes">
          <span>📱 Boleh digunakan di telefon atau laptop</span>
          <span>📌 Tidak perlu kemahiran teknikal</span>
        </div>
      </div>
    </section>

    <!-- SECTION 7: TRUST -->
    <section class="trust-section">
      <div class="container">
        <h2>Dibina Berdasarkan Format PBD Sebenar</h2>
        
        <div class="trust-content">
          <p class="trust-statement">
            "iRPM dibina berdasarkan struktur sebenar di sekolah — RPT, RPH, dan format PBD yang guru gunakan setiap hari."
          </p>
          
          <div class="trust-points">
            <div class="trust-point">
              <span class="trust-icon">✓</span>
              <span>Selari dengan DSKP terkini</span>
            </div>
            <div class="trust-point">
              <span class="trust-icon">✓</span>
              <span>Format laporan standard PBD</span>
            </div>
            <div class="trust-point">
              <span class="trust-icon">✓</span>
              <span>Sedia untuk audit pentadbir</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION 8: PRICING -->
    <section class="pricing-section">
      <div class="container">
        <h2>Harga Mampu Untuk Guru Individu</h2>
        <p class="section-intro">Kami percaya alat kerja guru perlu <strong>mampu dan berbaloi</strong>.</p>
        
        <div class="pricing-cards">
          <div class="pricing-card free">
            <div class="pricing-badge">Percubaan</div>
            <div class="pricing-price">
              <span class="price-amount">PERCUMA</span>
              <span class="price-period">30 hari</span>
            </div>
            <ul class="pricing-features">
              <li>✅ Akses penuh semua ciri</li>
              <li>✅ Tiada kad kredit diperlukan</li>
              <li>✅ Tiada komitmen</li>
            </ul>
            <button @click="goToRegister" class="btn btn-primary btn-block">
              Cuba Sekarang
            </button>
          </div>
          
          <div class="pricing-card premium">
            <div class="pricing-badge popular">Disyorkan</div>
            <div class="pricing-price">
              <span class="price-amount">RM50</span>
              <span class="price-period">/ tahun</span>
            </div>
            <p class="price-monthly">atau RM5 / bulan</p>
            <ul class="pricing-features">
              <li>✅ Rekod TP tanpa had</li>
              <li>✅ Analitik & laporan</li>
              <li>✅ Ulasan automatik</li>
              <li>✅ Akses di semua peranti</li>
              <li>✅ Laporan Excel lengkap</li>
            </ul>
            <button @click="goToRegister" class="btn btn-primary btn-block">
              Pilih Pelan Ini
            </button>
          </div>
        </div>
        
        <p class="pricing-note">
          <strong>Tiada kontrak. Boleh berhenti bila-bila masa.</strong>
        </p>
      </div>
    </section>

    <!-- SECTION 9: FINAL CTA -->
    <section class="final-cta-section">
      <div class="container">
        <h2>Kurangkan Beban Rekod PBD Hari Ini</h2>
        <p class="cta-subtitle">
          Fokuskan masa kepada pengajaran,<br>
          biar iRPM urus rekod dan laporan.
        </p>
        
        <button @click="goToRegister" class="btn btn-primary btn-xl">
          🚀 Cuba iRPM Secara Percuma Hari Ini
        </button>
        
        <p class="cta-tagline">
          <strong>iRPM</strong> — Rekod PdP. Bukti Penilaian. Tanpa Beban.
        </p>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="landing-footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-brand">
            <span class="logo-icon">📊</span>
            <span class="logo-text">iRPM</span>
            <p>Sistem Rekod Perkembangan Murid</p>
          </div>
          <div class="footer-links">
            <a href="#cara-guna">Cara Guna</a>
            <a @click="goToLogin">Log Masuk</a>
            <a @click="goToRegister">Daftar</a>
          </div>
        </div>
        <div class="footer-bottom">
          <p>© 2025 iRPM. Hak Cipta Terpelihara.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Base Styles */
.landing-page {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #1e293b;
  line-height: 1.6;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
}

.btn-ghost {
  background: transparent;
  color: #1e293b;
  border: 2px solid #e2e8f0;
}

.btn-ghost:hover {
  background: #f1f5f9;
}

.btn-lg {
  padding: 1rem 2rem;
  font-size: 1.1rem;
}

.btn-xl {
  padding: 1.25rem 3rem;
  font-size: 1.25rem;
}

.btn-block {
  width: 100%;
}

/* HERO SECTION */
.hero-section {
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fff7ed 100%);
  min-height: 100vh;
  padding: 1rem 1.5rem 4rem;
}

.nav-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  max-width: 1200px;
  margin: 0 auto;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.logo-icon {
  font-size: 1.5rem;
}

.logo-text {
  font-size: 1.5rem;
  font-weight: 800;
  color: #f59e0b;
}

.nav-actions {
  display: flex;
  gap: 0.75rem;
}

.hero-content {
  max-width: 800px;
  margin: 3rem auto;
  text-align: center;
}

.seo-h1 {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
  margin-bottom: 1rem;
}

.hero-headline {
  margin-bottom: 1.5rem;
}

.headline-main {
  display: block;
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 800;
  color: #1e293b;
  line-height: 1.2;
}

.headline-sub {
  display: block;
  font-size: clamp(1.5rem, 3vw, 2rem);
  font-weight: 600;
  color: #f59e0b;
  margin-top: 0.5rem;
}

.hero-description {
  font-size: 1.125rem;
  color: #475569;
  margin-bottom: 1.5rem;
}

.hero-benefits {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 2rem;
}

.hero-benefits span {
  background: white;
  padding: 0.5rem 1rem;
  border-radius: 2rem;
  font-size: 0.9rem;
  color: #1e293b;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.hero-cta {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.hero-visual {
  max-width: 600px;
  margin: 3rem auto 0;
}

.app-preview {
  background: #1e293b;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
}

.preview-header {
  background: #334155;
  padding: 0.75rem 1rem;
  display: flex;
  gap: 0.5rem;
}

.preview-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.preview-dot.red { background: #ef4444; }
.preview-dot.yellow { background: #f59e0b; }
.preview-dot.green { background: #22c55e; }

.preview-content {
  display: flex;
  min-height: 200px;
}

.preview-sidebar {
  background: #334155;
  padding: 1rem;
  width: 140px;
}

.preview-menu-item {
  padding: 0.5rem 0.75rem;
  color: #94a3b8;
  font-size: 0.8rem;
  border-radius: 0.375rem;
  margin-bottom: 0.25rem;
}

.preview-menu-item.active {
  background: #f59e0b;
  color: white;
}

.preview-main {
  flex: 1;
  padding: 1.5rem;
  background: #1e293b;
}

.preview-card {
  background: #334155;
  border-radius: 0.5rem;
  padding: 1rem;
}

.tp-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.tp-badge {
  padding: 0.5rem;
  border-radius: 0.375rem;
  text-align: center;
  font-weight: 600;
  font-size: 0.875rem;
  color: white;
}

.tp1 { background: #ef4444; }
.tp2 { background: #f97316; }
.tp3 { background: #eab308; }
.tp4 { background: #84cc16; }
.tp5 { background: #22c55e; }
.tp6 { background: #059669; }

/* PAIN SECTION */
.pain-section {
  padding: 5rem 1.5rem;
  background: #fff;
}

.pain-section h2 {
  text-align: center;
  font-size: clamp(1.5rem, 3vw, 2rem);
  margin-bottom: 0.5rem;
}

.section-intro {
  text-align: center;
  color: #64748b;
  margin-bottom: 2rem;
}

.pain-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  max-width: 900px;
  margin: 0 auto 2rem;
}

.pain-card {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.75rem;
  padding: 1.5rem;
  text-align: center;
}

.pain-icon {
  font-size: 2rem;
  display: block;
  margin-bottom: 0.75rem;
}

.pain-card p {
  color: #991b1b;
  font-size: 0.95rem;
  margin: 0;
}

.pain-conclusion {
  text-align: center;
  font-size: 1.125rem;
  color: #1e293b;
  max-width: 500px;
  margin: 0 auto;
}

/* SOLUTION SECTION */
.solution-section {
  padding: 5rem 1.5rem;
  background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
}

.solution-section h2 {
  text-align: center;
  font-size: clamp(1.5rem, 3vw, 2rem);
  margin-bottom: 0.5rem;
}

.steps-flow {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 1rem;
  margin: 2rem 0;
}

.step-card {
  background: white;
  border-radius: 1rem;
  padding: 2rem 1.5rem;
  text-align: center;
  width: 200px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  position: relative;
}

.step-number {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: #22c55e;
  color: white;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  font-weight: 700;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.step-icon {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
}

.step-card h3 {
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.step-card p {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.step-arrow {
  font-size: 2rem;
  color: #22c55e;
  align-self: center;
}

.time-highlight {
  text-align: center;
  font-size: 1.25rem;
  color: #166534;
  background: white;
  display: inline-block;
  padding: 0.75rem 1.5rem;
  border-radius: 2rem;
  margin: 0 auto;
  display: table;
}

/* FEATURES SECTION */
.features-section {
  padding: 5rem 1.5rem;
  background: #fff;
}

.features-section h2 {
  text-align: center;
  font-size: clamp(1.5rem, 3vw, 2rem);
  margin-bottom: 2rem;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  max-width: 1000px;
  margin: 0 auto;
}

.feature-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  padding: 1.5rem;
}

.feature-icon {
  font-size: 2rem;
  margin-bottom: 0.75rem;
}

.feature-card h3 {
  font-size: 1.125rem;
  margin-bottom: 0.75rem;
  color: #1e293b;
}

.feature-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.feature-card li {
  padding: 0.25rem 0;
  color: #64748b;
  font-size: 0.9rem;
}

.feature-card li::before {
  content: "•";
  color: #f59e0b;
  margin-right: 0.5rem;
}

/* SCREENSHOTS SECTION */
.screenshots-section {
  padding: 5rem 1.5rem;
  background: #f8fafc;
}

.screenshots-section h2 {
  text-align: center;
  font-size: clamp(1.5rem, 3vw, 2rem);
  margin-bottom: 0.5rem;
}

.screenshot-gallery {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  max-width: 900px;
  margin: 2rem auto 0;
}

.screenshot-card {
  text-align: center;
}

.screenshot-placeholder {
  background: linear-gradient(135deg, #1e293b, #334155);
  border-radius: 0.75rem;
  height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.75rem;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.placeholder-icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}

.placeholder-text {
  color: #94a3b8;
  font-size: 0.875rem;
}

.screenshot-card p {
  color: #64748b;
  font-size: 0.9rem;
}

/* AUDIENCE SECTION */
.audience-section {
  padding: 5rem 1.5rem;
  background: #fff;
}

.audience-section h2 {
  text-align: center;
  font-size: clamp(1.5rem, 3vw, 2rem);
  margin-bottom: 2rem;
}

.audience-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  max-width: 800px;
  margin: 0 auto 2rem;
}

.audience-card {
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 1rem;
  padding: 1.5rem;
  text-align: center;
}

.audience-icon {
  font-size: 2rem;
  display: block;
  margin-bottom: 0.5rem;
}

.audience-card h3 {
  font-size: 1rem;
  color: #92400e;
  margin: 0;
}

.audience-notes {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 1.5rem;
  color: #64748b;
}

/* TRUST SECTION */
.trust-section {
  padding: 5rem 1.5rem;
  background: linear-gradient(135deg, #1e293b, #334155);
  color: white;
}

.trust-section h2 {
  text-align: center;
  font-size: clamp(1.5rem, 3vw, 2rem);
  margin-bottom: 2rem;
}

.trust-content {
  max-width: 700px;
  margin: 0 auto;
  text-align: center;
}

.trust-statement {
  font-size: 1.25rem;
  font-style: italic;
  color: #fde68a;
  margin-bottom: 2rem;
}

.trust-points {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.trust-point {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.trust-icon {
  background: #22c55e;
  color: white;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
}

/* PRICING SECTION */
.pricing-section {
  padding: 5rem 1.5rem;
  background: #fff;
}

.pricing-section h2 {
  text-align: center;
  font-size: clamp(1.5rem, 3vw, 2rem);
  margin-bottom: 0.5rem;
}

.pricing-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  max-width: 700px;
  margin: 2rem auto;
}

.pricing-card {
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 1rem;
  padding: 2rem;
  text-align: center;
  position: relative;
}

.pricing-card.premium {
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
  border-color: #f59e0b;
}

.pricing-badge {
  display: inline-block;
  background: #64748b;
  color: white;
  padding: 0.25rem 1rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.pricing-badge.popular {
  background: #f59e0b;
}

.pricing-price {
  margin-bottom: 0.5rem;
}

.price-amount {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1e293b;
}

.price-period {
  color: #64748b;
}

.price-monthly {
  color: #64748b;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.pricing-features {
  list-style: none;
  padding: 0;
  margin: 0 0 1.5rem;
  text-align: left;
}

.pricing-features li {
  padding: 0.5rem 0;
  color: #475569;
}

.pricing-note {
  text-align: center;
  color: #64748b;
}

/* FINAL CTA SECTION */
.final-cta-section {
  padding: 5rem 1.5rem;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  text-align: center;
  color: white;
}

.final-cta-section h2 {
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  margin-bottom: 1rem;
}

.cta-subtitle {
  font-size: 1.125rem;
  opacity: 0.9;
  margin-bottom: 2rem;
}

.final-cta-section .btn-primary {
  background: white;
  color: #d97706;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.final-cta-section .btn-primary:hover {
  transform: translateY(-3px);
}

.cta-tagline {
  margin-top: 2rem;
  font-size: 1rem;
  opacity: 0.9;
}

/* FOOTER */
.landing-footer {
  background: #1e293b;
  color: #94a3b8;
  padding: 3rem 1.5rem 1.5rem;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  padding-bottom: 2rem;
  border-bottom: 1px solid #334155;
}

.footer-brand .logo-text {
  color: #f59e0b;
}

.footer-brand p {
  margin: 0.5rem 0 0;
  font-size: 0.875rem;
}

.footer-links {
  display: flex;
  gap: 1.5rem;
}

.footer-links a {
  color: #94a3b8;
  text-decoration: none;
  font-size: 0.9rem;
  cursor: pointer;
}

.footer-links a:hover {
  color: #f59e0b;
}

.footer-bottom {
  text-align: center;
  padding-top: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.footer-bottom p {
  font-size: 0.875rem;
  margin: 0;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .nav-actions .btn-ghost {
    display: none;
  }
  
  .steps-flow {
    flex-direction: column;
    align-items: center;
  }
  
  .step-arrow {
    transform: rotate(90deg);
  }
  
  .hero-visual {
    display: none;
  }
  
  .footer-content {
    flex-direction: column;
    text-align: center;
  }
}
</style>
