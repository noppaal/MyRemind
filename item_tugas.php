<?php
// --- Helper Code untuk Menampilkan 1 Baris Tugas ---
$deadlineDate = new DateTime($t['Deadline']);
$now = new DateTime();
$tomorrow = new DateTime('tomorrow');
$isOverdue = $now > $deadlineDate;
$diff = $now->diff($deadlineDate);
$daysLeft = $diff->days;

// Cek apakah deadline besok (H-1)
$isTomorrow = ($deadlineDate->format('Y-m-d') === $tomorrow->format('Y-m-d'));

// Cek Status Selesai
$isDone = ($t['StatusTugas'] == 'Selesai');

$namaMK = empty($t['NamaMK']) ? 'Tugas Umum' : $t['NamaMK'];
$deskripsi = empty($t['Deskripsi']) ? '' : $t['Deskripsi'];
?>

<div class="bg-white rounded-2xl p-4 border <?= $isTomorrow && !$isDone ? 'border-orange-300 shadow-orange-100 shadow-lg' : 'border-gray-200' ?> hover:shadow-md transition-all duration-300 relative">
    <?php if ($isTomorrow && !$isDone): ?>
    <!-- H-1 Warning Badge -->
    <div class="absolute -top-2 -right-2 bg-gradient-to-r from-orange-500 to-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse">
        <i class="fas fa-exclamation-triangle mr-1"></i>H-1
    </div>
    <?php endif; ?>
    
    <div class="flex justify-between items-start mb-2">
        <h4 class="font-semibold text-gray-800 text-base flex-1 pr-2"><?= htmlspecialchars($t['JudulTugas']) ?></h4>
        
        <!-- Three-dot menu -->
        <div class="relative">
            <button class="text-gray-400 hover:text-gray-600 p-1 task-menu-btn" data-task-id="<?= $t['KodeTugas'] ?>">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            
            <!-- Dropdown menu (hidden by default) -->
            <div class="task-menu hidden absolute right-0 top-8 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-10 min-w-[150px]" id="menu-<?= $t['KodeTugas'] ?>">
                <?php if($t['StatusTugas'] == 'Aktif'): ?>
                <a href="<?= BASE_URL ?>/public/proses_progress.php?kode=<?= $t['KodeTugas'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors" onclick="return confirm('Mulai mengerjakan tugas ini?')">
                    <i class="fas fa-play mr-2 text-blue-600"></i> Mulai
                </a>
                <?php endif; ?>
                <?php if(!$isDone): ?>
                <a href="<?= BASE_URL ?>/public/proses_selesai.php?kode=<?= $t['KodeTugas'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors" onclick="return confirm('Tandai tugas ini sebagai selesai?')">
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
                <a href="<?= BASE_URL ?>/public/hapus.php?kode=<?= $t['KodeTugas'] ?>&type=tugas" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 transition-colors" onclick="return confirm('Yakin ingin menghapus tugas ini?')">
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
            <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Selesai</span>
        <?php elseif($isOverdue): ?>
            <span class="text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>Deadline: <?= date('d/m/Y', strtotime($t['Deadline'])) ?></span>
        <?php elseif($isTomorrow): ?>
            <span class="text-orange-600 font-semibold">
                <i class="fas fa-clock mr-1"></i>Deadline BESOK: <?= date('d/m/Y H:i', strtotime($t['Deadline'])) ?>
            </span>
        <?php else: ?>
            <span><i class="fas fa-calendar mr-1"></i>Deadline: <?= date('d/m/Y', strtotime($t['Deadline'])) ?></span>
        <?php endif; ?>
    </div>
</div>
