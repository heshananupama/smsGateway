<?php
/**
 * Created by PhpStorm.
 * User: HeshanAnu
 * Date: 3/12/2018
 * Time: 3:46 PM
 */
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uokSMS";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}