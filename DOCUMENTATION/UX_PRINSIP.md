# 🎯 Prinsip UX iRPM (WAJIB PEGANG)

Untuk proses isi TP murid, UX mesti:

- **Zero typing** (tiada taip nombor / teks)
- **One-hand friendly** (phone)
- **Maximum 2–3 interaction** per murid
- **Boleh buat laju** tanpa tengok lama
- **Auto-save** (tiada confirm button)

> **UX target:** 30 murid ≤ 90 saat

---

## 📱 UI UTAMA: "TP QUICK INPUT VIEW"

Ini screen paling kritikal dalam iRPM.

### 🧩 Struktur Screen (Phone & Laptop)

```
┌───────────────────────────┐
│ Kelas: 5 Amanah           │
│ Subjek: Matematik         │
│ Subtopik: Pecahan Wajar   │
├───────────────────────────┤
│ Ali     [1][2][3][4][5][6]│
│ Aisyah  [1][2][3][4][5][6]│
│ Adam    [1][2][3][4][5][6]│
│ ...                       │
└───────────────────────────┘
```

👉 **Cikgu hanya TAP satu nombor. Siap.**

---

## 🟢 OPTION 1 (RECOMMENDED): INLINE TP BUTTONS

### Cara Interaksi
- Setiap murid → ada 6 button TP
- Tap sekali → auto save
- Button bertukar warna ikut TP

### Kenapa paling laju?
- Tiada modal
- Tiada dropdown
- Tiada confirm

### Interaction Count
```
1 murid = 1 tap
30 murid = 30 tap
```

> 🔥 **UX terpantas**

---

## 🎨 Warna TP (Standard & Mudah Faham)

| TP  | Warna       |
|-----|-------------|
| TP1 | Merah       |
| TP2 | Oren        |
| TP3 | Kuning      |
| TP4 | Hijau muda  |
| TP5 | Hijau       |
| TP6 | Hijau gelap |

> 📌 Guru tak perlu baca nombor, cukup lihat warna.

---

## 🟢 AUTO SAVE BEHAVIOR (PENTING)

### ❌ Jangan ada button:
- "Save"
- "Submit"
- "Confirm"

### ✔️ Sebaliknya:
- Tap TP → API call
- Success → haptic / tick kecil
- Offline → simpan local → sync kemudian

---

## 🧠 UX DETAIL KECIL (TAPI BESAR IMPACT)

### 1️⃣ Highlight Murid Belum Dinilai
- Background kelabu
- Hilang bila TP dipilih

### 2️⃣ Sticky Header
- Nama kelas & subtopik sentiasa nampak
- Kurangkan keliru

### 3️⃣ Last TP Memory
- Jika cikgu selalu beri TP4:
  - Default highlight TP4
  - 1 tap terus confirm

### 4️⃣ Undo (5 saat)
- Toast: "TP disimpan (Undo)"
- Elak salah tekan

---

## 💻 UX LAPTOP (GRID MODE)

Untuk laptop / tablet:

```
          TP1 TP2 TP3 TP4 TP5 TP6
Ali        ○   ○   ○   ●   ○   ○
Aisyah     ○   ○   ○   ○   ●   ○
Adam       ○   ○   ●   ○   ○   ○
```

- Click sekali
- Keyboard navigation (optional)
- Scroll laju

---

## 🧪 UX TESTING TARGET (KPI)

| Metric                      | Target    |
|-----------------------------|-----------|
| Masa isi 30 murid           | ≤ 90 saat |
| Click per murid             | 1         |
| Error rate                  | < 2%      |
| Guru faham tanpa training   | Ya        |

---

## 🔁 FLOW LENGKAP (REAL USE CASE)

1. Guru buka phone
2. Tap "Today Class"
3. Pilih subtopik
4. Screen TP Quick Input muncul
5. Tap TP murid satu-satu
6. Keluar → data dah selamat

> 📌 Tiada popup, tiada borang

---

## ⚠️ UX ANTI-PATTERN (JANGAN BUAT)

- ❌ Dropdown TP
- ❌ Form panjang
- ❌ Perlu tekan "Save All"
- ❌ Reload page
- ❌ Alert popup kerap

> **Ini semua bunuh adoption.**

---

## 🏁 RUMUSAN UX iRPM

> **Cikgu tak nak sistem cantik.**
> **Cikgu nak sistem cepat.**

iRPM UX mesti:

- **Tap** → selesai
- **Warna** → faham
- **Auto** → selamat