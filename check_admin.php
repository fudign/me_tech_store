<?php

echo "Checking admin user...\n";

$host = 'ep-divine-dawn-ahbd0wsd-pooler.c-3.us-east-1.aws.neon.tech';
$port = '5432';
$dbname = 'neondb';
$user = 'neondb_owner';
$password = 'npg_8YQS3eqyoLcw';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options='endpoint=ep-divine-dawn-ahbd0wsd'";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Check admin user
    $stmt = $pdo->query("SELECT id, name, email, is_admin, created_at FROM users WHERE email = 'admin@metech.kg'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "✓ Admin user found:\n";
        print_r($admin);

        // Test password hash
        echo "\nTesting password 'admin123'...\n";
        $stmt = $pdo->query("SELECT password FROM users WHERE email = 'admin@metech.kg'");
        $storedHash = $stmt->fetchColumn();

        echo "Stored hash: " . substr($storedHash, 0, 20) . "...\n";

        // Generate new hash for testing
        $newHash = password_hash('admin123', PASSWORD_BCRYPT);
        echo "New hash would be: " . substr($newHash, 0, 20) . "...\n";

        // Verify the stored hash
        if (password_verify('admin123', $storedHash)) {
            echo "✓ Password verification: SUCCESS\n";
        } else {
            echo "✗ Password verification: FAILED\n";
            echo "Updating password...\n";
            $pdo->exec("UPDATE users SET password = '$newHash' WHERE email = 'admin@metech.kg'");
            echo "✓ Password updated\n";
        }
    } else {
        echo "✗ Admin user NOT found. Creating...\n";
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->exec("INSERT INTO users (name, email, password, is_admin) VALUES ('Admin', 'admin@metech.kg', '$hash', TRUE)");
        echo "✓ Admin user created\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
