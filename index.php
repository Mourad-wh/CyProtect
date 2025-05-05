<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de Clés RSA</title>
</head>
<body>

    <h1>Générer des clés RSA</h1>

    <form action="generate_keys_action.php" method="POST">
        <button type="submit" name="generate" value="true">Générer les Clés</button>
    </form>

    <?php
    // Vérifier si les clés existent dans la session
    if (isset($_SESSION['private_key']) && isset($_SESSION['public_key'])) {
        $privateKey = htmlspecialchars($_SESSION['private_key']);
        $publicKey = htmlspecialchars($_SESSION['public_key']);

        echo "<h3>Clé Privée :</h3>";
        echo "<textarea id='private_key' name='private_key' readonly>$privateKey</textarea>";

        echo "<h3>Clé Publique :</h3>";
        echo "<textarea id='public_key' name='public_key' readonly>$publicKey</textarea>";

        // Effacer les clés de la session pour éviter qu'elles ne restent affichées
        unset($_SESSION['private_key']);
        unset($_SESSION['public_key']);
    }
    ?>

</body>
</html>