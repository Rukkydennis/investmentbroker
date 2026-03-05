<?php
$DB = mysqli_connect(
    "localhost",
    "cryptosite",
    "localhost",
    "cryptosite"
);
if ($DB->connect_error) {
    echo json_encode("DATABASE_CONNECTION_ERROR: " . $DB->connect_error);
}