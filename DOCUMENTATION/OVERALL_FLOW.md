# iRPM — Overall Flow (A → Z)

Visualisasi keseluruhan flow iRPM dari Admin setup sampai guru isi TP & keluar laporan, selari dengan ERD v2 & real workflow guru.

**3 Layer:**
1. Big Picture (end-to-end)
2. Flow detail ikut peranan (Admin → Guru → Data)
3. Mapping flow → table & sistem

---

## 🧭 1️⃣ BIG PICTURE — iRPM A → Z

```
Admin sediakan struktur → Guru guna harian → Sistem simpan bukti → Laporan sentiasa sedia
```

---

## 🧑‍💻 2️⃣ FLOW A → Z (STEP-BY-STEP)

---

### 🅐 PHASE A — ADMIN SETUP (SEKALI SAJA / SETAHUN)

> 🎯 Dilakukan oleh kau sahaja (Super Admin)

#### A1. Setup Subjek
- Contoh: Sains, Matematik
- Level: Primary / Secondary
- 📦 DB: `subjects`

#### A2. Setup RPT (Tahun & Subjek)
Daripada dokumen RPT sebenar:
- Tahun: 2025
- Tema
- Tajuk
- Standard Kandungan
- 📦 DB: `topics`

#### A3. Setup Standard Pembelajaran
Daripada RPT:
- Kod: 1.1.1
- Huraian
- Susunan (sequence)
- 📦 DB: `subtopics`

> 📌 **DONE** — Struktur akademik negara kini "digitized & locked"

---

### 📤 ADMIN FEATURE: UPLOAD EXCEL DSKP (AUTO IMPORT)

> 🎯 Admin tak perlu key-in RPT & Standard Pembelajaran secara manual

#### Objektif
- 1 Excel → ribuan subtopic
- Update setahun sekali
- KPM tukar DSKP → upload baru → semua guru dapat update instantly

#### Admin UX Flow

**Step � — Pilih Konteks**
- Subjek: Sains
- Tahun: 2025

**Step �🅑 — Upload Excel**
- Drag & drop
- Preview 10 row pertama

**Step 🅒 — Column Mapping (ONE-TIME)**
Admin map:
- Theme → column "Tema"
- Topic → column "Tajuk"
- SP Code → column "Standard Pembelajaran"
- SP Description → column "Huraian"

> 📌 Sistem simpan mapping untuk subjek itu

**Step 🅓 — Validate & Preview**
Sistem check:
- SP Code format (1.1.1)
- Duplicate
- Missing data

Admin nampak:
- Jumlah Topic
- Jumlah Subtopic

**Step 🅔 — Confirm Import**
- Admin klik **Import**
- Sistem: Create/update topics → Create/update subtopics → Log audit

#### Data Flow (Teknikal)
```
Excel Upload
     ↓
Parse Rows
     ↓
Normalize Data
     ↓
Group by Subject + Year
     ↓
Create Topics
     ↓
Create Subtopics
     ↓
Audit Log
```

#### Business Rules
| Rule | Description |
|------|-------------|
| Subject + Year Unique | Satu subjek + tahun = 1 struktur aktif |
| Re-upload | Replace dengan confirmation |
| Data Lama Selamat | Evaluations tidak dipadam |
| Admin Only | Guru ❌ tak boleh upload |

#### 📦 DB Tables
- `dskp_imports` — Track setiap import
- `dskp_import_logs` — Debug & audit

---

### 🅑 PHASE B — GURU ONBOARDING (SEKALI)

> 🎯 Dilakukan oleh guru (user)

#### B1. Guru Daftar Akaun
- Nama
- Email
- Password
- 📦 DB: `users` (role = guru)

#### B2. Guru Cipta Kelas
- Nama kelas: 6 Amanah
- Tahun: 2025
- 📦 DB: `classes`

#### B3. Guru Tambah Murid
**Flow:**
1. Masukkan IC murid
2. Sistem hash IC
3. Check global students
   - Wujud → guna
   - Tak wujud → create
- 📦 DB: `students`, `class_students`

> 📌 Ini hanya sekali per kelas

#### B4. Guru Assign Subjek ke Kelas
Contoh:
- Kelas 6 Amanah
- Subjek Sains
- Tahun 2025
- 📦 DB: `teaching_assignments`

---

### 🅒 PHASE C — HARIAN PdP (CORE VALUE iRPM)

> 🎯 Dilakukan **SETIAP HARI** oleh guru

#### C1. Guru Login
- Landing: **Today Classes**

#### C2. Guru Pilih:
- Kelas
- Subjek
- Subtopik (Standard Pembelajaran)

> 📌 Subtopik datang terus dari RPT  
> 📌 Guru tak key-in apa-apa

#### C3. TP Quick Input Screen (UX TERPENTING)

```
Ali     [1][2][3][4][5][6]
Aisyah  [1][2][3][4][5][6]
Adam    [1][2][3][4][5][6]
```

- 👉 **1 TAP = 1 murid**
- 👉 **Auto-save**
- 👉 **Tiada submit**
- 📦 DB: `evaluations` (append-only)

#### C4. Sistem Buat Kerja Automatik
- Simpan TP
- Trigger background job
- Update cache (Redis)
- 📦 DB/Cache: `evaluations`, `redis:tp_summary`

---

### 🆕 FLOW UX AUTO-ULASAN

#### Step 1 — Pilih Subtopik
Guru pilih:
- Kelas
- Subjek
- Subtopik (Standard Pembelajaran)

#### Step 2 — TP Quick Input (UNCHANGED)
```
Ali     [1][2][3][4][5][6]
Aisyah  [1][2][3][4][5][6]
Adam    [1][2][3][4][5][6]
```
> 📌 1 tap = 1 murid

#### Step 3 — Pilih Kaedah Pentaksiran (GLOBAL)

> 🧠 Satu pilihan untuk satu sesi PdP

```
Kaedah Pentaksiran:
(●) Pemerhatian
(○) Lisan
(○) Bertulis
(○) Projek
```

> 📌 Default = last used  
> 📌 Bukan per murid

#### Step 4 — AUTO ULASAN GENERATED (BACKGROUND)

**Logic:**
```
TP + Kaedah → comment_templates → auto_comment
```

**Contoh:**
- TP4 + Pemerhatian
- Sistem auto set: *"Murid menunjukkan penguasaan baik melalui pemerhatian tanpa bimbingan."*

#### Step 5 — OPTIONAL: Edit Ulasan (RARE)

**UI:**
```
[ Auto-generated comment ]
✏️ Edit (optional)
```

> 📌 90% masa → tak disentuh  
> 📌 Edit hanya bila perlu

---

### 📋 UX RULES (WAJIB IKUT)

#### ✅ Wajib
- Auto-comment silent
- Tiada popup
- Tiada wajib edit
- Edit inline sahaja

#### ❌ Jangan
- Jangan minta cikgu taip panjang
- Jangan buka modal setiap murid
- Jangan paksa simpan comment

---

### 🅓 PHASE D — VISUAL & MONITORING

> 🎯 Guru tak buat apa-apa tambahan

#### D1. Progress Murid
Visual seperti GitHub commit:
- Baris = Murid
- Kotak = Subtopik
- Warna = TP
- 📦 Source: `evaluations` + cached summary

#### D2. Overall TP
Sistem kira:
- Purata subtopik → topik
- Purata topik → subjek

> 📌 Guru tak kira manual

---

### 🅔 PHASE E — LAPORAN & AUDIT (ON DEMAND)

> 🎯 Bila diperlukan sahaja

#### E1. Guru Jana Laporan
- Pilih murid
- Pilih subjek
- Pilih tempoh
- 📦 Source: `evaluations` (read-only)

#### E2. Output
- Paparan skrin
- PDF (optional)

**Laporan mengandungi:**
- Tarikh
- Standard Pembelajaran
- TP
- Sejarah

#### E3. Audit Log
Semua direkod:
- Siapa view
- Bila export
- 📦 DB: `audit_logs`

---

### 🅕 PHASE F — DATA LIFECYCLE

> 🎯 Sistem yang matang & selamat

#### F1. Data Kekal
- TP lama tak dipadam
- Sejarah kekal

#### F2. Tahun Baru
- Admin tambah RPT tahun baru (via Excel upload)
- Data lama tak disentuh

#### F3. Scale
- Partition `evaluations` ikut tahun
- Archive bila perlu

---

## 🧠 3️⃣ FLOW → TABLE MAPPING (CHEAT SHEET)

| Flow                    | Table                  |
|-------------------------|------------------------|
| Guru daftar             | `users`                |
| Murid ditambah          | `students`             |
| Murid ↔ kelas           | `class_students`       |
| Subjek ajar             | `teaching_assignments` |
| RPT                     | `topics`               |
| Standard Pembelajaran   | `subtopics`            |
| Kaedah Pentaksiran      | `assessment_methods`   |
| Template Ulasan         | `comment_templates`    |
| Isi TP                  | `evaluations`          |
| Visual                  | `evaluations` + cache  |
| Audit                   | `audit_logs`           |
| **DSKP Import**         | `dskp_imports`         |
| **Import Logs**         | `dskp_import_logs`     |

---

## 🏁 RUMUSAN A → Z

1. **Admin** sediakan struktur (via Excel upload)
2. **Guru** guna secara harian
3. **Sistem** simpan bukti
4. **Data** sentiasa sedia bila diperlukan

> ✅ Tiada kerja bertindih  
> ✅ Tiada borang panjang  
> ✅ Tiada stress hujung tahun