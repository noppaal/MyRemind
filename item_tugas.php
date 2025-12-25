<?php
// --- Helper Code untuk Menampilkan 1 Baris Tugas ---
$deadlineDate = new DateTime($t['Deadline']);
$now = new DateTime();
$isOverdue = $now > $deadlineDate;
$diff = $now->diff($deadlineDate);
$daysLeft = $diff->days;

// Cek Status Selesai
$isDone = ($t['StatusTugas'] == 'Selesai');

$namaMK = empty($t['NamaMK']) ? 'Tugas Umum' : $t['NamaMK'];
$deskripsi = empty($t['Deskripsi']) ? '' : $t['Deskripsi'];
?>

<div class="bg-white rounded-2xl p-4 border border-gray-200 hover:shadow-md transition-all duration-300 relative">
    <div class="flex justify-between items-start mb-2">
        <h4 class="font-semibold text-gray-800 text-base flex-1 pr-2"><?= htmlspecialchars($t['JudulTugas']) ?></h4>
        
        <!-- Three-dot menu -->
        <div class="relative">
            <button class="text-gray-400 hover:text-gray-600 p-1 task-menu-btn" data-task-id="<?= $t['KodeTugas'] ?>">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            
            <!-- Dropdown menu (hidden by default) -->
            <div class="task-menu hidden absolute right-0 top-8 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-10 min-w-[150px]" id="menu-<?= $t['KodeTugas'] ?>">
                <?php if(!$isDone): ?>
                <a href="proses_selesai.php?id=<?= $t['KodeTugas'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors" onclick="return confirm('Tandai tugas ini sebagai selesai?')">
                    <i class="fas fa-check mr-2 text-green-600"></i> Tandai Selesai
                </a>
                <?php endif; ?>
                <button class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors btn-edit-tugas" 
                    data-id="<?= $t['KodeTugas'] ?>" 
                    data-judul="<?= htmlspecialchars($t['JudulTugas']) ?>" 
                    data-deadline="<?= date('Y-m-d\TH:i', strtotime($t['Deadline'])) ?>" 
                    data-deskripsi="<?= htmlspecialchars($t['Deskripsi']) ?>">
                    <i class="fas fa-edit mr-2 text-blue-600"></i> Edit
                </button>
                <a href="hapus.php?id=<?= $t['KodeTugas'] ?>&type=tugas" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 transition-colors" onclick="return confirm('Yakin ingin menghapus tugas ini?')">
                    <i class="fas fa-trash mr-2"></i> Hapus
                </a>
            </div>
        </div>
    </div>
    
    <?php if(!empty($deskripsi)): ?>
    <p class="text-gray-600 text-sm mb-3"><?= htmlspecialchars($deskripsi) ?></p>
    <?php endif; ?>
    
    <div class="text-gray-500 text-sm">
        <?php if($isDone): ?>
            <span class="text-green-600">Selesai</span>
        <?php elseif($isOverdue): ?>
            <span class="text-red-600 font-medium">Deadline: <?= date('d/m/Y', strtotime($t['Deadline'])) ?></span>
        <?php else: ?>
            <span>Deadline: <?= date('d/m/Y', strtotime($t['Deadline'])) ?></span>
        <?php endif; ?>
    </div>
</div>