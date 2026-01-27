<?php

echo "Connecting to Supabase PostgreSQL...\n";

$host = 'ep-divine-dawn-ahbd0wsd-pooler.c-3.us-east-1.aws.neon.tech';
$port = '5432';
$dbname = 'neondb';
$user = 'neondb_owner';
$password = 'npg_8YQS3eqyoLcw';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;options='endpoint=ep-divine-dawn-ahbd0wsd'";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 30,
    ]);

    echo "✓ Connected successfully!\n\n";

    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/neon_schema.sql');

    if (!$sql) {
        die("ERROR: Could not read supabase_schema.sql file\n");
    }

    echo "Executing SQL schema...\n";

    // Execute the SQL
    $pdo->exec($sql);

    echo "✓ Schema created successfully!\n\n";

    // Verify tables were created
    echo "Verifying tables...\n";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables created:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }

    // Count products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();
    echo "\n✓ Products inserted: $count\n";

    // Count categories
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $count = $stmt->fetchColumn();
    echo "✓ Categories inserted: $count\n";

    // Count settings
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    $count = $stmt->fetchColumn();
    echo "✓ Settings inserted: $count\n";

    echo "\n🎉 Database setup completed successfully!\n";

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
