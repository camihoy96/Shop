<?php
require 'dbconn.php';

// ===============================================
// GMAIL IMAP CONFIGURATION
// ===============================================
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'hacknet395@gmail.com';
$password = 'bczh fzeh yeuz eywv'; // your Gmail app password

// ===============================================
// CONNECT TO GMAIL
// ===============================================
$inbox = imap_open($hostname, $username, $password) or die('❌ Cannot connect to Gmail: ' . imap_last_error());

// ===============================================
// FETCH UNREAD EMAILS SINCE OCT 1
// ===============================================
$emails = imap_search($inbox, 'UNSEEN SINCE "1-Oct-2025"');

if ($emails) {
    rsort($emails); // newest first

    foreach ($emails as $email_number) {
        $overview = imap_fetch_overview($inbox, $email_number, 0)[0];
        $structure = imap_fetchstructure($inbox, $email_number);

        // ===============================================
        // EXTRACT MESSAGE BODY
        // ===============================================
        $message = '';
        if (!isset($structure->parts)) {
            // single-part message
            $message = imap_fetchbody($inbox, $email_number, 1);
        } else {
            // multi-part message → find text/plain part
            foreach ($structure->parts as $partNo => $part) {
                if (isset($part->subtype) && strtoupper($part->subtype) == 'PLAIN') {
                    $message = imap_fetchbody($inbox, $email_number, $partNo + 1);
                    break;
                }
            }
        }

        $message = quoted_printable_decode($message);
        $message = trim($message);
        if (empty($message)) continue; // skip empty emails

        // ===============================================
        // EXTRACT SENDER EMAIL
        // ===============================================
        $from = $overview->from;
        if (preg_match('/<(.+?)>/', $from, $matches)) {
            $senderEmail = strtolower(trim($matches[1]));
        } else {
            $senderEmail = strtolower(trim($from));
        }

        // ===============================================
        // ✅ CHECK IF THIS SENDER EXISTS IN `messages` TABLE
        // ===============================================
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE email = ?");
        $checkStmt->bind_param("s", $senderEmail);
        $checkStmt->execute();
        $checkStmt->bind_result($exists);
        $checkStmt->fetch();
        $checkStmt->close();

        // Skip unrelated emails
        if ($exists == 0) continue;

        // ===============================================
        // INSERT INTO `conversation` TABLE
        // ===============================================
        $stmt = $conn->prepare("
            INSERT INTO conversation (email, sender, message, date_sent)
            VALUES (?, 'user', ?, NOW())
        ");
        $stmt->bind_param("ss", $senderEmail, $message);
        $stmt->execute();
        $stmt->close();

        // ===============================================
        // MARK AS READ
        // ===============================================
        imap_setflag_full($inbox, $email_number, "\\Seen");
    }
}

imap_close($inbox);
$conn->close();

echo "✅ Gmail replies fetched successfully.";
?>
