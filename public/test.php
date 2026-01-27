<?php
// Test database connection
echo "Testing Supabase PostgreSQL connection...\n\n";

$host = getenv('DB_HOST') ?: 'db.wtevayfmmvrbtevxsbwh.supabase.co';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_DATABASE') ?: 'postgres';
$user = getenv('DB_USERNAME') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: '26211810Emir';

echo "Host: $host\n";
echo "Database: $dbname\n";
echo "User: $user\n\n";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
    ]);

    echo "✓ Connection successful!\n\n";

    // Check if tables exist
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables found (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }

    if (empty($tables)) {
        echo "\n❌ NO TABLES FOUND! Please run the SQL script in Supabase SQL Editor.\n";
        echo "\n1. Open https://wtevayfmmvrbtevxsbwh.supabase.co\n";
        echo "2. Go to SQL Editor (left menu)\n";
        echo "3. Copy all contents from supabase_schema.sql file\n";
        echo "4. Paste and click RUN\n";
    } else {
        // Count products
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM products");
            $count = $stmt->fetchColumn();
            echo "\n✓ Products: $count\n";
        } catch (Exception $e) {
            echo "\n❌ Error querying products: " . $e->getMessage() . "\n";
        }
    }

} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
