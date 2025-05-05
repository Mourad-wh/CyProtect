<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Certificate</title>
</head>
<body>
    <h1>Certificate Generator</h1>
    <form action="" method="post">
        <label for="commonName">Common Name (e.g., example.com):</label><br>
        <input type="text" id="commonName" name="commonName" required><br><br>
        
        <label for="organizationName">Organization Name:</label><br>
        <input type="text" id="organizationName" name="organizationName" required><br><br>
        
        <label for="localityName">City/Locality:</label><br>
        <input type="text" id="localityName" name="localityName" required><br><br>
        
        <label for="stateOrProvinceName">State/Province:</label><br>
        <input type="text" id="stateOrProvinceName" name="stateOrProvinceName" required><br><br>
        
        <label for="countryName">Country (2-letter code):</label><br>
        <input type="text" id="countryName" name="countryName" maxlength="2" required><br><br>
        
        <button type="submit" name="generate">Generate Certificate</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
        // Fetch user inputs
        $dn = array(
            "countryName" => $_POST['countryName'],
            "stateOrProvinceName" => $_POST['stateOrProvinceName'],
            "localityName" => $_POST['localityName'],
            "organizationName" => $_POST['organizationName'],
            "commonName" => $_POST['commonName'],
        );

        // Generate a new private key
        $config = array(
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $privateKey = openssl_pkey_new($config);

        // Create a CSR
        $csr = openssl_csr_new($dn, $privateKey);

        // Self-sign the certificate (valid for 365 days)
        $certificate = openssl_csr_sign($csr, null, $privateKey, 365);

        // Save certificate and private key to files
        openssl_x509_export($certificate, $certOut);
        openssl_pkey_export($privateKey, $privateKeyOut);

        // Display the results
        echo "<h2>Certificate Generated</h2>";
        echo "<h3>Certificate</h3>";
        echo "<pre>" . htmlspecialchars($certOut) . "</pre>";
        echo "<h3>Private Key</h3>";
        echo "<pre>" . htmlspecialchars($privateKeyOut) . "</pre>";
    }
    ?>
</body>
</html>
