<?php

namespace Tests\Unit\Model;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../model/Database.php';
require_once __DIR__ . '/../../../model/GroupModel.php';

class GroupModelTest extends TestCase
{
    private $testNIM;
    
    protected function setUp(): void
    {
        resetTestDatabase();
        $this->testNIM = createTestUser();
    }
    
    public function testCreateGroupSuccess()
    {
        $kodeGrup = 'GRP-' . time();
        $data = [
            'kode_grup' => $kodeGrup,
            'nama_grup' => 'Test Group',
            'deskripsi' => 'Test Description',
            'creator_nim' => $this->testNIM
        ];
        
        $result = createGroup($data);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('kode_grup', $result);
    }
    
    public function testGetAllGroupsByNIM()
    {
        // Create test groups
        createGroup([
            'kode_grup' => 'GRP-' . time() . '-' . mt_rand(1000, 9999),
            'nama_grup' => 'Group 1',
            'deskripsi' => 'Desc 1',
            'creator_nim' => $this->testNIM
        ]);
        
        sleep(1); // Ensure unique timestamp
        createGroup([
            'kode_grup' => 'GRP-' . time() . '-' . mt_rand(1000, 9999),
            'nama_grup' => 'Group 2',
            'deskripsi' => 'Desc 2',
            'creator_nim' => $this->testNIM
        ]);
        
        $groups = getAllGroups($this->testNIM);
        
        $this->assertGreaterThanOrEqual(2, count($groups));
    }
    
    public function testJoinByInviteSuccess()
    {
        // Create group
        $kodeGrup = 'GRP-' . time() . '-' . mt_rand(1000, 9999);
        $groupResult = createGroup([
            'kode_grup' => $kodeGrup,
            'nama_grup' => 'Test Group',
            'deskripsi' => 'Test Description',
            'creator_nim' => $this->testNIM
        ]);
        
        // Create another user
        $newNIM = createTestUser('1301200002', 'User 2', 'user2@example.com');
        
        // Join group
        $result = joinByInvite($kodeGrup, $newNIM);
        
        $this->assertTrue($result['success']);
    }
    
    public function testJoinByInviteAlreadyMember()
    {
        // Create group (creator is auto-added as owner)
        $kodeGrup = 'GRP-' . time();
        $groupResult = createGroup([
            'kode_grup' => $kodeGrup,
            'nama_grup' => 'Test Group',
            'deskripsi' => 'Test Description',
            'creator_nim' => $this->testNIM
        ]);
        
        // Try to join again
        $result = joinByInvite($kodeGrup, $this->testNIM);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('sudah menjadi anggota', $result['message']);
    }
    
    public function testJoinByInviteInvalidCode()
    {
        $result = joinByInvite('INVALID-CODE', $this->testNIM);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak valid', $result['message']);
    }
    
    public function testGetGroupMembers()
    {
        // Create group
        $kodeGrup = 'GRP-' . time();
        $groupResult = createGroup([
            'kode_grup' => $kodeGrup,
            'nama_grup' => 'Test Group',
            'deskripsi' => 'Test Description',
            'creator_nim' => $this->testNIM
        ]);
        
        // Add another member
        $newNIM = createTestUser('1301200002', 'User 2', 'user2@example.com');
        joinByInvite($kodeGrup, $newNIM);
        
        $members = getGroupMembers($kodeGrup);
        
        $this->assertCount(2, $members);
    }
}
