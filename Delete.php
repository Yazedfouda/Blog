<?php 
    header("Content-Type: application/json");
    include_once "db.php";
    $user=include_once "auth.php";

    // التحقق من الصلاحية
    if (!isset($user) || (int)$user['admin'] !== 1) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Access Denied (Admins Only)"
        ]);
        exit;
    }

    // التحقق من method
    if($_SERVER['REQUEST_METHOD'] !== "DELETE"){
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Method Not Allowed"
        ]);
        exit();
    }

    $data = json_decode(file_get_contents("php://input"), true);
        $postId = $data['id'] ?? null;

    try{
        $stmt=$con->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute([":id" => $postId]);

        echo json_encode([
            "success" => true,
            "message" => "Delete is Successfully"
        ]);
    }
    catch(PDOException $e){
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "ERROR: SERVER IS DOWN"
        ]);
        exit;
    }
