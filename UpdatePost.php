    <?php 

        header("Content-Type: applicattion/json");
        include_once("db.php");
        $user=include("auth.php");


        // التحقق من ان المستخدم هوا الادمن
if (!isset($user) || (int)$user['admin'] !== 1) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Access Denied (Admins Only)"
    ]);
    exit;
}

        // التحقق من method
        if($_SERVER['REQUEST_METHOD'] !== "PUT"){
            http_response_code(405);
            echo json_encode([
                "success" => false,
                "message" => "Method Not Allowed"
            ]);
            exit;
        }

        // جلب البيانات 
        $data=json_decode(file_get_contents("php://input") , true);

        // التحقق من البيانات
        $errors=[];
        if(empty($data["title"]) || trim($data["title"]) == ""){
            $errors[]="Title is Required";
        }
        if(empty($data["description"]) || trim($data["description"]) == ""){
            $errors[]="Description is Required";
        }
        if(empty($data["author"]) || trim($data["author"]) == ""){
            $errors[]="Author is Required";
        }
        if(!empty($errors)){
            echo json_encode([
                "success" => false,
                "message" => $errors
            ]);
            exit;
        }

        // البيانات
        $title=trim(strip_tags($data['title']));
        $description=trim(strip_tags($data['description']));
        $author=trim(strip_tags($data['author']));

        try{
            $stmt=$con->prepare("UPDATE posts SET title= :title , description= :description , author= :author");
            $stmt->bindParam(":title" , $title, PDO::PARAM_STR);
            $stmt->bindParam(":description" , $description, PDO::PARAM_STR);
            $stmt->bindParam(":author" , $author, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode([
                "success" => true,
                "message" => "Update is Successfully",
                "data" => [
                    "NewTitle" => $title,
                    "NewDescription" => $description,
                    "NewAuthor" => $author
                ]
            ]);
        }
        catch(PDOException $e){
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Internal Server Error "
            ]);
            exit;
        }