<?php
// auth.php - يعيد $user أو يتوقف إذا فشل التوثيق
include_once "db.php";

$headers = getallheaders();

// مراعاة اختلاف أسماء الهيدر (Authorization vs authorization)
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Token Missing"]);
    exit;
}

$token = trim($authHeader);
if (stripos($token, "Bearer ") === 0) {
    $token = substr($token, 7);
}
$token = trim($token);

try {
    $stmt = $con->prepare("SELECT id, email, name, admin FROM user WHERE token = :token LIMIT 1");
    $stmt->bindParam(":token", $token, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(403);
        echo json_encode(
            [
                "success" => false, 
            "message" => "Login is Required"
        ]);
        exit;
    }

    // اجعل قيمة admin رقمية لتسهيل المقارنات لاحقًا
    $user['admin'] = isset($user['admin']) ? (int)$user['admin'] : 0;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server Error: " . $e->getMessage()]);
    exit;
}
