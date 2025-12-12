<?php
$host = "10.147.17.170";
$port = "1521";
$service_name = "orclpdb";
$username = "system";
$password = 'dhaval123';

$tns = "$host:$port/$service_name";

echo "Attempting connection to: $tns<br>";

$conn = @oci_connect($username, $password, $tns);

if ($conn) {
    echo "✓ Connection Successful!<br>";
    oci_close($conn);
} else {
    $error = oci_error();
    echo "✗ Connection Failed: " . $error['message'];
}
?>