<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'dbconn.php';
require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));

    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hacknet395@gmail.com';
        $mail->Password = 'bczh fzeh yeuz eywv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email Content
        $mail->setFrom('hacknet395@gmail.com', 'St4nger Dev');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = nl2br($message);

        // Send the email
        $mail->send();

        // Insert into conversation table
        $stmt = $conn->prepare("INSERT INTO conversation (email, sender, message) VALUES (?, 'admin', ?)");
        $stmt->bind_param("ss", $email, $message);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Reply sent and saved successfully!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
