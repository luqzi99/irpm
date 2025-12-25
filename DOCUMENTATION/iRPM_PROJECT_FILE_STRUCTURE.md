# 📁 iRPM PROJECT FILE STRUCTURE

Struktur yang boleh terus guna untuk kickoff project tanpa refactor besar kemudian.

Bahagian: **Backend (Laravel)** dan **Frontend (Vue)**

---

## 🧱 BACKEND — Laravel (API)

```
backend/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php
│   │   │   │
│   │   │   ├── Admin/
│   │   │   │   ├── SubjectController.php
│   │   │   │   ├── DskpImportController.php
│   │   │   │   └── CommentTemplateController.php
│   │   │   │
│   │   │   └── Teacher/
│   │   │       ├── ClassController.php
│   │   │       ├── StudentController.php
│   │   │       ├── EvaluationController.php
│   │   │       └── ProgressController.php
│   │   │
│   │   ├── Middleware/
│   │   │   └── EnsureRole.php
│   │   │
│   │   └── Requests/
│   │       ├── Auth/
│   │       ├── Admin/
│   │       │   └── DskpImportRequest.php
│   │       └── Teacher/
│   │           └── EvaluationRequest.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Student.php
│   │   ├── ClassRoom.php
│   │   ├── Subject.php
│   │   ├── Topic.php
│   │   ├── Subtopic.php
│   │   ├── TeachingAssignment.php
│   │   ├── Evaluation.php
│   │   ├── AssessmentMethod.php
│   │   ├── CommentTemplate.php
│   │   ├── DskpImport.php
│   │   └── AuditLog.php
│   │
│   ├── Services/
│   │   ├── Dskp/
│   │   │   ├── DskpExcelParser.php
│   │   │   ├── DskpNormalizer.php
│   │   │   └── DskpImportService.php
│   │   │
│   │   ├── Evaluation/
│   │   │   ├── EvaluationCreator.php
│   │   │   ├── AutoCommentGenerator.php
│   │   │   └── EvaluationAggregator.php
│   │   │
│   │   └── Security/
│   │       ├── IcEncryptionService.php
│   │       └── AuditLogger.php
│   │
│   ├── Jobs/
│   │   ├── ProcessDskpImportJob.php
│   │   └── UpdateEvaluationSummaryJob.php
│   │
│   ├── Policies/
│   │   ├── StudentPolicy.php
│   │   └── EvaluationPolicy.php
│   │
│   └── Providers/
│       └── AppServiceProvider.php
│
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   ├── xxxx_create_students_table.php
│   │   ├── xxxx_create_classes_table.php
│   │   ├── xxxx_create_subjects_table.php
│   │   ├── xxxx_create_topics_table.php
│   │   ├── xxxx_create_subtopics_table.php
│   │   ├── xxxx_create_teaching_assignments_table.php
│   │   ├── xxxx_create_assessment_methods_table.php
│   │   ├── xxxx_create_comment_templates_table.php
│   │   ├── xxxx_create_evaluations_table.php
│   │   ├── xxxx_create_dskp_imports_table.php
│   │   ├── xxxx_create_dskp_import_logs_table.php
│   │   ├── xxxx_create_teaching_schedules_table.php
│   │   └── xxxx_create_audit_logs_table.php
│   │
│   └── seeders/
│       ├── SubjectSeeder.php
│       ├── AssessmentMethodSeeder.php
│       └── CommentTemplateSeeder.php
│
├── routes/
│   ├── api.php
│   └── auth.php
│
├── storage/
│   └── dskp-imports/
│
├── tests/
│   ├── Feature/
│   │   ├── DskpImportTest.php
│   │   └── EvaluationTest.php
│   │
│   └── Unit/
│       └── AutoCommentGeneratorTest.php
│
└── docker/
    ├── Dockerfile
    └── nginx.conf
```

---

### 🧠 Kenapa Struktur Backend Ini Betul

#### ✅ Controllers Nipis
- Controller tak buat logic
- Semua logic berat → **Services**

#### ✅ DSKP Logic Terpisah
Excel import = complex & risky → diletakkan dalam:
```
Services/Dskp/
```
Supaya:
- Senang debug
- Senang test
- Tak kacau flow lain

#### ✅ Evaluation Logic Modular
```
Services/Evaluation/
```
- Auto ulasan
- Aggregation
- Append-only logic

> 📌 Ini jantung iRPM

#### ✅ Security Bukan Afterthought
```
Services/Security/
```
- Encryption
- Audit
- Centralized

---

## 🎨 FRONTEND — Vue.js (SPA)

```
frontend/
├── src/
│   ├── api/
│   │   ├── auth.js
│   │   ├── admin.js
│   │   ├── teacher.js
│   │   └── evaluation.js
│   │
│   ├── components/
│   │   ├── common/
│   │   │   ├── ButtonTP.vue
│   │   │   ├── Toast.vue
│   │   │   └── Loading.vue
│   │   │
│   │   ├── teacher/
│   │   │   ├── TpQuickInput.vue
│   │   │   ├── AssessmentMethodPicker.vue
│   │   │   ├── AutoCommentPreview.vue
│   │   │   └── ProgressGrid.vue
│   │   │
│   │   └── admin/
│   │       ├── DskpUpload.vue
│   │       ├── DskpColumnMapper.vue
│   │       └── DskpPreview.vue
│   │
│   ├── views/
│   │   ├── auth/
│   │   │   └── Login.vue
│   │   │
│   │   ├── teacher/
│   │   │   ├── Dashboard.vue
│   │   │   ├── ClassDetail.vue
│   │   │   └── EvaluationView.vue
│   │   │
│   │   └── admin/
│   │       ├── Subjects.vue
│   │       └── DskpImports.vue
│   │
│   ├── router/
│   │   └── index.js
│   │
│   ├── store/
│   │   ├── auth.js
│   │   └── evaluation.js
│   │
│   ├── utils/
│   │   ├── date.js
│   │   └── tpColor.js
│   │
│   └── main.js
│
└── vite.config.js
```

---

### 🧠 Kenapa Struktur Frontend Ini Betul

#### ✅ Component Ikut Domain, Bukan Page
- `TpQuickInput` boleh reuse
- `AssessmentMethodPicker` standalone

#### ✅ Admin & Teacher Jelas Dipisahkan
- Elak logic bercampur
- Senang control access

#### ✅ UX-Critical Component Diasingkan
Contoh:
- `ButtonTP`
- `AutoCommentPreview`

> ➡️ Mudah optimize performance

---

## 🐳 DOCKER & INFRA FILES

```
infra/
├── docker-compose.yml
├── .env.example
└── nginx/
    └── default.conf
```

---

## 🔐 ENV STRUCTURE (IMPORTANT)

```env
APP_ENV=local
APP_KEY=
DB_CONNECTION=pgsql
DB_HOST=postgres
REDIS_HOST=redis
FILESYSTEM_DISK=s3
```

---

## 🧠 RUMUSAN AKHIR

Struktur ini:
- ✅ Selari dengan ERD v3
- ✅ Selari dengan Excel PBD
- ✅ Mudah scale
- ✅ Mudah maintain
- ✅ Tak akan rosak bila tambah feature sekolah nanti

> **Kalau struktur betul dari hari pertama, coding jadi 3× lebih laju.**