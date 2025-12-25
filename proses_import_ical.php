<?php
include 'config.php';

// Pastikan user login
if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}

$nim = $_SESSION['nim'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ical_url'])) {
    $ical_url = trim($_POST['ical_url']);
    
    // Validate URL
    if (!filter_var($ical_url, FILTER_VALIDATE_URL)) {
        header("Location: index.php?msg=invalid_url");
        exit;
    }
    
    // Check if URL ends with .ics or contains export_execute.php (LMS Telkom format)
    if (!preg_match('/\.ics(\?|$)/', $ical_url) && !preg_match('/export_execute\.php/', $ical_url)) {
        header("Location: index.php?msg=invalid_ical_format");
        exit;
    }
    
    // Fetch iCal data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ical_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    
    $ical_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code != 200 || empty($ical_data)) {
        header("Location: index.php?msg=fetch_failed");
        exit;
    }
    
    // Parse iCal data
    $events = parseICalData($ical_data);
    
    if (empty($events)) {
        header("Location: index.php?msg=no_events");
        exit;
    }
    
    // Import events to database
    $imported_count = 0;
    $skipped_count = 0;
    
    foreach ($events as $event) {
        // Check if event already exists (by title and date)
        $check_query = "SELECT COUNT(*) as count FROM tugas 
                       WHERE NIM = '$nim' 
                       AND JudulTugas = '" . mysqli_real_escape_string($conn, $event['summary']) . "' 
                       AND DATE(Deadline) = '" . $event['start_date'] . "'";
        $check_result = mysqli_query($conn, $check_query);
        $exists = mysqli_fetch_assoc($check_result)['count'] > 0;
        
        if ($exists) {
            $skipped_count++;
            continue;
        }
        
        // Insert as task
        $summary = mysqli_real_escape_string($conn, $event['summary']);
        $description = mysqli_real_escape_string($conn, $event['description']);
        $deadline = $event['start_datetime'];
        
        // Generate unique KodeTugas (LMS-timestamp-random)
        $kode_tugas = 'LMS-' . time() . '-' . substr(md5(uniqid()), 0, 6);
        
        // Insert with KodeTugas and KodeMK (set to GENERAL for LMS imports)
        $insert_query = "INSERT INTO tugas (KodeTugas, NIM, KodeMK, JudulTugas, Deskripsi, Deadline, StatusTugas) 
                        VALUES ('$kode_tugas', '$nim', 'GENERAL', '$summary', '$description', '$deadline', 'Aktif')";
        
        if (mysqli_query($conn, $insert_query)) {
            $imported_count++;
        }
        
        // Small delay to ensure unique timestamps
        usleep(10000); // 10ms delay
    }
    
    // Save iCal URL for future syncs (optional - requires ical_sync table)
    // Uncomment if you create the ical_sync table
    /*
    $save_url_query = "INSERT INTO ical_sync (NIM, ical_url, last_sync, sync_status) 
                      VALUES ('$nim', '" . mysqli_real_escape_string($conn, $ical_url) . "', NOW(), 'success')
                      ON DUPLICATE KEY UPDATE ical_url = VALUES(ical_url), last_sync = NOW(), sync_status = 'success'";
    mysqli_query($conn, $save_url_query);
    */
    
    header("Location: index.php?msg=import_success&imported=$imported_count&skipped=$skipped_count");
    exit;
} else {
    header("Location: index.php");
    exit;
}

// Function to parse iCal data
function parseICalData($ical_data) {
    $events = [];
    
    // Split by VEVENT
    preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $ical_data, $matches);
    
    foreach ($matches[1] as $event_data) {
        $event = [];
        
        // Extract SUMMARY (title)
        if (preg_match('/SUMMARY:(.*)/i', $event_data, $summary_match)) {
            $event['summary'] = trim($summary_match[1]);
        } else {
            $event['summary'] = 'Event Tanpa Judul';
        }
        
        // Extract DESCRIPTION
        if (preg_match('/DESCRIPTION:(.*)/i', $event_data, $desc_match)) {
            $event['description'] = trim($desc_match[1]);
        } else {
            $event['description'] = '';
        }
        
        // Extract DTSTART (start date/time)
        if (preg_match('/DTSTART[;:]([^\r\n]*)/i', $event_data, $start_match)) {
            $start_raw = trim($start_match[1]);
            $event['start_datetime'] = parseICalDateTime($start_raw);
            $event['start_date'] = date('Y-m-d', strtotime($event['start_datetime']));
        } else {
            continue; // Skip events without start date
        }
        
        // Extract DTEND (end date/time) - optional
        if (preg_match('/DTEND[;:]([^\r\n]*)/i', $event_data, $end_match)) {
            $end_raw = trim($end_match[1]);
            $event['end_datetime'] = parseICalDateTime($end_raw);
        }
        
        // Extract LOCATION - optional
        if (preg_match('/LOCATION:(.*)/i', $event_data, $loc_match)) {
            $event['location'] = trim($loc_match[1]);
        }
        
        $events[] = $event;
    }
    
    return $events;
}

// Function to parse iCal datetime format
function parseICalDateTime($ical_datetime) {
    // Remove VALUE=DATE: prefix if exists
    $ical_datetime = preg_replace('/^[^:]*:/', '', $ical_datetime);
    
    // iCal format: 20250126T080000Z or 20250126
    $ical_datetime = trim($ical_datetime);
    
    // Check if it's a date-only format (8 characters)
    if (strlen($ical_datetime) == 8) {
        // Format: YYYYMMDD
        $year = substr($ical_datetime, 0, 4);
        $month = substr($ical_datetime, 4, 2);
        $day = substr($ical_datetime, 6, 2);
        return "$year-$month-$day 23:59:59";
    }
    
    // Full datetime format: YYYYMMDDTHHMMSSZ
    if (preg_match('/(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})/', $ical_datetime, $matches)) {
        $year = $matches[1];
        $month = $matches[2];
        $day = $matches[3];
        $hour = $matches[4];
        $minute = $matches[5];
        $second = $matches[6];
        
        // Convert to MySQL datetime format
        return "$year-$month-$day $hour:$minute:$second";
    }
    
    // Fallback
    return date('Y-m-d H:i:s');
}
?>
