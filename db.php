<?php 

$dbname="mysql:host=localhost;dbname=blog;";
$username="root";
$password="root";

try{
    $con=new PDO($dbname,$username,$password);
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e){
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "ERROR: " . $e->getMessage()

    ]);
}