<?php
// --- Helper Code untuk Menampilkan 1 Baris Tugas ---
$deadlineDate = new DateTime($t['Deadline']);
$now = new DateTime();
$isOverdue = $now > $deadlineDate;
$diff = $now->diff($deadlineDate);
$daysLeft = $diff->days;

// Cek Status Selesai
$isDone = ($t['StatusTugas'] == 'Selesai');

if ($isDone) {
    $statusText = "Selesai";
    $statusClass = "bg-success";
    $containerClass = "task-done"; 
} else {
    $statusText = $isOverdue ? "Terlewat" : ($daysLeft <= 3 ? "Mendesak" : "Aktif");
    $statusClass = $isOverdue ? "badge-overdue" : ($daysLeft <= 3 ? "bg-danger" : "bg-light text-dark border");
    $containerClass = "";
}

$namaMK = empty($t['NamaMK']) ? 'Tugas Umum' : $t['NamaMK'];
?>

<div class="list-card d-flex justify-content-between align-items-start btn-view-detail <?= $containerClass ?>"
     data-judul="<?= htmlspecialchars($t['JudulTugas']) ?>"
     data-mk="<?= htmlspecialchars($namaMK) ?>"
     data-deadline="<?= date('d M Y, H:i', strtotime($t['Deadline'])) ?>"
     data-desc="<?= htmlspecialchars($t['Deskripsi'] ?? '-') ?>"
     data-status="<?= $statusText ?>">
    
    <div class="flex-grow-1">
        <h6 class="fw-bold mb-1"><?= htmlspecialchars($t['JudulTugas']) ?></h6>
        <p class="text-muted small mb-1">
            <i class="fa-solid fa-book-open me-1"></i> <?= htmlspecialchars($namaMK) ?>
        </p>
        
        <small class="text-muted">
            <i class="fa-regular fa-clock me-1"></i> 
            <?php if($isDone): ?>
                <span class="text-success">Selesai pada <?= date('d M Y') ?></span>
            <?php elseif($isOverdue): ?>
                <span class="text-danger fw-bold">Terlewat sejak <?= date('d M Y, H:i', strtotime($t['Deadline'])) ?></span>
            <?php else: ?>
                Deadline: <?= date('d M Y, H:i', strtotime($t['Deadline'])) ?> 
            <?php endif; ?>
        </small>
    </div>

    <div class="d-flex flex-column align-items-end gap-2 ms-3">
        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
        
        <div class="d-flex gap-1">
            <?php if(!$isDone): ?>
            <a href="proses_selesai.php?id=<?= $t['KodeTugas'] ?>" class="btn btn-sm btn-light text-success border-0" title="Tandai Selesai" onclick="return confirm('Tandai tugas ini sebagai selesai?')">
                <i class="fa-solid fa-check"></i>
            </a>
            <?php endif; ?>

            <button class="btn btn-sm btn-light text-primary btn-edit-tugas" 
                data-id="<?= $t['KodeTugas'] ?>" 
                data-judul="<?= htmlspecialchars($t['JudulTugas']) ?>" 
                data-deadline="<?= date('Y-m-d', strtotime($t['Deadline'])) ?>" 
                data-deskripsi="<?= htmlspecialchars($t['Deskripsi']) ?>">
                <i class="fa-solid fa-pen"></i>
            </button>
            
            <a href="hapus.php?id=<?= $t['KodeTugas'] ?>&type=tugas" class="btn btn-sm btn-light text-danger border-0" onclick="return confirm('Yakin ingin menghapus tugas ini?')">
                <i class="fa-solid fa-trash"></i>
            </a>
        </div>
    </div>
</div>