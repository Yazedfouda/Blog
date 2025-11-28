<?php
include_once "db.php";
header("Content-Type: application/json");
// header("Access-Control-Allow-Origin: *");

// التحقق من method
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method Not Allowed"
    ]);
    exit;
}

// جلب البيانات
$data = json_decode(file_get_contents("php://input"), true);

// التحقق من البيانات
$errors = [];

if (empty($data['email']) || !filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email Not Valid";
}
if (!isset($data['password']) || trim($data['password']) === "") {
    $errors[] = "Password Not Valid";
}
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $errors
    ]);
    exit;
}

// لا نعدّل كلمة المرور قبل التحقق (لا strip_tags عليها)
$email = strip_tags(trim($data["email"]));
$password = $data["password"]; // احتفظ بالقيمة الأصلية للتحقق من password_hash

try {
    // جلب المستخدم
    $stmt = $con->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        // توليد توكين جديد
        $newToken = bin2hex(random_bytes(32));
        $update = $con->prepare("UPDATE user SET token = :token WHERE id = :id");
        $update->bindParam(":token", $newToken, PDO::PARAM_STR);
        $update->bindParam(":id", $user["id"], PDO::PARAM_INT);
        $update->execute();

        // إرجاع النتيجة
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Login Success",
            "data" => [
                "id" => (int)$user['id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "token" => $newToken
            ]
        ]);
        exit;
    } else {
        // بيانات اعتماد غير صحيحة
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid Email or Password"
        ]);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
    exit;
}
