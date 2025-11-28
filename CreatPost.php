<?php
header("Content-Type: application/json");
include_once "db.php";
$user = include_once "auth.php";

// التحقق من الصلاحية
if (!isset($user) || (int)$user['admin'] !== 1) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Access Denied (Admins Only)"
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$errors = [];
if (!isset($data["title"]) || trim($data["title"]) === "") {
    $errors[] = "Title is Required";
}
if (!isset($data["description"]) || trim($data["description"]) === "") {
    $errors[] = "Description is Required";
}
if (!isset($data["author"]) || trim($data["author"]) === "") {
    $errors[] = "Author is Required";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $errors
    ]);
    exit;
}

$title       = strip_tags(trim($data['title']));
$description = strip_tags(trim($data['description']));
$author      = strip_tags(trim($data['author']));

try {
    $con->beginTransaction();

    $stmt = $con->prepare("INSERT INTO posts (title, description, author) VALUES (:title, :description, :author)");
    $stmt->execute([
        ":title"       => $title,
        ":description" => $description,
        ":author"      => $author
    ]);

    $postId = (int)$con->lastInsertId();

    $con->commit();

    echo json_encode([
        "success" => true,
        "message" => "Post Created Successfully",
        "data" => [
            "post_id" => $postId
        ]
    ]);

} catch (PDOException $e) {
    if ($con->inTransaction()) $con->rollBack();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server Error"
        // لا تعرض $e->getMessage() في الإنتاج أبداً!
    ]);
}