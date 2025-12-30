<?php

namespace Tests\Unit\Model;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../model/Database.php';
require_once __DIR__ . '/../../../model/TaskModel.php';

class TaskModelTest extends TestCase
{
    private $testNIM;
    
    protected function setUp(): void
    {
        resetTestDatabase();
        $this->testNIM = createTestUser();
    }
    
    public function testAddTaskSuccess()
    {
        $data = [
            'nim' => $this->testNIM,
            'judul' => 'Test Task',
            'deskripsi' => 'Test Description',
            'deadline' => '2025-12-31 23:59:00'
        ];
        
        $result = addTask($data);
        
        $this->assertTrue($result);
    }
    
    public function testGetAllTasksByNIM()
    {
        // Add test tasks
        addTask([
            'nim' => $this->testNIM,
            'judul' => 'Task 1',
            'deskripsi' => 'Description 1',
            'deadline' => '2025-12-31 23:59:00'
        ]);
        
        addTask([
            'nim' => $this->testNIM,
            'judul' => 'Task 2',
            'deskripsi' => 'Description 2',
            'deadline' => '2025-12-31 23:59:00'
        ]);
        
        $tasks = getAllTasks($this->testNIM);
        
        $this->assertCount(2, $tasks);
        $this->assertEquals('Task 1', $tasks[0]['JudulTugas']);
        $this->assertEquals('Task 2', $tasks[1]['JudulTugas']);
    }
    
    public function testUpdateTaskSuccess()
    {
        // Add task first
        $conn = getTestConnection();
        $kode = 'TSK-' . time();
        $query = "INSERT INTO tugas (KodeTugas, NIM, JudulTugas, Deskripsi, Deadline, StatusTugas) 
                  VALUES (?, ?, 'Original', 'Original Desc', '2025-12-31 23:59:00', 'Aktif')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $kode, $this->testNIM);
        mysqli_stmt_execute($stmt);
        mysqli_close($conn);
        
        // Update task
        $data = [
            'judul' => 'Updated Task',
            'deskripsi' => 'Updated Description',
            'deadline' => '2025-12-31 23:59:00'
        ];
        
        $result = updateTask($kode, $data);
        
        $this->assertTrue($result['success']);
    }
    
    public function testDeleteTaskSuccess()
    {
        // Add task first
        $conn = getTestConnection();
        $kode = 'TSK-' . time();
        $query = "INSERT INTO tugas (KodeTugas, NIM, JudulTugas, Deskripsi, Deadline, StatusTugas) 
                  VALUES (?, ?, 'Test Task', 'Test Desc', '2025-12-31 23:59:00', 'Aktif')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $kode, $this->testNIM);
        mysqli_stmt_execute($stmt);
        mysqli_close($conn);
        
        // Delete task
        $result = deleteTask($kode);
        
        $this->assertTrue($result['success']);
    }
    
    public function testMarkTaskCompleted()
    {
        // Add task first
        $conn = getTestConnection();
        $kode = 'TSK-' . time();
        $query = "INSERT INTO tugas (KodeTugas, NIM, JudulTugas, Deskripsi, Deadline, StatusTugas) 
                  VALUES (?, ?, 'Test Task', 'Test Desc', '2025-12-31 23:59:00', 'Aktif')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $kode, $this->testNIM);
        mysqli_stmt_execute($stmt);
        mysqli_close($conn);
        
        // Mark as completed
        $result = markCompleted($kode);
        
        $this->assertTrue($result['success']);
        
        // Verify status changed
        $conn = getTestConnection();
        $checkQuery = "SELECT StatusTugas FROM tugas WHERE KodeTugas = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $kode);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $task = mysqli_fetch_assoc($result);
        mysqli_close($conn);
        
        $this->assertEquals('Selesai', $task['StatusTugas']);
    }
}
