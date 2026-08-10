<?php
// Set the response header to JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // 1. Cloudflare Turnstile Captcha Validation
    $turnstile_secret = "0x4AAAAAADROZxCzqH0E2Ou1IsXz-Gx8E3M"; 
    $turnstile_response = $_POST['cf-turnstile-response'] ?? '';

    if (empty($turnstile_response)) {
        echo json_encode(["status" => "error", "message" => "Please complete the captcha challenge."]);
        exit;
    }

    // Verify token with Cloudflare API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://challenges.cloudflare.com/turnstile/v0/siteverify");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $turnstile_secret,
        'response' => $turnstile_response
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Сумісність з хостингами (щоб запит до Cloudflare не падав через сертифікати)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    curl_close($ch);

    $outcome = json_decode($response, true);
    
    if (!$outcome['success']) {
        echo json_encode(["status" => "error", "message" => "Captcha verification failed. Please try again."]);
        exit;
    }

    // 2. Collect and sanitize form data
    $name    = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $phone   = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $address = filter_input(INPUT_POST, 'Address', FILTER_SANITIZE_SPECIAL_CHARS); // Matches name="Address" from your HTML
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);

    // Validate required fields
    if (!$name || !$phone || !$email || !$address || !$message) {
        echo json_encode(["status" => "error", "message" => "Please fill out all required fields correctly."]);
        exit;
    }

    // 3. Email Settings
    $to = "sd@lg-refrigerator-repair.com"; 
    // $to = "lynnyk.work@gmail.com"; 
    $subject = "LG Repair Service New Quote Request";
    
 // Email Body Content
    $body = "You have received a new message from the contact form:\n\n";
    $body .= "Name: $name\n";
    $body .= "Phone: $phone\n";
    $body .= "Email: $email\n";
    $body .= "Address: $address\n";
    $body .= "Issue Details:\n$message\n";

    // Email Headers
    $headers = "From: sd@lg-refrigerator-repair.com\r\n"; 
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // 4. Send Email
    if (mail($to, $subject, $body, $headers)) {
        echo json_encode(["status" => "success", "message" => "Thank you! Your request has been sent successfully."]);
        exit; // <--- Миттєво зупиняємо скрипт після успіху
    } else {
        echo json_encode(["status" => "error", "message" => "Server error: Failed to send email via mail()."]);
        exit; // <--- Зупиняємо скрипт у разі помилки надсилання
    }

} else {
    // If the file is accessed directly without POST method
    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    exit; // <--- Зупиняємо скрипт
}
?>