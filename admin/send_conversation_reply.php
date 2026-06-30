<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'dbconn.php';
require '../vendor/autoload.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    if (!empty($email) && !empty($message)) {
        // 🗂 Save to conversation table
        $stmt = $conn->prepare("INSERT INTO conversation (email, sender, message, date_sent) VALUES (?, 'admin', ?, NOW())");
        $stmt->bind_param("ss", $email, $message);
        $stmt->execute();
        $stmt->close();

        // ✉️ Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'hacknet395@gmail.com';
            $mail->Password = 'bczh fzeh yeuz eywv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('hacknet395@gmail.com', 'St4nger Dev');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reply from Admin'; // ✅ Ensure this is not empty
            $mail->Body = nl2br($message);

            $mail->send();

            echo json_encode(['success' => true, 'message' => 'Reply sent and emailed successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing email or message.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
