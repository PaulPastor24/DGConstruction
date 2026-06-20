<?php
// Direct database connection and migration script
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "dg_construction_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL statements to add approval workflow columns
$sqls = [
    "ALTER TABLE accomplishment_reports ADD COLUMN reviewed_by BIGINT UNSIGNED NULL AFTER submitted_by",
    "ALTER TABLE accomplishment_reports ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER reviewed_by",
    "ALTER TABLE accomplishment_reports ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER ai_status",
    "ALTER TABLE accomplishment_reports ADD COLUMN approval_remarks LONGTEXT NULL AFTER approval_status",
    "ALTER TABLE accomplishment_reports ADD COLUMN reviewed_at TIMESTAMP NULL AFTER approval_remarks",
    "ALTER TABLE accomplishment_reports ADD COLUMN approved_at TIMESTAMP NULL AFTER reviewed_at",
    "ALTER TABLE accomplishment_reports ADD COLUMN rejected_at TIMESTAMP NULL AFTER approved_at",
    "ALTER TABLE accomplishment_reports ADD FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE",
    "ALTER TABLE accomplishment_reports ADD FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE"
];

$errors = [];
$executed = 0;

foreach ($sqls as $sql) {
    if ($conn->query($sql) === TRUE) {
        $executed++;
        echo "✓ Executed: " . substr($sql, 0, 50) . "...\n";
    } else {
        // Check if column already exists
        if (strpos($conn->error, "Duplicate column") !== false || strpos($conn->error, "already exists") !== false) {
            echo "⚠ Skipped (already exists): " . substr($sql, 0, 50) . "...\n";
            $executed++;
        } else if (strpos($conn->error, "Duplicate key") !== false) {
            echo "⚠ Skipped (foreign key already exists): " . substr($sql, 0, 50) . "...\n";
            $executed++;
        } else {
            $errors[] = $sql . " | Error: " . $conn->error;
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
}

// Verify columns
echo "\n\nVerifying columns in accomplishment_reports:\n";
$result = $conn->query("DESCRIBE accomplishment_reports");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\n\n========================================\n";
echo "Summary: " . $executed . " operations completed\n";
if (count($errors) > 0) {
    echo "Errors: " . count($errors) . "\n";
    foreach ($errors as $error) {
        echo "  " . $error . "\n";
    }
} else {
    echo "✓ All operations completed successfully!\n";
}
echo "========================================\n";

$conn->close();
?>
