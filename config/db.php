<?php

if (isset($conn) && $conn instanceof mysqli) {
    return;
}

$host = "localhost";
$user = "root";
$password = "Ashraf@540";
$database = "MindMerge_db";

$conn = mysqli_connect( $host,$user,$password,$database);

if(!$conn){

die("Database Connection Failed");

}

?>