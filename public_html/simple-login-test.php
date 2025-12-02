<?php
// Simple login test without the framework

// Session config BEFORE starting
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
session_start();



// Database connection
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=perruftn_club;charset=utf8mb4',
        'perruftn_clubu',
        'MPCEW9MradnuXCaG',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Get admin from database
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        // Login successful
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['user_type'] = 'admin';
        
        echo "<h1>Login Successful!</h1>";
        echo "<p>Session Data:</p>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        echo "<a href='/admin'>Go to Admin Dashboard</a>";
        exit;
    } else {
        echo "<p style='color: red;'>Invalid credentials</p>";
    }
}
?>

<h1>Simple Login Test</h1>
<form method="POST">
    <p>
        <label>Email:</label><br>
        <input type="email" name="email" value="mit@w3systems.net" required>
    </p>
    <p>
        <label>Password:</label><br>
        <input type="password" name="password" required>
    </p>
    <p>
        <button type="submit">Test Login</button>
    </p>
</form>

<h2>Current Session:</h2>
<pre><?= print_r($_SESSION, true) ?></pre>