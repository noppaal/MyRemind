# 🔍 Debug: Kenapa Jadwal Tidak Tampil?

## Checklist Debugging

### 1. Cek apakah data tersimpan di database
```sql
SELECT * FROM jadwalkuliah WHERE NIM = 'YOUR_NIM' ORDER BY IDJadwal DESC LIMIT 5;
```

### 2. Cek apakah mata kuliah dibuat
```sql
SELECT * FROM matakuliah ORDER BY KodeMK DESC LIMIT 5;
```

### 3. Cek query di dashboard
Dashboard menggunakan query:
```php
$queryJadwalToday = "SELECT j.*, m.NamaMK, d.NamaDosen 
                     FROM jadwalkuliah j 
                     LEFT JOIN matakuliah m ON j.KodeMK = m.KodeMK 
                     LEFT JOIN dosen d ON m.KodeDosen = d.KodeDosen
                     WHERE j.NIM = '$nim' AND j.Hari = '$hariIni' 
                     ORDER BY j.JamMulai ASC";
```

### 4. Cek nilai $hariIni
Pastikan `$hariIni` sesuai dengan hari yang dipilih saat membuat jadwal.
- Hari ini (26 Des 2025) = Jumat
- Form memilih: Jumat
- Seharusnya match ✅

### 5. Kemungkinan Masalah

#### A. Field Kelas tidak ada di form
Form modal jadwal tidak punya field `Kelas`, tapi tabel `jadwalkuliah` mungkin require field ini.

#### B. Time format
- Form mengirim: `"14:00"` (HH:MM)
- Model menambahkan: `":00"` → `"14:00:00"` (HH:MM:SS)
- Seharusnya OK ✅

#### C. NIM tidak tersimpan
Pastikan `$_SESSION['nim']` ada saat submit form.

### 6. Solusi

#### Tambahkan field Kelas ke form modal
```html
<div class="mb-4">
    <label class="block font-medium text-gray-700 mb-2 text-sm">Kelas</label>
    <input type="text" name="kelas" class="..." placeholder="Contoh: 3IF-01">
</div>
```

#### Update ScheduleModel::addSchedule()
```php
$kelas = mysqli_real_escape_string($conn, $data['kelas'] ?? '');

$query = "INSERT INTO jadwalkuliah (KodeMK, NIM, Hari, JamMulai, JamSelesai, Ruangan, Kelas) 
          VALUES ('$kodeMK', '$nim', '$hari', '$jamMulai', '$jamSelesai', '$ruangan', '$kelas')";
```

### 7. Test Query Manual
Jalankan di phpMyAdmin:
```sql
-- Cek data jadwal
SELECT j.*, m.NamaMK 
FROM jadwalkuliah j 
LEFT JOIN matakuliah m ON j.KodeMK = m.KodeMK 
WHERE j.NIM = 'YOUR_NIM';

-- Jika kosong, cek apakah insert berhasil
SELECT * FROM jadwalkuliah ORDER BY IDJadwal DESC LIMIT 1;
```

## Quick Fix

Tambahkan debug output di dashboard untuk melihat query:
```php
// Setelah query
echo "<!-- Debug: NIM = $nim, Hari = $hariIni -->";
echo "<!-- Debug: Count = " . mysqli_num_rows($resultJadwalToday) . " -->";
```
