<?php
// Inclure les fonctions nécessaires
include 'functionstest1.php';

// Initialisation de variables
$message = "";
$step = isset($_POST['step']) ? $_POST['step'] : "start";
$imagePath = "";
$encryptionKey = "";
$aesKey = "";

// Fonction pour générer une clé AES
function generateAESKey($length = 32) {
    return bin2hex(random_bytes($length));
}

// Fonction pour chiffrer une clé avec AES
function encryptWithAES($data, $aesKey) {
    $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $aesKey, 0, $iv);
    return base64_encode($iv . $encrypted);
}

// Fonction pour déchiffrer une clé avec AES
function decryptWithAES($encryptedData, $aesKey) {
    $data = base64_decode($encryptedData);
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $aesKey, 0, $iv);
}

// Gestion des étapes du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    switch ($step) {
        case "encrypt":
            $uploadedFile = $_FILES['image'] ?? null;

            if ($uploadedFile && $uploadedFile['error'] === 0) {
                // Save uploaded file to the server
                $imagePath = saveUploadedFile($uploadedFile, 'uploads/');
                if ($imagePath) {
                    // Generate the hash of the image and convert it to an encryption key
                    $hash = hashageCalculator($imagePath);
                    $encryptionKey = convertHash($hash); // Convert hash to encryption key

                    // Generate an AES key
                    $aesKey = generateAESKey();

                    // Encrypt the encryption key using the AES key
                    $encryptedKey = encryptWithAES($encryptionKey, $aesKey);

                    // Save the encrypted key and AES key to a text file
                    $keyFilePath = 'uploads/encryption_key_' . basename($imagePath) . '.txt';
                    file_put_contents($keyFilePath, $encryptedKey . "\n" . $aesKey);

                    // Define the output path for the encrypted image
                    $outputPath = 'uploads/encrypted_' . basename($imagePath);

                    // Encrypt the image using the generated encryption key
                    encryptImage($imagePath, $outputPath, $encryptionKey);

                    // Prepare success message with download links
                    $message = "Image chiffrée avec succès !<br>";
                    $message .= "<a href='$outputPath' download>Télécharger l'image chiffrée</a><br>";
                    $message .= "<a href='$keyFilePath' download>Télécharger la clé de chiffrement</a>";
                } else {
                    $message = "Erreur lors du téléchargement de l'image.";
                }
            } else {
                $message = "Veuillez sélectionner une image valide.";
            }
            break;

        case "decrypt":
            $uploadedFile = $_FILES['image'] ?? null;
            $keyFile = $_FILES['key_file'] ?? null; // Get the key file from the form

            if ($uploadedFile && $uploadedFile['error'] === 0 && $keyFile && $keyFile['error'] === 0) {
                // Save uploaded image and key file to the server
                $imagePath = saveUploadedFile($uploadedFile, 'uploads/');
                $keyFilePath = saveUploadedFile($keyFile, 'uploads/');

                if ($imagePath && $keyFilePath) {
                    // Read the encrypted key and AES key from the key file
                    $keyFileContent = file($keyFilePath, FILE_IGNORE_NEW_LINES);
                    $encryptedKey = trim($keyFileContent[0]);
                    $aesKey = trim($keyFileContent[1]);

                    // Decrypt the encryption key using the AES key
                    $encryptionKey = decryptWithAES($encryptedKey, $aesKey);

                    // Define the output path for the decrypted image
                    $outputPath = 'uploads/decrypted_' . basename($imagePath);

                    // Decrypt the image using the provided encryption key
                    decryptImage($imagePath, $outputPath, $encryptionKey);

                    // Prepare success message with a download link
                    $message = "Image déchiffrée avec succès !<br><a href='$outputPath' download>Télécharger l'image déchiffrée</a>";
                } else {
                    $message = "Erreur lors du téléchargement de l'image ou du fichier de clé.";
                }
            } else {
                $message = "Veuillez sélectionner une image valide et un fichier de clé.";
            }
            break;

        default:
            $message = "Action invalide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chiffrement et Déchiffrement d'Images</title>
</head>
<body>
    <h1>Chiffrement et Déchiffrement d'Images</h1>

    <?php if ($step === "start"): ?>
        <h2>Choisissez une action :</h2>
        <form method="POST">
            <button name="step" value="encrypt">Chiffrer une image</button>
            <button name="step" value="decrypt">Déchiffrer une image</button>
        </form>

    <?php elseif ($step === "encrypt"): ?>
        <h2>Chiffrement d'une image</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="image">Sélectionnez une image :</label>
            <input type="file" name="image" id="image" required>
            <button type="submit">Chiffrer</button>
            <input type="hidden" name="step" value="encrypt">
        </form>

    <?php elseif ($step === "decrypt"): ?>
        <h2>Déchiffrement d'une image</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="image">Sélectionnez l'image chiffrée :</label>
            <input type="file" name="image" id="image" required>
            <label for="key_file">Sélectionnez le fichier contenant la clé de chiffrement :</label>
            <input type="file" name="key_file" id="key_file" required>
            <button type="submit">Déchiffrer</button>
            <input type="hidden" name="step" value="decrypt">
        </form>
    <?php endif; ?>

    <p style="color: blue;">
        <?= $message ?>
    </p>
</body>
</html>
