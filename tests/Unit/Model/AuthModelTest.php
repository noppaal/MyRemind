<?php

namespace Tests\Unit\Model;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../model/Database.php';
require_once __DIR__ . '/../../../model/AuthModel.php';

class AuthModelTest extends TestCase
{
    private $testNIM;
    
    protected function setUp(): void
    {
        resetTestDatabase();
        $this->testNIM = '1301200001';
    }
    
    public function testRegisterUserSuccess()
    {
        $data = [
            'nim' => '1301200001',
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];
        
        $result = registerUser($data);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Registrasi berhasil!', $result['message']);
    }
    
    public function testRegisterUserDuplicateNIM()
    {
        // Create first user
        createTestUser('1301200001', 'Test User', 'test@example.com');
        
        // Try to register with same NIM
        $data = [
            'nim' => '1301200001',
            'nama' => 'Another User',
            'email' => 'another@example.com',
            'password' => 'password123'
        ];
        
        $result = registerUser($data);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('NIM sudah terdaftar!', $result['message']);
    }
    
    public function testRegisterUserDuplicateEmail()
    {
        // Create first user
        createTestUser('1301200001', 'Test User', 'test@example.com');
        
        // Try to register with same email
        $data = [
            'nim' => '1301200002',
            'nama' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        $result = registerUser($data);
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Email sudah terdaftar!', $result['message']);
    }
    
    public function testGetUserByNIMSuccess()
    {
        createTestUser($this->testNIM, 'Test User', 'test@example.com');
        
        $user = getUserByNIM($this->testNIM);
        
        $this->assertNotNull($user);
        $this->assertEquals($this->testNIM, $user['NIM']);
        $this->assertEquals('Test User', $user['Nama']);
        $this->assertEquals('test@example.com', $user['Email']);
    }
    
    public function testGetUserByNIMNotFound()
    {
        $user = getUserByNIM('9999999999');
        
        $this->assertNull($user);
    }
    
    public function testGetUserByEmailSuccess()
    {
        createTestUser($this->testNIM, 'Test User', 'test@example.com');
        
        $user = getUserByEmail('test@example.com');
        
        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user['Email']);
    }
    
    public function testValidateLoginSuccess()
    {
        createTestUser($this->testNIM, 'Test User', 'test@example.com');
        
        $result = validateLogin('test@example.com', 'password123');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($this->testNIM, $result['user']['NIM']);
    }
    
    public function testValidateLoginWrongPassword()
    {
        createTestUser($this->testNIM, 'Test User', 'test@example.com');
        
        $result = validateLogin('test@example.com', 'wrongpassword');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Password salah!', $result['message']);
    }
    
    public function testValidateLoginUserNotFound()
    {
        $result = validateLogin('nonexistent@example.com', 'password123');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Email tidak terdaftar!', $result['message']);
    }
}
