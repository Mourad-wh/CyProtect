<?php
// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['input'] ?? '';
    $algo = $_POST['algo'] ?? 'sha256';

    // Générer le hash en fonction de l'algorithme sélectionné
    if (!empty($input) && in_array($algo, hash_algos())) {
        $hash = hash($algo, $input);
    } else {
        $error = "Entrez un texte valide et choisissez un algorithme disponible.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de Hash PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .output {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <h1>Générateur de Hash en PHP</h1>
    <form method="post">
        <label for="input">Texte à hasher :</label>
        <textarea name="input" id="input" rows="3" placeholder="Entrez un texte ici..." required><?= htmlspecialchars($input ?? '') ?></textarea>

        <label for="algo">Choisir un algorithme :</label>
        <select name="algo" id="algo">
            <?php foreach (hash_algos() as $available_algo): ?>
                <option value="<?= $available_algo ?>" <?= (isset($algo) && $algo === $available_algo) ? 'selected' : '' ?>>
                    <?= strtoupper($available_algo) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Générer le Hash</button>
    </form>

    <?php if (isset($hash)): ?>
        <h2>Résultat :</h2>
        <div class="output"><?= htmlspecialchars($hash) ?></div>
    <?php elseif (isset($error)): ?>
        <div style="color: red;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</body>
</html>
