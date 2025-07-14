<?php

if (! function_exists('removeDirectory')) {
    function removeDirectory($dir): bool {
        // Remove trailing slash if exists
        $dir = rtrim($dir, '/\\');

        // Check if the path exists
        if (!is_dir($dir)) {
            return false;
        }

        // Get the list of files and directories inside the directory
        $files = glob($dir . '/*');

        // Loop through each file and directory
        foreach ($files as $file) {
            // If it's a directory, recursively remove it
            if (is_dir($file)) {
                removeDirectory($file);
            } else {
                // If it's a file, delete it
                unlink($file);
            }
        }

        // Remove the directory itself
        @rmdir($dir);

        return true;
    }
}

// Function to extract file extension from format
function getFileExtensionFromFormat($format) {
    switch ($format) {
        case 'image/jpeg':
            return 'jpeg';
        case 'image/png':
            return 'png';
        // Add more cases for other image formats if needed
        default:
            return ''; // Return empty string if format is unknown or unsupported
    }
}
