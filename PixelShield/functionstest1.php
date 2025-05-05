<?php

// Check if a file exists
function fileExists($filepath) {
    return file_exists($filepath);
}

// Save an uploaded file to the server
function saveUploadedFile($file, $destinationDir) {
    if (!file_exists($destinationDir)) {
        mkdir($destinationDir, 0755, true); // Create directory if it doesn't exist
    }

    // Ensure safe file name to avoid directory traversal
    $safeFilename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
    $destinationPath = $destinationDir . $safeFilename;

    if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
        return $destinationPath;
    }
    return false;
}

// Load an image into memory
function loadImage($imagePath) {
    $imageInfo = getimagesize($imagePath);
    $imageType = $imageInfo[2];

    switch ($imageType) {
        case IMAGETYPE_JPEG:
            return imagecreatefromjpeg($imagePath);
        case IMAGETYPE_PNG:
            return imagecreatefrompng($imagePath);
        default:
            throw new Exception("Unsupported image type. Please use JPEG or PNG.");
    }
}

// Save an image from memory
function saveImage($image, $outputPath, $imageType) {
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            imagejpeg($image, $outputPath);
            break;
        case IMAGETYPE_PNG:
            imagepng($image, $outputPath);
            break;
        default:
            throw new Exception("Unsupported image type.");
    }
}

// Encrypt an image
function encryptImage($inputPath, $outputPath, $encryptionKey) {
    $imageData = file_get_contents($inputPath);

    $iv = substr($encryptionKey, 0, 16); // Use the first 16 bytes of the key as the IV
    $encryptedData = openssl_encrypt($imageData, 'aes-256-cbc', $encryptionKey, 0, $iv);

    if ($encryptedData === false) {
        throw new Exception("Failed to encrypt the image.");
    }

    file_put_contents($outputPath, base64_encode($encryptedData));
}

// Decrypt an image
function decryptImage($inputPath, $outputPath, $encryptionKey) {
    $encodedData = file_get_contents($inputPath);
    $encryptedData = base64_decode($encodedData);

    $iv = substr($encryptionKey, 0, 16); // Use the first 16 bytes of the key as the IV
    $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $encryptionKey, 0, $iv);

    if ($decryptedData === false) {
        throw new Exception("Failed to decrypt the image.");
    }

    file_put_contents($outputPath, $decryptedData);
}

// Calculate a hash for an image
function hashageCalculator($imagePath) {
    $image = loadImage($imagePath);
    $width = imagesx($image);
    $height = imagesy($image);

    $pixels = [];
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $pixels[] = imagecolorat($image, $x, $y);
        }
    }

    return hash("sha512", implode("", $pixels));
}

// Convert the hash to an encryption key
function convertHash($hash) {
    return substr(hash('sha256', $hash), 0, 32); // Return a 32-byte AES-256 key
}

// Download an image file
function downloadImage($filePath) {
    if (!file_exists($filePath)) {
        die("File not found.");
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    flush();
    readfile($filePath);
    exit;
}
