<?php
// Function to recursively set permissions
function setPermissions($path, $dirPerm = 0755, $filePerm = 0644) {
    if (is_dir($path)) {
        chmod($path, $dirPerm);
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                setPermissions($path . '/' . $item, $dirPerm, $filePerm);
            }
        }
    } else {
        chmod($path, $filePerm);
    }
}

// Directories that need special permissions
$specialDirs = [
    'backups' => 0700, // More restrictive for backup directory
    'logs' => 0700,    // More restrictive for logs
    'config' => 0700,  // More restrictive for config files
];

// Set base permissions
$baseDir = __DIR__;
setPermissions($baseDir);

// Set special permissions for sensitive directories
foreach ($specialDirs as $dir => $perm) {
    $dirPath = $baseDir . '/' . $dir;
    if (file_exists($dirPath)) {
        setPermissions($dirPath, $perm, $perm);
    }
}

// Set specific file permissions
$files = [
    'backup.php' => 0700,
    'set_permissions.php' => 0700,
    'config/database.php' => 0600,
];

foreach ($files as $file => $perm) {
    $filePath = $baseDir . '/' . $file;
    if (file_exists($filePath)) {
        chmod($filePath, $perm);
    }
}

echo "Permissions set successfully!\n";
?> 