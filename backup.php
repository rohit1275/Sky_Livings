<?php
require_once 'config/database.php';

// Configuration
$backupDir = __DIR__ . '/backups';
$maxBackups = 5; // Number of backups to keep

// Create backup directory if it doesn't exist
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Generate backup filename with timestamp
$timestamp = date('Y-m-d_H-i-s');
$dbBackupFile = $backupDir . "/database_backup_{$timestamp}.sql";
$filesBackupFile = $backupDir . "/files_backup_{$timestamp}.zip";

// Backup database
try {
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $output = '';
    foreach ($tables as $table) {
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        $output .= $create[1] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `$table`");
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $values = array_map(function($value) use ($pdo) {
                return $pdo->quote($value);
            }, $row);
            $output .= "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n";
        }
        $output .= "\n";
    }

    file_put_contents($dbBackupFile, $output);
} catch (PDOException $e) {
    error_log("Database backup failed: " . $e->getMessage());
    die("Database backup failed");
}

// Backup files
try {
    $zip = new ZipArchive();
    if ($zip->open($filesBackupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        // Add files to zip
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(__DIR__) + 1);
                
                // Skip backup directory and large files
                if (strpos($relativePath, 'backups/') === 0 || 
                    strpos($relativePath, 'node_modules/') === 0 ||
                    filesize($filePath) > 100 * 1024 * 1024) { // Skip files larger than 100MB
                    continue;
                }
                
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    } else {
        throw new Exception("Cannot create zip file");
    }
} catch (Exception $e) {
    error_log("Files backup failed: " . $e->getMessage());
    die("Files backup failed");
}

// Clean up old backups
$backups = glob($backupDir . "/*");
if (count($backups) > $maxBackups) {
    usort($backups, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    for ($i = $maxBackups; $i < count($backups); $i++) {
        unlink($backups[$i]);
    }
}

echo "Backup completed successfully!\n";
echo "Database backup: " . basename($dbBackupFile) . "\n";
echo "Files backup: " . basename($filesBackupFile) . "\n";
?> 