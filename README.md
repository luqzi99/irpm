# iRPM  
**Interactive Rekod Perkembangan Murid**

Sistem SaaS untuk guru Malaysia merekod, menjejak dan melaporkan **Tahap Penguasaan (TP)** murid secara **berperingkat, adil dan boleh diaudit**, selari dengan DSKP & amalan PdPR sebenar.

---

## 📌 Ringkasan Projek

**iRPM** dibina untuk menyelesaikan masalah sebenar guru:
- Tidak sempat merekod penilaian PdPR
- Penilaian hujung tahun dibuat secara anggaran
- Tiada bukti apabila pentadbir / PPD datang menilai

iRPM menyediakan sistem **rekod pantas**, **visual jelas**, dan **data selamat** untuk kegunaan harian guru.

---

## 🎯 Objektif Sistem

- Membolehkan guru merekod TP murid (TP1–TP6) **setiap PdPR**
- Penilaian dibuat **ikut subtopik**
- Sistem mengira:
  - Purata TP subtopik
  - Purata TP topik
  - TP keseluruhan subjek
- Menyediakan laporan perkembangan murid yang boleh diaudit

---

## 👥 Pengguna Sistem

### 1. Guru (Pengguna Utama)
- Login & akses kelas
- Rekod TP murid
- Lihat perkembangan murid
- Jana laporan

### 2. Admin
- Urus sekolah
- Urus guru
- Konfigurasi subjek, topik & subtopik (DSKP)
- Upload & simpan dokumen rasmi

---

## 🧠 Prinsip Reka Bentuk Sistem

- **Append-only data** (tiada overwrite penilaian)
- **Audit-friendly**
- **Mobile-first**
- **Minimum klik**
- **Scalable ke jutaan murid**
- **Data murid dilindungi (PDPA-aware)**

---

## 🏗️ Seni Bina Sistem (High Level)

```
Client (Browser / Mobile)
        ↓
    Vue.js SPA
        ↓
    Laravel API
        ↓
PostgreSQL + Redis
        ↓
Object Storage (PDF, report)
```

---

## 🧰 Tech Stack

### Backend
- Laravel (API-based)
- PHP 8+
- Laravel Queue

### Frontend
- Vue.js (SPA)
- Mobile-first UI

### Database
- PostgreSQL (primary)
- Redis (cache & aggregation)

### Infra
- Docker & Docker Compose
- Nginx
- VPS / Cloud

---

## 📊 Data Model (Ringkas)

### users
```sql
id
name
email
password
role (admin, guru)
school_id
```

### students
```sql
id
ic_hash (indexed)
encrypted_ic
encrypted_name
school_id
```

### subjects
```sql
id
name
level
```

### topics
```sql
id
subject_id
name
year
```

### subtopics
```sql
id
topic_id
name
sequence
```

### classes
```sql
id
school_id
name
year
```

### class_students
```sql
class_id
student_id
```

### teaching_assignments
```sql
id
teacher_id
class_id
subject_id
year
```

### evaluations (APPEND-ONLY)
```sql
id
student_id
teacher_id
subject_id
topic_id
subtopic_id
tp (1–6)
date
created_at
```

---

## 🔐 Keselamatan & Encryption

### Data Sensitif
- IC murid
- Nama murid

### Strategi
- IC & nama → AES-256 encrypted
- Hash IC (SHA-256) untuk lookup
- Role-based access (Laravel Policy)
- Audit log untuk semua akses sensitif

```php
$encrypted = Crypt::encryptString($value);
$hash = hash('sha256', $value);
```

---

## 🔎 Lookup Murid (IC Strategy)

**Flow:**
```
Input IC
    → Hash IC
    → Cari dalam DB
    → Jika wujud → guna
    → Jika tiada → create murid baru
```

---

## 🧠 Aggregation Strategy

- Data penilaian disimpan mentah (append-only)
- Purata TP dikira melalui:
  - Background job
  - Redis cache
- Elakkan kiraan berat setiap request

---

## 🗄️ Database Scaling

- Table `evaluations` dipartition ikut tahun
- Index pada:
  - `student_id`
  - `subject_id`
  - `subtopic_id`
- Read replica untuk skala besar

---

## 🧾 Audit Trail

Semua aktiviti penting direkod:
- Rekod penilaian
- Akses data murid
- Export laporan

### audit_logs
```sql
id
user_id
action
entity_type
entity_id
created_at
```

---

## 📅 Teaching Schedule & Reminder

Guru boleh set jadual mengajar:
- Hari
- Masa
- Kelas
- Subjek

Sistem boleh:
- Papar "Today Class"
- Reminder untuk rekod TP

---

## 📱 UX Flow Guru (PdPR)

1. Login
2. Pilih kelas hari ini
3. Pilih subtopik
4. Senarai murid dipaparkan
5. Tap TP (1–6)
6. Simpan → siap

> ⏱️ **Sasaran:** < 2 minit / kelas

---

## 📈 Capacity Planning (Ringkas)

| Murid | Rekod / Tahun | DB Size |
|-------|---------------|---------|
| 10k   | ~2.4M         | ~500MB  |
| 100k  | ~24M          | ~5GB    |
| 1M    | ~240M         | ~50GB   |

PostgreSQL + partition mampu handle skala ini.

---

## 💰 Business Model

### Pricing Cadangan
- RM30 / guru / bulan
- Free trial 30 hari

### Target Awal
- Sekolah swasta
- Sekolah agama
- Tadika / pusat pendidikan

---

## 🚀 Deployment (MVP)

### Requirement
- VPS (Ubuntu 22.04)
- Docker
- Docker Compose

### Services
- Laravel API
- Vue SPA
- PostgreSQL
- Redis
- Nginx

---

## 🔁 CI/CD (Ringkas)

- GitHub
- GitHub Actions
- Auto deploy ke VPS

---

## 🛣️ Roadmap

### Phase 1 (MVP)
- Rekod TP
- Progress view
- Laporan asas

### Phase 2
- Reminder automatik
- PDF rasmi
- Dashboard sekolah

### Phase 3
- Analitik lanjutan
- Insight prestasi murid
- Integrasi kementerian (jika perlu)

---

## 🏁 Penutup

iRPM dibina untuk:
- Mengurangkan beban guru
- Menjadikan penilaian lebih adil
- Menyediakan bukti PdPR yang sah

> **Guru fokus mengajar, iRPM urus rekod.**