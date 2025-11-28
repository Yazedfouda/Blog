<?php 
    header("Content-Type: application/json");
    include_once("db.php");

    // التحقق من method
    if($_SERVER['REQUEST_METHOD'] !== "GET"){
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Method Not Invalid"
        ]);
        exit;
    }

    $data= json_decode(file_get_contents("php://input"));

    if(empty($data['id']) || !is_int($data['id'])){
        echo json_encode([
            "success" => false,
            "message" => "Id is Required"
        ]);
        exit;
    }

    $id=(int)$data['id'];

    try{
        $stmt=$con->prepare("SELECT * FROM posts WHERE id= :id");
        $stmt->execute([":id" => $id]);
        $post=$stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount() > 0){
            echo json_encode([
                "success" => true,
                "message" => "POST",
                "data" => $post
            ]);
        }else{
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Not Found"
            ]);
        }
    }
    catch(PDOException $e){
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "ERROR: SERVER IS DOWN"
        ]);
    }