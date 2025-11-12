<?php
session_start();

// Kiểm tra đăng nhập admin
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Yêu cầu đăng nhập
function requireLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: ../login.php");
        exit;
    }
}

// Lấy thông tin admin hiện tại
function getCurrentAdmin($conn) {
    if (isAdminLoggedIn()) {
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return null;
}

if (isset($_SESSION['admin_id']) && !isset($_SESSION['employee_id'])) {
    // Check if admin exists in employees table
    $stmt = $conn->prepare("SELECT id FROM employees WHERE email = (SELECT email FROM admin_users WHERE id = ?)");
    $stmt->execute([$_SESSION['admin_id']]);
    $result = $stmt->fetch();
    
    if ($result) {
        $_SESSION['employee_id'] = $result['id'];
    } else {
        // Get admin info and manager role
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        
        $stmt = $conn->prepare("SELECT id FROM roles WHERE name = 'Manager'");
        $stmt->execute();
        $role = $stmt->fetch();
        
        if ($role) {
            // Create employee record for admin
            $stmt = $conn->prepare("
                INSERT INTO employees (role_id, full_name, email, password, status) 
                VALUES (?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $role['id'],
                $admin['fullname'], 
                $admin['email'],
                $admin['password']
            ]);
            $_SESSION['employee_id'] = $conn->lastInsertId();
        }
    }
}
?>