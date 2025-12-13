
php<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set Oracle environment
putenv("ORACLE_HOME=C:\\oracle\\product\\21c\\dbhomeXE");

$host = "10.147.17.170";
$port = "1521";
$service = "orclpdb";
$user = "system";
$pass = "dhaval123";

$tns = "$host:$port/$service";

echo "Connecting to: $tns<br>";
echo "User: $user<br>";

$conn = oci_connect($user, $pass, $tns);

if ($conn) {
    echo "✓ Connected successfully!<br>";
    oci_close($conn);
} else {
    $err = oci_error();
    echo "✗ Connection failed: " . $err['message'];
}
?>
