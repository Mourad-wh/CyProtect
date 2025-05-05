<?php
header('Content-Type: text/plain');

// Vérifier si le fichier a été envoyé
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die("Erreur : Aucun fichier envoyé.");
}

// Charger la clé privée
$keyPath = C:\xampp\htdocs\web app\keys . '/keys/private_key.pem';
if (!file_exists($keyPath)) {
    die("Erreur : Clé privée introuvable. Veuillez en générer une.");
}
$privateKey = file_get_contents($keyPath);

// Créer un hash du fichier
$fileContent = file_get_contents($_FILES['file']['tmp_name']);
$hash = hash('sha256', $fileContent);

// Signer le hash
if (!openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
    die("Erreur : Échec de la signature.");
}

// Résultat de la signature
echo "Hash du fichier : $hash\n";
echo "Signature (base64) : " . base64_encode($signature) . "\n";
?>
