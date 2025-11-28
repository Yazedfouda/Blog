<?php 
header("Content-Type: application/json");

include_once("db.php");
// $user=include("auth.php");

// التحقق من method

if($_SERVER['REQUEST_METHOD'] !== "GET"){
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Invalid"
    ]);
    exit;
}

try{
    $stmt=$con->prepare("SELECT * FROM posts");
    $stmt->execute();
    $posts=$stmt->fetchAll();
    if($stmt->rowCount() > 0){
        echo json_encode([
            "success" => true,
            "message" => "All Posts",
            "data" => $posts
        ]);
    }
    else{
        http_response_code(404);
                echo json_encode([
            "success" => false,
            "message" => "Not Have Posts",
        ]);
        exit;
    }
}
catch(PDOException $e){
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "ERROR SERVER"
    ]);
    exit;
}