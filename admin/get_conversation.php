<?php 
require 'dbconn.php';

if (isset($_GET['email'])) {
    $email = trim(strtolower($_GET['email']));

    // ✅ Fetch both messages (user + admin) in order, using your exact DB structure
    $stmt = $conn->prepare("
        SELECT 
            id, 
            name AS sender_name, 
            message, 
            date_sent, 
            'user' AS sender_type
        FROM messages
        WHERE email = ?
        UNION ALL
        SELECT 
            id, 
            sender AS sender_name, 
            message, 
            date_sent, 
            sender AS sender_type
        FROM conversation
        WHERE email = ?
        ORDER BY date_sent ASC
    ");
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ✅ Output your CSS styles
        echo "
        <style>
            .chat-container {
                max-height: 400px;
                overflow-y: auto;
                padding: 15px;
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .message-bubble {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                animation: fadeIn 0.3s ease-out;
                max-width: 100%;
            }

            .user-align {
                justify-content: flex-start;
                align-self: flex-start;
            }

            .admin-align {
                justify-content: flex-end;
                align-self: flex-end;
            }

            .chat-text {
                padding: 12px 16px;
                border-radius: 18px;
                max-width: 70%;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                word-wrap: break-word;
            }

            .user-bubble {
                background: #e2eaff;
                border-bottom-left-radius: 5px;
                border-top-right-radius: 18px;
                border-bottom-right-radius: 18px;
            }

            .admin-bubble {
                background: #d1f5d3;
                border-bottom-right-radius: 5px;
                border-top-left-radius: 18px;
                border-bottom-left-radius: 18px;
            }

            .chat-text:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }

            .chat-text strong {
                display: block;
                margin-bottom: 5px;
                font-size: 14px;
                color: #212529;
            }

            .chat-text p {
                margin: 0;
                line-height: 1.4;
                color: #212529;
            }

            .chat-text small {
                display: block;
                margin-top: 8px;
                font-size: 11px;
                color: #6c757d;
                opacity: 0.8;
            }

            .delete-msg-btn {
                background: rgba(220, 53, 69, 0.1);
                border: 1px solid rgba(220, 53, 69, 0.3);
                border-radius: 6px;
                padding: 8px;
                cursor: pointer;
                color: #dc3545;
                transition: all 0.3s ease;
                font-size: 12px;
                opacity: 0;
                flex-shrink: 0;
            }

            .message-bubble:hover .delete-msg-btn {
                opacity: 1;
            }

            .delete-msg-btn:hover {
                background: #dc3545;
                color: white;
                transform: scale(1.1);
            }

            .user-align .delete-msg-btn {
                order: 2;
            }

            .admin-align .delete-msg-btn {
                order: -1;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .chat-container::-webkit-scrollbar {
                width: 6px;
            }

            .chat-container::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 3px;
            }

            .chat-container::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 3px;
            }

            .chat-container::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }
        </style>
        ";

        echo "<div class='chat-container'>";

        while ($row = $result->fetch_assoc()) {
            $id = htmlspecialchars($row['id']);
            $message = nl2br(htmlspecialchars($row['message']));
            $time = date('M d, Y h:i A', strtotime($row['date_sent']));
            $isAdmin = ($row['sender_type'] === 'admin');

            // ✅ Use proper display name
            $displayName = $isAdmin ? 'Admin' : htmlspecialchars($row['sender_name']);

            // ✅ Apply styles
            $bubbleClass = $isAdmin ? 'admin-bubble' : 'user-bubble';
            $alignClass = $isAdmin ? 'admin-align' : 'user-align';

            echo "
            <div class='message-bubble $alignClass'>
                <div class='chat-text $bubbleClass'>
                    <strong>{$displayName}:</strong>
                    <p>{$message}</p>
                    <small>{$time}</small>
                </div>
                <button class='delete-msg-btn' data-id='{$id}' data-type='{$row['sender_type']}' title='Delete message'>
                    <i class='fa-solid fa-trash'></i>
                </button>
            </div>";
        }

        echo "</div>";

    } else {
        echo "<div style='text-align: center; padding: 40px; color: #6c757d;'>
                <i class='fa-regular fa-comments' style='font-size: 48px; margin-bottom: 15px;'></i>
                <p>No messages yet for this conversation.</p>
              </div>";
    }

    $stmt->close();
} else {
    echo "<p style='color: #dc3545; text-align: center;'>Invalid request.</p>";
}

$conn->close();
?>
