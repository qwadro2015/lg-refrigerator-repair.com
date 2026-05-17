<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json'); // JSON-відповідь

    // Turnstile Recaptcha
function validateTurnstile($token, $secret, $remoteip = null) {
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    $data = [
        'secret' => $secret,
        'response' => $token
    ];

    if ($remoteip) {
        $data['remoteip'] = $remoteip;
    }

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        return ['success' => false, 'error-codes' => ['internal-error']];
    }

    return json_decode($response, true);

}

// Usage
$secret_key = '0x4AAAAAADROZxCzqH0E2Ou1IsXz-Gx8E3M';
$token = $_POST['cf-turnstile-response'] ?? '';
$remoteip = $\_SERVER['HTTP_CF_CONNECTING_IP'] ??
$\_SERVER['HTTP_X_FORWARDED_FOR'] ??
$\_SERVER['REMOTE_ADDR'];

$validation = validateTurnstile($token, $secret_key, $remoteip);

if ($validation['success']) {
// Valid token - process form
echo "Form submission successful!";
// Process your form data here
} else {
// Invalid token - show error
echo "Verification failed. Please try again.";
error_log('Turnstile validation failed: ' . implode(', ', $validation['error-codes']));
}

    // Отримуємо та обробляємо дані з форми
    $name = htmlspecialchars($_POST["name"]);
    $phone =  htmlspecialchars($_POST["phone"]);
    $email = htmlspecialchars($_POST["email"]);
    $address = htmlspecialchars($_POST["address"]);
    $message = htmlspecialchars($_POST["message"]);

    // Перевірка на валідність email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit;
    }

    // Перевірка на наявність всіх полів
    if (empty($name) || (empty($phone) || empty($email) || empty($address) || empty($message)) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }

    // Налаштовуємо email
    // $to = "sd@lg-refrigerator-repair.com"; // Ваша email-адреса
    $to = "lynnyk.work@gmail.com";
    $headers = "From: $email\r\nReply-To: $email\r\nContent-Type: text/html; charset=UTF-8";  // Використовуємо HTML

    // Формуємо тіло листа з HTML
    $body = "<b>Name:</b> $name<br><b>Phone:</b> $phone<br><b>Email:</b> $email<br><b>Address:</b> $address<br><b>Issue details:</b><br>$message";

    // Відправляємо лист
    if (mail($to, $subject, $body, $headers)) {
        echo json_encode(["status" => "success", "message" => "Email sent successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error sending email!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>