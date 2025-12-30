<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../model/Database.php';

// Check if user is logged in
if (!isset($_SESSION['nim'])) {
    header("Location: ../../public/login.php");
    exit;
}

$nim = $_SESSION['nim'];
$kodeGrup = isset($_GET['kode']) ? $_GET['kode'] : '';

if (empty($kodeGrup)) {
    header("Location: ../../public/index.php?tab=grup");
    exit;
}

$conn = getConnection();

// Get group details and check if user is member
$query = "SELECT g.*, ga.Role, m.Nama as CreatorName
          FROM grup g
          LEFT JOIN grup_anggota ga ON g.KodeGrup = ga.KodeGrup AND ga.NIM = ?
          LEFT JOIN mahasiswa m ON g.CreatedBy = m.NIM
          WHERE g.KodeGrup = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $nim, $kodeGrup);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: ../../public/index.php?tab=grup&msg=group_not_found");
    exit;
}

$group = mysqli_fetch_assoc($result);

// Check if user is member
if (empty($group['Role'])) {
    header("Location: ../../public/index.php?tab=grup&msg=not_member");
    exit;
}

$userRole = $group['Role'];
$isAdmin = ($userRole === 'owner' || $userRole === 'admin');

// Get members
$membersQuery = "SELECT ga.*, m.Nama, m.Email
                 FROM grup_anggota ga
                 LEFT JOIN mahasiswa m ON ga.NIM = m.NIM
                 WHERE ga.KodeGrup = ?
                 ORDER BY 
                   CASE ga.Role 
                     WHEN 'owner' THEN 1 
                     WHEN 'admin' THEN 2 
                     ELSE 3 
                   END,
                   ga.JoinedAt ASC";
$stmtMembers = mysqli_prepare($conn, $membersQuery);
mysqli_stmt_bind_param($stmtMembers, "s", $kodeGrup);
mysqli_stmt_execute($stmtMembers);
$membersResult = mysqli_stmt_get_result($stmtMembers);

$members = [];
while ($row = mysqli_fetch_assoc($membersResult)) {
    $members[] = $row;
}

// Get events/schedules
$eventsQuery = "SELECT gj.*, m.Nama as CreatorName
                FROM grup_jadwal gj
                LEFT JOIN mahasiswa m ON gj.CreatedBy = m.NIM
                WHERE gj.KodeGrup = ?
                ORDER BY gj.TanggalMulai ASC";
$stmtEvents = mysqli_prepare($conn, $eventsQuery);
mysqli_stmt_bind_param($stmtEvents, "s", $kodeGrup);
mysqli_stmt_execute($stmtEvents);
$eventsResult = mysqli_stmt_get_result($stmtEvents);

$events = [];
while ($row = mysqli_fetch_assoc($eventsResult)) {
    $events[] = $row;
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($group['NamaGrup']) ?> - MyRemind</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen font-sans" style="background: linear-gradient(to bottom, #EEF2FF, #FAF5FF, #FDF2F8);">
    
    <div class="max-w-4xl mx-auto min-h-screen shadow-xl bg-white">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4">
            <div class="flex items-center gap-3 mb-3">
                <button onclick="window.location.href='../../public/index.php?tab=grup'" class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-all">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="flex-1">
                    <h1 class="text-xl font-bold"><?= htmlspecialchars($group['NamaGrup']) ?></h1>
                    <p class="text-sm text-white/80"><?= count($members) ?> anggota • <?= count($events) ?> event</p>
                </div>
                <?php if ($isAdmin): ?>
                <button onclick="openModal('modalInvite')" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-all">
                    <i class="fas fa-user-plus mr-2"></i>Undang
                </button>
                <?php endif; ?>
            </div>
            
            <?php if ($group['Deskripsi']): ?>
            <p class="text-sm text-white/90 bg-white/10 rounded-lg p-3"><?= htmlspecialchars($group['Deskripsi']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 bg-white sticky top-0 z-10">
            <button class="detail-tab flex-1 py-3 px-4 font-medium text-sm border-b-2 border-purple-600 text-purple-600" data-tab="jadwal">
                <i class="fas fa-calendar mr-2"></i>Jadwal
            </button>
            <button class="detail-tab flex-1 py-3 px-4 font-medium text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="anggota">
                <i class="fas fa-users mr-2"></i>Anggota
            </button>
            <?php if ($userRole === 'owner'): ?>
            <button class="detail-tab flex-1 py-3 px-4 font-medium text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="pengaturan">
                <i class="fas fa-cog mr-2"></i>Pengaturan
            </button>
            <?php endif; ?>
        </div>

        <!-- Tab Content -->
        <div class="p-4">
            <!-- Jadwal Tab -->
            <div id="tab-jadwal" class="detail-tab-content">
                <div class="mb-4">
                    <button onclick="openModal('modalAddEvent')" class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-medium hover:shadow-lg transition-all">
                        <i class="fas fa-plus mr-2"></i>Tambah Jadwal/Event
                    </button>
                </div>

                <?php if (count($events) > 0): ?>
                <div class="space-y-3">
                    <?php foreach ($events as $event): 
                        $start = new DateTime($event['TanggalMulai']);
                        $end = new DateTime($event['TanggalSelesai']);
                        $now = new DateTime();
                        $isPast = $end < $now;
                        $isToday = $start->format('Y-m-d') === $now->format('Y-m-d');
                    ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-4 <?= $isPast ? 'opacity-60' : '' ?>">
                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 rounded-lg <?= $isToday ? 'bg-red-100' : 'bg-purple-100' ?> flex items-center justify-center flex-shrink-0">
                                <div class="text-center">
                                    <div class="text-xs <?= $isToday ? 'text-red-600' : 'text-purple-600' ?> font-semibold"><?= $start->format('d') ?></div>
                                    <div class="text-xs <?= $isToday ? 'text-red-500' : 'text-purple-500' ?>"><?= $start->format('M') ?></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800 mb-1"><?= htmlspecialchars($event['JudulKegiatan']) ?></h3>
                                <?php if ($event['Deskripsi']): ?>
                                <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($event['Deskripsi']) ?></p>
                                <?php endif; ?>
                                <div class="flex flex-wrap gap-2 text-xs text-gray-500">
                                    <span><i class="fas fa-clock mr-1"></i><?= $start->format('H:i') ?> - <?= $end->format('H:i') ?></span>
                                    <?php if ($event['Lokasi']): ?>
                                    <span><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($event['Lokasi']) ?></span>
                                    <?php endif; ?>
                                    <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($event['CreatorName']) ?></span>
                                </div>
                            </div>
                            <?php if ($isAdmin || $event['CreatedBy'] === $nim): ?>
                            <div class="flex gap-2">
                                <button onclick="editEvent('<?= $event['KodeJadwal'] ?>')" class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button onclick="deleteEvent('<?= $event['KodeJadwal'] ?>')" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-calendar-alt text-5xl mb-3"></i>
                    <p class="text-sm font-medium">Belum ada jadwal</p>
                    <p class="text-xs mt-1">Buat jadwal pertama untuk grup ini</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Anggota Tab -->
            <div id="tab-anggota" class="detail-tab-content hidden">
                <?php if ($isAdmin): ?>
                <div class="mb-4">
                    <button onclick="openModal('modalAddMember')" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg font-medium hover:shadow-lg transition-all">
                        <i class="fas fa-user-plus mr-2"></i>Tambah Anggota
                    </button>
                </div>
                <?php endif; ?>

                <div class="space-y-2">
                    <?php foreach ($members as $member): ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                            <?= strtoupper(substr($member['Nama'], 0, 1)) ?>
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-gray-800 text-sm"><?= htmlspecialchars($member['Nama']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($member['NIM']) ?></div>
                            <?php if (!empty($member['Email'])): ?>
                            <div class="text-xs text-gray-400 mt-0.5">
                                <i class="fas fa-envelope mr-1"></i><?= htmlspecialchars($member['Email']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php
                            $roleColors = [
                                'owner' => 'bg-yellow-100 text-yellow-700',
                                'admin' => 'bg-blue-100 text-blue-700',
                                'member' => 'bg-gray-100 text-gray-700'
                            ];
                            $roleColor = $roleColors[$member['Role']] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <span class="<?= $roleColor ?> text-xs font-medium px-2 py-1 rounded-full">
                                <?= ucfirst($member['Role']) ?>
                            </span>
                            <?php if ($isAdmin && $member['NIM'] !== $nim && $member['Role'] !== 'owner'): ?>
                            <button onclick="removeMember('<?= $member['NIM'] ?>', '<?= htmlspecialchars($member['Nama']) ?>')" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pengaturan Tab (Owner only) -->
            <?php if ($userRole === 'owner'): ?>
            <div id="tab-pengaturan" class="detail-tab-content hidden">
                <div class="space-y-4">
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">Informasi Grup</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Dibuat oleh:</span>
                                <span class="font-medium"><?= htmlspecialchars($group['CreatorName']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Dibuat pada:</span>
                                <span class="font-medium"><?= date('d M Y', strtotime($group['CreatedAt'])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kode Grup:</span>
                                <span class="font-mono text-xs"><?= $kodeGrup ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h3 class="font-semibold text-red-800 mb-2">Zona Bahaya</h3>
                        <p class="text-sm text-red-700 mb-3">Tindakan ini tidak dapat dibatalkan</p>
                        <button onclick="deleteGroup()" class="w-full py-2 px-4 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-all">
                            <i class="fas fa-trash mr-2"></i>Hapus Grup
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Add Event -->
    <div id="modalAddEvent" class="modal hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-5">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-lg font-bold text-gray-800">Tambah Jadwal/Event</h2>
                <button onclick="closeModal('modalAddEvent')" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="grup_jadwal_create.php" method="POST">
                <input type="hidden" name="kode_grup" value="<?= $kodeGrup ?>">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul <span class="text-red-500">*</span></label>
                        <input type="text" name="judul" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Contoh: Rapat Kelompok">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Deskripsi kegiatan..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="tanggal_mulai" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="tanggal_selesai" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                        <input type="text" name="lokasi" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Contoh: Ruang 301">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('modalAddEvent')" class="flex-1 py-2 px-4 border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" class="flex-1 py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Event -->
    <div id="modalEditEvent" class="modal hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-5">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-lg font-bold text-gray-800">Edit Jadwal/Event</h2>
                <button onclick="closeModal('modalEditEvent')" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="grup_jadwal_update.php" method="POST">
                <input type="hidden" name="kode_jadwal" id="edit_kode_jadwal">
                <input type="hidden" name="kode_grup" value="<?= $kodeGrup ?>">
                
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">Judul Kegiatan *</label>
                    <input type="text" name="judul" id="edit_judul" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block font-medium text-gray-700 mb-2 text-sm">Tanggal Mulai *</label>
                        <input type="datetime-local" name="tanggal_mulai" id="edit_tanggal_mulai" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-2 text-sm">Tanggal Selesai *</label>
                        <input type="datetime-local" name="tanggal_selesai" id="edit_tanggal_selesai" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block font-medium text-gray-700 mb-2 text-sm">Lokasi</label>
                    <input type="text" name="lokasi" id="edit_lokasi" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('modalEditEvent')" class="flex-1 py-2 px-4 border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" class="flex-1 py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Add Member -->
    <div id="modalAddMember" class="modal hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-5">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-lg font-bold text-gray-800">Tambah Anggota</h2>
                <button onclick="closeModal('modalAddMember')" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="grup_member_add.php" method="POST">
                <input type="hidden" name="kode_grup" value="<?= $kodeGrup ?>">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">NIM Anggota <span class="text-red-500">*</span></label>
                    <input type="text" name="nim_anggota" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: 1301210001">
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('modalAddMember')" class="flex-1 py-2 px-4 border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Generate Invite -->
    <div id="modalInvite" class="modal hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-5">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-lg font-bold text-gray-800">Undang Anggota</h2>
                <button onclick="closeModal('modalInvite')" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-3 text-sm">
                        <i class="fas fa-link mr-2 text-blue-600"></i>Kode Undangan Grup
                    </h4>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex-1 bg-white border-2 border-blue-400 rounded-lg px-4 py-3 font-mono text-lg font-bold text-blue-600 text-center">
                            <?= htmlspecialchars($group['KodeGrup']) ?>
                        </div>
                        <button onclick="copyInviteCode('<?= htmlspecialchars($group['KodeGrup']) ?>', event)" 
                            class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="bg-blue-100 border border-blue-300 rounded-lg p-3">
                        <p class="text-xs text-blue-900 mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Cara menggunakan:</strong>
                        </p>
                        <ol class="text-xs text-blue-800 space-y-1 ml-4 list-decimal">
                            <li>Klik tombol copy di samping kode</li>
                            <li>Bagikan kode ke teman via WhatsApp/Email</li>
                            <li>Teman masuk ke menu "Gabung Grup" di dashboard</li>
                            <li>Paste kode dan klik "Gabung"</li>
                        </ol>
                    </div>
                </div>
                
                <button onclick="closeModal('modalInvite')" 
                    class="w-full py-3 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all font-medium">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.detail-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                // Update tab buttons
                document.querySelectorAll('.detail-tab').forEach(t => {
                    t.classList.remove('border-purple-600', 'text-purple-600');
                    t.classList.add('border-transparent', 'text-gray-500');
                });
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('border-purple-600', 'text-purple-600');
                
                // Update tab content
                document.querySelectorAll('.detail-tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                document.getElementById('tab-' + tabName).classList.remove('hidden');
            });
        });

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Generate invite code
        function generateInvite() {
            fetch('grup_invite_create.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'kode_grup=<?= $kodeGrup ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('inviteContent').innerHTML = `
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-green-800 mb-2">Kode undangan berhasil dibuat!</p>
                            <div class="bg-white border border-green-300 rounded-lg p-3 mb-2">
                                <p class="text-center font-mono text-2xl font-bold text-purple-600">${data.inviteCode}</p>
                            </div>
                            <p class="text-xs text-green-700">Berlaku hingga: ${new Date(data.expiresAt).toLocaleDateString('id-ID')}</p>
                        </div>
                        <button onclick="copyInviteCode('${data.inviteCode}')" class="w-full py-2 px-4 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            <i class="fas fa-copy mr-2"></i>Salin Kode
                        </button>
                    `;
                } else {
                    alert('Error: ' + (data.error || 'Gagal membuat kode undangan'));
                }
            });
        }

        function copyInviteCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                alert('Kode undangan berhasil disalin: ' + code);
            });
        }

        // Remove member
        function removeMember(nim, nama) {
            if (confirm(`Hapus ${nama} dari grup?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'grup_member_remove.php';
                form.innerHTML = `
                    <input type="hidden" name="kode_grup" value="<?= $kodeGrup ?>">
                    <input type="hidden" name="nim_anggota" value="${nim}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Edit event
        function editEvent(kodeJadwal) {
            // Get event data from the page
            const events = <?= json_encode($events) ?>;
            const event = events.find(e => e.KodeJadwal === kodeJadwal);
            
            if (!event) {
                alert('Event tidak ditemukan');
                return;
            }
            
            // Populate modal fields
            document.getElementById('edit_kode_jadwal').value = event.KodeJadwal;
            document.getElementById('edit_judul').value = event.JudulKegiatan;
            document.getElementById('edit_deskripsi').value = event.Deskripsi || '';
            document.getElementById('edit_lokasi').value = event.Lokasi || '';
            
            // Format datetime for input
            const formatDateTime = (dateStr) => {
                const d = new Date(dateStr);
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                const hours = String(d.getHours()).padStart(2, '0');
                const minutes = String(d.getMinutes()).padStart(2, '0');
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            };
            
            document.getElementById('edit_tanggal_mulai').value = formatDateTime(event.TanggalMulai);
            document.getElementById('edit_tanggal_selesai').value = formatDateTime(event.TanggalSelesai);
            
            // Open modal
            openModal('modalEditEvent');
        }

        // Delete event
        function deleteEvent(kodeJadwal) {
            if (confirm('Hapus jadwal ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'grup_jadwal_delete.php';
                form.innerHTML = `<input type="hidden" name="kode_jadwal" value="${kodeJadwal}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Delete group
        function deleteGroup() {
            if (confirm('PERINGATAN: Hapus grup ini?\n\nSemua data anggota dan jadwal akan dihapus permanen!')) {
                if (confirm('Apakah Anda yakin? Tindakan ini tidak dapat dibatalkan!')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'grup_delete.php';
                    form.innerHTML = `<input type="hidden" name="kode_grup" value="<?= $kodeGrup ?>">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>

    <script>
        function copyInviteCode(code, evt) {
            navigator.clipboard.writeText(code).then(function() {
                // Show success feedback
                if (evt && evt.target) {
                    const btn = evt.target.closest('button');
                    if (btn) {
                        const originalHTML = btn.innerHTML;
                        btn.innerHTML = '<i class="fas fa-check"></i>';
                        btn.classList.add('bg-green-600');
                        btn.classList.remove('bg-blue-600');
                        
                        setTimeout(function() {
                            btn.innerHTML = originalHTML;
                            btn.classList.remove('bg-green-600');
                            btn.classList.add('bg-blue-600');
                        }, 2000);
                    }
                }
                
                // Show alert notification
                alert('Kode undangan berhasil disalin!\n\nKode: ' + code + '\n\nBagikan kode ini kepada teman yang ingin bergabung.');
            }).catch(function(err) {
                alert('Gagal menyalin kode: ' + err);
            });
        }
    </script>
</body>
</html>




