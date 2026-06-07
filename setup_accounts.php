<?php
require_once 'includes/db.php';

$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$staff_password = password_hash('staff123', PASSWORD_DEFAULT);

try {
    // Insert Admin
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['System Admin', 'admin@waves.com', '0000000000', $admin_password, 'admin', 'active']);
    echo "Admin account created! Email: admin@waves.com | Password: admin123<br>";

    // Insert Staff
    $stmt->execute(['Support Agent', 'staff@waves.com', '1111111111', $staff_password, 'staff', 'active']);
    echo "Staff account created! Email: staff@waves.com | Password: staff123<br>";

    echo "<br><strong>DELETE THIS FILE NOW for security!</strong>";
} catch(PDOException $e) {
    echo "Error (maybe accounts already exist): " . $e->getMessage();
}
?>