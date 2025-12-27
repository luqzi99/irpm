# iRPM - Features List

## 🔐 Authentication & User Management

### Login & Registration
- Email/password authentication with Laravel Sanctum
- User registration with role selection (Teacher/Admin)
- Password reset via email

### Email Verification
- Email verification required for new accounts
- Resend verification email functionality
- Unverified users redirected to verification page

### Profile Management
- View and update profile information
- Change password

---

## 👨‍🏫 Teacher Features

### Dashboard
- Overview of classes taught
- Quick stats and recent activity
- Today's schedule display

### Class Management
- Create, edit, delete classes
- Add/remove students from classes
- Student lookup by IC number (auto-fill)

### Schedule Management
- Weekly teaching schedule (day + time slots)
- Link class and subject to schedule
- Current schedule detection for auto-populate

### TP Input (Tahap Penguasaan)
- Quick TP entry (1-6 scale)
- Auto-populate class/subject based on current schedule
- Topic → Subtopic selection
- Batch TP entry for multiple students
- History of recent evaluations

### Progress View
- Per-class progress summary
- Student list with average TP and completion %
- Color-coded TP badges
- Progress bar for each student
- Link to detailed student view

### Student Detail View
- Individual student TP breakdown
- TP per subtopic display
- Historical evaluation data

### Excel Report Export
- Download Excel report with multiple sheets:
  - **LAPORAN** - Student TP matrix by subtopic
  - **ANALISA** - Summary table + Pie chart + Bar chart
- MTM (Minimum Target Mastery) percentage calculation
- Auto-generated "Ulasan" (comments) based on TP level

---

## 👔 Admin Features

### Admin Dashboard
- System-wide statistics
- User management overview
- Quick access to admin functions

### User Management
- View all users
- Edit user details
- Assign/change user roles
- Activate/deactivate accounts

### DSKP Management
- View all subjects with topics/subtopics
- Import DSKP from Excel file
- Preview import before confirming
- Edit existing topics/subtopics
- Education level filtering (Primary/Secondary)

---

## 📱 PWA Features

### Progressive Web App
- Installable on mobile devices
- Offline-capable with service worker
- Push notifications support
- App-like experience

---

## 🔧 Technical Features

### Security
- Encrypted student data (name, IC)
- IC hashing for lookups
- Token-based API authentication
- CORS configured for frontend

### Database
- PostgreSQL database
- Teacher-student many-to-many relationship
- Evaluation history with timestamps

### API
- RESTful API with Laravel
- Sanctum token authentication
- JSON responses

---

## 📊 Report Features

### TP Statistics
- Average TP calculation (integer rounding)
- Completion percentage
- TP distribution (TP1-TP6 counts)

### MTM Analysis
- TP 1-3: Tidak Mencapai MTM (Below target)
- TP 4-6: Mencapai MTM (Meeting target)
- Percentage breakdown

### Charts (in Excel export)
- Pie chart: TP distribution percentage
- Bar chart: Student count per TP level
