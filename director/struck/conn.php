<?php
$host = 'localhost'; // เช่น 'localhost' หรือ '127.0.0.1'
$dbname = 'innovation'; // ชื่อฐานข้อมูลที่คุณต้องการเชื่อมต่อ
$username = 'root'; // ชื่อผู้ใช้ของ MySQL
$password = ''; // รหัสผ่านของ MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "เชื่อมต่อฐานข้อมูลสำเร็จ!";
} catch (PDOException $e) {
    //die("เกิดข้อผิดพลาด: " . $e->getMessage());
}
?>
