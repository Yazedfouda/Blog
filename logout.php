<?php 
header("Content-Type: application/json");

include_once("db.php");

// التحقق من method

if($_SERVER['REQUEST_METHOD'] !== "POST"){
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Invaild"
    ]);
    exit;
}

// جلب كل الهيدر
$headers=getallheaders();
$token=$headers['Authorization'] ?? $headers['authorization'] ?? null;

if(!$token){
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Token is Required"
    ]);
    exit;
}

try{
    // التحقق من ان المستخدم موجود

    $stmt=$con->prepare("SELECT id FROM user WHERE token=:token LIMIT 1");
    $stmt->execute([
        ":token" => $token
    ]);
    $user=$stmt->fetch(PDO::FETCH_ASSOC);
    if(!$user){
        echo json_encode([
            "success" => false,
            "message" => "Token is Required"
        ]);
        exit;
    }

    // حذف التوكن

    $update=$con->prepare("UPDATE user SET token= NULL WHERE id= :id");
    $update->bindParam(":id" , $user['id'] , PDO::PARAM_INT);
    $update->execute();

    echo json_encode([
        "success" => true,
        "message" => "Logout is successfully"
    ]);
}
catch(PDOException $e){
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "ERROR: " .$e
    ]);
    exit;
}