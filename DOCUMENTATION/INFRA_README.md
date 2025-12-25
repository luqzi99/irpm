# iRPM — Tech Stack & Infrastructure Guide
**From Early Stage to National Scale**

Dokumen ini menerangkan **pemilihan tech stack & infra** untuk iRPM secara berperingkat,  
berdasarkan **real usage guru**, **kos munasabah**, dan **keperluan skala nasional**.

---

## 🎯 Prinsip Asas Infra iRPM

iRPM ialah sistem:
- Data-heavy (jutaan rekod penilaian)
- Write frequent (setiap PdPR)
- Read heavy (laporan & audit)
- Data sensitif (murid & IC)
- Digunakan di sekolah (internet tidak stabil)

Oleh itu infra mesti:
- Stable
- Mudah maintain (small team)
- Scalable tanpa rewrite besar
- Cost-efficient

---

## 🧱 TECH STACK (FINAL)

### Backend
- **Laravel (API-first)**
- PHP 8+

**Sebab pilih Laravel:**
- Ecosystem matang
- Queue, policy, encryption built-in
- Sesuai untuk SaaS jangka panjang
- Senang cari developer

---

### Frontend
- **Vue.js (SPA)**

**Sebab bukan Blade:**
- UX perlu sangat pantas (tap → save)
- Mobile-first
- Reactive UI (TP grid, heatmap)
- Boleh evolve ke PWA / mobile app

> 📌 Blade masih boleh digunakan untuk:
> - Landing page
> - Admin panel ringkas

---

### Database
- **PostgreSQL**

**Sebab pilih PostgreSQL:**
- Kuat untuk data besar & analitik
- Partition table (ikut tahun)
- MVCC untuk read/write serentak
- JSONB untuk config DSKP
- Lebih audit-friendly

> 📌 MySQL sesuai untuk CRUD kecil,  
> iRPM perlukan **data integrity & scalability**

---

### Cache
- **Redis**

Digunakan untuk:
- Cache aggregation TP
- Kurangkan query berat
- Support offline sync logic

---

### Storage
- **Object Storage (S3 compatible)**

Digunakan untuk:
- PDF DSKP
- Laporan murid
- Export data

Pilihan:
- Cloudflare R2
- AWS S3
- Backblaze B2

> ❌ Jangan simpan file dalam DB

---

## 🚀 STAGE 1 — EARLY STAGE (MVP)

### 🎯 Objective
- Launch cepat
- Jimat kos
- Mudah deploy
- Validate dengan guru sebenar

---

### Architecture (Single VPS)

```
         Internet
            ↓
    Nginx (Reverse Proxy)
            ↓
      Docker Compose
      ├─ Laravel API
      ├─ Vue SPA
      ├─ PostgreSQL
      └─ Redis
```

---

### Infra Setup

| Item       | Pilihan           |
|------------|-------------------|
| Server     | 1 VPS             |
| OS         | Ubuntu 22.04      |
| Container  | Docker + Compose  |
| Web Server | Nginx             |

---

### VPS Spec Minimum

| Resource | Spec           |
|----------|----------------|
| CPU      | 2 vCPU         |
| RAM      | 4 GB           |
| Storage  | 80–100 GB SSD  |

> 💰 Anggaran kos: **RM30–50 / bulan**

---

### Kenapa Setup Ini?
- Semua dalam satu server → senang debug
- Docker → senang migrate
- Murah untuk validate idea

---

## 🟡 STAGE 2 — GROWTH (10k–100k Murid)

### Bila?
- Banyak sekolah onboard
- Guru aktif serentak
- DB mula berat

---

### Architecture (Separated Services)

```
    Cloudflare (DNS + WAF)
            ↓
    Load Balancer / Nginx
            ↓
    Laravel API (1–2 instance)
            ↓
    PostgreSQL (Dedicated)
            ↓
      Redis (Dedicated)
```

---

### Infra Spec (Anggaran)

| Component  | Spec         |
|------------|--------------|
| App Server | 4 vCPU / 8GB |
| DB Server  | 4 vCPU / 8GB |
| Redis      | 4GB          |
| Storage    | 300GB        |

> 💰 Anggaran kos: **RM150–250 / bulan**

---

### Database Strategy
- `evaluations` table partition by year
- Index pada:
  - `student_id`
  - `subject_id`
  - `subtopic_id`
- Aggregation melalui background job

---

### Kenapa Separate DB?
- Elak DB jadi bottleneck
- App boleh scale tanpa sentuh data
- Lebih selamat untuk data murid

---

## 🔴 STAGE 3 — PRODUCTION NASIONAL (1M Murid)

### Target
- Digunakan secara meluas
- Banyak guru serentak
- Audit & reporting berat

---

### Architecture (High Availability)

```
    Cloudflare (DNS, WAF, Cache)
              ↓
        Load Balancer
              ↓
    Laravel API (Auto-scale)
              ↓
      PostgreSQL Primary
       ├─ Read Replica
              ↓
        Redis Cluster
```

---

### Infra Spec (Anggaran)

| Component   | Spec                   |
|-------------|------------------------|
| App Servers | 2–3 × (8 vCPU / 16GB)  |
| DB Primary  | 8 vCPU / 32GB          |
| DB Replica  | 8 vCPU / 16GB          |
| Redis       | 8–16GB                 |
| Storage     | 1TB+                   |

> 💰 Anggaran kos: **RM800 – 1,200 / bulan**

> 📌 Masih rendah untuk skala nasional

---

## 🔐 SECURITY & DATA PROTECTION

### Encryption
- IC & nama murid → AES-256 encrypted
- Hash IC (SHA-256) untuk lookup
- `.env` encryption key
- Key rotation (annual)

---

### Access Control
- Role-based (guru, admin, super admin)
- Guru hanya boleh akses kelas sendiri
- Policy enforced di backend

---

### Audit Log
Semua akses sensitif direkod:
- View murid
- Rekod TP
- Export laporan

---

## 🔁 BACKUP & DISASTER RECOVERY

**Minimum:**
- Daily DB backup
- Offsite encrypted storage
- 7–14 hari retention

**Advanced:**
- PostgreSQL WAL archiving
- Point-in-time recovery

---

## 🔄 CI/CD STRATEGY

Simple & effective:
- GitHub repository
- GitHub Actions
- Auto deploy ke server

**Flow:**
```
git push → test → build → deploy
```

---

## 🧠 RUMUSAN TEKNIKAL

### Kenapa Stack Ini Dipilih?

| Komponen   | Sebab                        |
|------------|------------------------------|
| Laravel    | Stabil, matang, SaaS-ready   |
| Vue        | UX pantas, mobile-first      |
| PostgreSQL | Data besar, audit, analitik  |
| Redis      | Performance & cache          |
| Docker     | Portability & scaling        |

---

## 🏁 Penutup

Infra iRPM direka supaya:
- Start kecil tanpa pembaziran
- Scale tanpa rewrite
- Selamat untuk data murid
- Realistik untuk solo / small team

> **Build simple. Scale smart. Protect data.**