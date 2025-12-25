# iRPM — Database Design
**ERD & Table Definitions**

---

## 1️⃣ ERD VISUAL (UPDATED)

```
ADMIN
 └── USERS (Guru)
       ├── CLASSES
       │     └── CLASS_STUDENTS
       │           └── STUDENTS (global)
       │
       ├── TEACHING_ASSIGNMENTS
       │
       └── EVALUATIONS (RPM CORE)
             ├── SUBJECTS
             ├── TOPICS (RPT)
             ├── SUBTOPICS (Standard Pembelajaran)
             └── ASSESSMENT_METHODS
                   └── COMMENT_TEMPLATES

ADMIN TOOLS
 └── DSKP_IMPORTS
       └── DSKP_IMPORT_LOGS
```

---

## 2️⃣ TABLE DEFINITIONS (UPDATED & LOCKED)

### 👤 users (GURU)
```sql
id
name
email
password
role ENUM('admin','guru')
created_at
```

> 📌 Hanya 1 admin (kau)  
> 📌 Semua user lain = guru

---

### 👩‍🎓 students (GLOBAL)
```sql
id
ic_hash (UNIQUE, INDEX)
encrypted_ic
encrypted_name
school_name   -- optional (TEXT)
created_at
```

> 📌 Murid:
> - Boleh wujud tanpa sekolah formal
> - Boleh digunakan oleh banyak guru

---

### 🏫 classes (MILIK GURU)
```sql
id
teacher_id
name        -- contoh: 6 Amanah
year
created_at
```

> 📌 Kelas = konteks kerja guru, bukan sekolah

---

### 🔗 class_students
```sql
class_id
student_id
```

---

### 📘 subjects
```sql
id
name
level
created_at
```

---

### 🧑‍🏫 teaching_assignments
```sql
id
teacher_id
class_id
subject_id
year
```

> 📌 Masih perlu untuk:
> - Guru ajar banyak kelas
> - Guru ajar banyak subjek

---

### 🧠 topics (RPT)
```sql
id
subject_id
year
theme
title
standard_kandungan
sequence
```

> 📌 Admin (kau) urus semua RPT

---

### 🧩 subtopics (STANDARD PEMBELAJARAN)
```sql
id
topic_id
code        -- contoh: 1.1.1
description
sequence
```

---

## ⭐ PBD EXTENSION (DARI EXCEL)

### 📝 assessment_methods
```sql
id
code        -- OBS, LISAN, BERTULIS, PROJEK
name        -- Pemerhatian, Lisan, Bertulis
created_at
```

> 📌 Ini datang terus dari amalan PBD sebenar

---

### 💬 comment_templates
```sql
id
assessment_method_id
tp               -- 1 hingga 6
template_text
created_at
```

**Contoh:**
- TP3 + Pemerhatian → *"Murid menunjukkan kefahaman asas melalui pemerhatian dengan bimbingan."*

---

### ⭐ evaluations (RPM CORE)
```sql
id
student_id
teacher_id
subject_id
topic_id
subtopic_id
assessment_method_id
tp                  -- 1 hingga 6
auto_comment        -- generated from template
custom_comment NULL -- if teacher edit
evaluation_date
created_at
```

> 🔥 **Append-only**  
> 🔥 **Guru-based ownership**  
> 🔥 **Audit-friendly**  
> 🔥 **Excel-equivalent 1-to-1**

---

### 📅 teaching_schedules
```sql
id
teacher_id
class_id
subject_id
day_of_week
start_time
```

---

### 🧾 audit_logs
```sql
id
user_id
action
entity_type
entity_id
created_at
```

---

## 📤 DSKP EXCEL IMPORT (ADMIN ONLY)

### 🆕 dskp_imports
```sql
id
subject_id
year
file_path
imported_by     -- admin user id
status ENUM('pending','completed','failed')
created_at
```

> 📌 Track setiap import Excel DSKP

---

### 🆕 dskp_import_logs
```sql
id
dskp_import_id
row_number
message
level ENUM('info','warning','error')
created_at
```

> 📌 Untuk: Debug, Audit, Transparency

---

### 📋 Expected Excel Format

| Column | Description |
|--------|-------------|
| Subject | Nama subjek |
| Year | Tahun (e.g., 5) |
| Theme | Tema |
| Topic | Tajuk |
| Standard Content | Standard Kandungan |
| SP Code | Kod Standard Pembelajaran (e.g., 1.1.1) |
| SP Description | Huraian |

**Contoh row:**
```
Sains | 5 | Inkuiri | Kemahiran Saintifik | 1.1 | 1.1.1 | Menjalankan eksperimen mudah
```

> 📌 Tak kisah susunan column  
> 📌 Admin akan map column masa upload (1 kali sahaja)

---

### 🔒 Business Rules (DSKP Import)

| Rule | Description |
|------|-------------|
| **Subject + Year Unique** | Satu subjek + tahun = 1 struktur aktif |
| **Re-upload** | Replace dengan confirmation (soft-delete lama) |
| **Data Selamat** | Evaluations tidak dipadam, maintain link via subtopic_id |
| **Admin Only** | Guru ❌ tak boleh upload/edit RPT |

---

### 🔐 Security (File Upload)

- File size limit
- Virus scan (basic)
- Only `.xlsx` / `.csv`
- Store file di object storage

---

## 3️⃣ ACCESS CONTROL (UPDATED LOGIC)

### Rule Utama
Guru hanya boleh:
- Lihat class dia
- Isi TP yang dia buat

### Student Record
- Boleh wujud global
- Tapi evaluation terikat kepada guru

> ➡️ **Guru A tak boleh edit penilaian Guru B**

---

## 4️⃣ KENAPA MODEL INI FUTURE-PROOF

### Sekarang (B2C Guru)
- Guru guna sendiri
- Platform admin kawal segalanya

### Masa Depan (B2B Sekolah)
Boleh tambah:
- `schools` table
- Assign guru → sekolah
- **Data sedia ada TAK ROSAK**

> 📌 Ini design yang betul untuk grow

---

## 5️⃣ RISIKO & MITIGASI

### Risiko: Murid Duplicate
**Mitigasi:**
- IC hash UNIQUE
- Merge flow (admin only)

### Risiko: Data Trust
**Mitigasi:**
- Audit log
- Read-only history

---

## 🧠 RUMUSAN KEPUTUSAN

> **Sekolah bukan user.**  
> **Guru ialah user.**  
> **Admin ialah pemilik sistem.**

Ini buat iRPM:
- ✅ Cepat berkembang
- ✅ Mudah digunakan
- ✅ Kurang birokrasi
- ✅ Lebih realistik untuk startup