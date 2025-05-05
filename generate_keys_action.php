<?php
session_start();

$openssl_cnf_path = 'C:/xampp/php/extras/openssl/openssl.cnf';
putenv("OPENSSL_CONF=$openssl_cnf_path");

if (!file_exists($openssl_cnf_path)) {
    exit("Le fichier openssl.cnf est introuvable à l'emplacement : $openssl_cnf_path");
}

// Mot de passe pour le chiffrement de la clé privée
$password = "unMotDePasseSecurise!"; // Utilisez un mot de passe complexe et sécurisé ici

// Génération des clés RSA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate']) && $_POST['generate'] === 'true') {
    $config = [
        "config" => $openssl_cnf_path,
        "digest_alg" => "sha512",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];

    $res = openssl_pkey_new($config);
    if (!$res) {
        exit("Erreur lors de la génération des clés : " . openssl_error_string());
    }

    // Exporter la clé privée
    $privateKey = '';
    openssl_pkey_export($res, $privateKey, null, $config);

    // Chiffrement de la clé privée avec AES-256-CBC
    $encryptedPrivateKey = openssl_encrypt($privateKey, 'aes-256-cbc', $password, 0, '1234567890123456'); // IV de 16 bytes

    // Récupérer la clé publique
    $keyDetails = openssl_pkey_get_details($res);
    $publicKey = $keyDetails['key'];

    // Stocker les clés dans la session (clé privée chiffrée et clé publique)
    $_SESSION['encrypted_private_key'] = $encryptedPrivateKey;
    $_SESSION['public_key'] = $publicKey;
}

// Traitement du fichier et signature
$hash = '';
$signature = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileTmpName = $_FILES['file']['tmp_name'];

    if (is_uploaded_file($fileTmpName)) {
        // Lire le contenu du fichier
        $fileContent = file_get_contents($fileTmpName);

        // Calculer le hash du fichier
        $hash = hash('sha256', $fileContent);

        // Déchiffrement de la clé privée
        $decryptedPrivateKey = openssl_decrypt($_SESSION['encrypted_private_key'], 'aes-256-cbc', $password, 0, '1234567890123456');

        // Signer le hash avec la clé privée
        $privateKeyResource = openssl_pkey_get_private($decryptedPrivateKey);
        openssl_sign($hash, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);

        // Libérer la clé privée
        openssl_free_key($privateKeyResource);

        // Convertir la signature en base64 pour l'afficher
        $signature = base64_encode($signature);
    } else {
        echo "Erreur lors du téléchargement du fichier.";
    }
}

// Vérification de la signature
$verificationResult = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_signature']) && !empty($_POST['signature']) && isset($_FILES['file_to_verify'])) {
    // Vérifiez si un fichier a été téléchargé pour la vérification
    if (isset($_FILES['file_to_verify']['tmp_name']) && is_uploaded_file($_FILES['file_to_verify']['tmp_name'])) {
        // Lire le contenu du fichier téléchargé pour la vérification
        $fileToVerify = file_get_contents($_FILES['file_to_verify']['tmp_name']);
        
        // Recalculer le hash du fichier pour la vérification
        $hashToVerify = hash('sha256', $fileToVerify);

        // Décoder la signature de base64
        $providedSignature = base64_decode($_POST['signature']);
        $publicKey = openssl_pkey_get_public($_SESSION['public_key']);

        // Vérifier la signature
        $verificationResult = (openssl_verify($hashToVerify, $providedSignature, $publicKey, OPENSSL_ALGO_SHA256) === 1)
            ? "La signature est valide."
            : "La signature n'est pas valide.";

        openssl_free_key($publicKey);
    } else {
        $verificationResult = "Erreur : aucun fichier valide sélectionné pour la vérification.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de Clés RSA et Signature</title>
    <style>
        /* Style global */
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        h1, h2, h3 {
            text-align: center;
            color: #00bcd4;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: #1e1e1e;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        textarea, input[type="file"], button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border: none;
            border-radius: 4px;
        }

        textarea {
            background-color: #262626;
            color: #e0e0e0;
            border: 1px solid #333;
            resize: none;
        }

        input[type="file"] {
            background-color: #333;
            color: #e0e0e0;
            border: 1px solid #555;
            cursor: pointer;
        }

        button {
            background-color: #00bcd4;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #008c9e;
        }

        .drop-zone {
            border: 2px dashed #00bcd4;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            font-size: 16px;
            color: #8f8f8f;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .drop-zone.dragover {
            border-color: #00bcd4;
            background-color: #2a2a2a;
        }

        .file-info {
            margin-top: 10px;
            color: #fff;
        }

        .result {
            background: #262626;
            padding: 15px;
            border-radius: 4px;
            color: #e0e0e0;
            margin-top: 10px;
            overflow: auto;
        }

        .error {
            color: #f44336;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Générateur de Clés RSA</h1>

        <!-- Affichage de la clé privée chiffrée -->
        <?php if (isset($_SESSION['encrypted_private_key']) && isset($_SESSION['public_key'])): ?>
            <h3>Clé Privée (chiffrée) :</h3>
            <textarea readonly><?= htmlspecialchars($_SESSION['encrypted_private_key']); ?></textarea>

            <h3>Clé Publique :</h3>
            <textarea readonly><?= htmlspecialchars($_SESSION['public_key']); ?></textarea>
        <?php endif; ?>

        <!-- Formulaire de téléchargement pour signer un fichier -->
        <form id="upload-form" action="" method="POST" enctype="multipart/form-data">
            <div class="drop-zone" id="drop-zone">
                Glissez et déposez votre fichier ici ou cliquez pour parcourir
                <input id="file-input" type="file" name="file" accept="*" style="display: none;">
            </div>
            <div class="file-info" id="file-info"></div>
            <button type="submit">Signer le fichier</button>
        </form>

        <!-- Résultats de la signature -->
        <?php if ($hash && $signature): ?>
            <h3>Hash du fichier :</h3>
            <textarea readonly><?= htmlspecialchars($hash); ?></textarea>

            <h3>Signature (base64) :</h3>
            <textarea readonly><?= htmlspecialchars($signature); ?></textarea>
        <?php endif; ?>

        <!-- Formulaire de vérification de la signature -->
        <h2>Vérification de la signature</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="file_to_verify">Choisir le fichier à vérifier :</label>
            <input id="file_to_verify" name="file_to_verify" type="file" required>

            <label for="signature">Signature (base64) :</label>
            <textarea id="signature" name="signature" rows="4" required></textarea>

            <button type="submit" name="verify_signature">Vérifier la signature</button>
        </form>

        <!-- Résultat de la vérification -->
        <?php if ($verificationResult): ?>
            <h3>Résultat de la vérification :</h3>
            <p class="<?= $verificationResult === 'La signature est valide.' ? '' : 'error'; ?>">
                <?= $verificationResult; ?>
            </p>
        <?php endif; ?>
    </div>

    <script>
        // Code JavaScript pour gérer le drag-and-drop
        document.addEventListener("DOMContentLoaded", function() {
            const dropZone = document.getElementById("drop-zone");
            const fileInput = document.getElementById("file-input");
            const fileInfo = document.getElementById("file-info");

            dropZone.addEventListener("dragover", function(event) {
                event.preventDefault();
                dropZone.classList.add("dragover");
            });

            dropZone.addEventListener("dragleave", function() {
                dropZone.classList.remove("dragover");
            });

            dropZone.addEventListener("drop", function(event) {
                event.preventDefault();
                dropZone.classList.remove("dragover");
                const files = event.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    displayFileInfo(files[0]);
                }
            });

            dropZone.addEventListener("click", function() {
                fileInput.click();
            });

            fileInput.addEventListener("change", function() {
                if (fileInput.files.length > 0) {
                    displayFileInfo(fileInput.files[0]);
                }
            });

            function displayFileInfo(file) {
                const fileName = file.name;
                fileInfo.innerHTML = `<p><strong>Nom du fichier :</strong> ${fileName}</p>`;
            }
        });
    </script>

</body>
</html>
