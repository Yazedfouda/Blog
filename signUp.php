<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include_once "db.php";

// التحقق من request
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit();
}

// جلب البيانات
$data = json_decode(file_get_contents("php://input"), true);

// التحقق من البيانات
$errors = [];
if (empty($data["email"]) || trim($data["email"]) == "" || !filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email is Not Valid";
}
if (empty($data["password"]) || trim($data["password"]) == "") {
    $errors[] = "Password is Not Valid";
}
if (($data["password"] ?? '') !== ($data["confirm_password"] ?? 'x')) {
    $errors[] = "Passwords do not match";
}
if (empty($data["name"]) || trim($data["name"]) == "") {
    $errors[] = "Name is Not Valid";
}
if (!empty($errors)) {
    echo json_encode([
        "success" => false,
        "message" => $errors
    ]);
    exit();
}

// تهيئة المتغيرات بعد التحقق
$email = strip_tags(trim($data["email"]));
$name = strip_tags(trim($data["name"]));
$password = password_hash($data["password"], PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

try {
    // التحقق من وجود البريد
    $checkstmt = $con->prepare("SELECT id FROM user WHERE email = :email LIMIT 1");
    $checkstmt->bindParam(":email", $email, PDO::PARAM_STR); // الآن نمرر المتغير
    $checkstmt->execute();

    if ($checkstmt->rowCount() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Email Already Exists"
        ]);
        exit();
    }

    // ادخال user جديد (تأكد من أن أسماء الأعمدة صحيحة في قاعدة البيانات)
    $stmt = $con->prepare("INSERT INTO user (email, password, name, token) VALUES (:email, :password, :name, :token)");
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->bindParam(":password", $password, PDO::PARAM_STR);
    $stmt->bindParam(":name", $name, PDO::PARAM_STR);
    $stmt->bindParam(":token", $token, PDO::PARAM_STR);
    $stmt->execute();

    $lastId = $con->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Sign Up Success",
        "data" => [
            "id" => $lastId,
            "email" => $email,
            "name" => $name
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
