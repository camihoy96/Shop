<?php
session_start();
require 'dbconn.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_name = $_SESSION['user_name'];

// Fetch messages from DB
$sql = "SELECT * FROM messages ORDER BY date_sent ASC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | St4nger Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --dark: #0a192f;
            --light: #f8f9fa;
            --gray: #6c757d;
            --success: #4cc9f0;
            --warning: #f72585;
            --danger: #e63946;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, var(--dark) 0%, #172a45 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 25px;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

         .sidebar-header {
    padding: 0 25px 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 20px;
}

.sidebar-header h2 {
    font-size: 20px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Wrapper for fixed logo size */
.sidebar-logo-container {
    width: 50px;   /* space reserved for logo */
    height: 50px;
    flex-shrink: 0; /* prevents shrinking */
    display: flex;
    align-items: center;
    justify-content: center;
}
/* Actual logo (can scale larger visually) */
.sidebar-header .sidebar-logo {
    width: 70px;       /* bigger visual size */
    height: 70px;
    border-radius: 12px;
    object-fit: cover;
    transform: scale(1.3); /* visually enlarge */
    transition: transform 0.3s ease;
}

.sidebar-header .sidebar-logo:hover {
    transform: scale(1.4); /* optional hover grow */
}

        .sidebar-nav {
            flex: 1;
            width: 100%;
        }

        .sidebar-nav a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 25px;
            transition: var(--transition);
            font-weight: 500;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            padding-left: 28px;
        }

       .sidebar-nav a.active {
            background: rgba(7, 90, 174, 0.15);
            color: var(--white);
            border-left: 4px solid var(--primary);
        }

        .sidebar-nav a i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        /* Main content */
        .main {
            margin-left: 260px;
            padding: 25px;
            transition: var(--transition);
        }

        .header {
            background: #fff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--card-shadow);
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            background: var(--light);
            padding: 8px 16px;
            border-radius: 30px;
        }

        .user-info i {
            color: var(--primary);
            font-size: 20px;
        }

        /* Table Container */
        .table-container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-header h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-header h3 i {
            color: var(--primary);
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .table-actions button {
            background: var(--light);
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-actions button:hover {
            background: var(--primary);
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
            position: sticky;
            top: 0;
        }

        tr {
            transition: var(--transition);
        }

        tr:hover {
            background: #f8fbff;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }

        .email-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .email-link:hover {
            text-decoration: underline;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        button.reply-btn, button.delete-btn, button.view-convo-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        button.reply-btn {
            background: rgba(76, 201, 240, 0.15);
            color: var(--success);
        }

        button.delete-btn {
            background: rgba(230, 57, 70, 0.15);
            color: var(--danger);
        }

        button.view-convo-btn {
            background: rgba(67, 97, 238, 0.15);
            color: var(--primary);
        }

        button.reply-btn:hover {
            background: var(--success);
            color: white;
            transform: translateY(-2px);
        }

        button.delete-btn:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
        }

        button.view-convo-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        /* Notification Dot */
        .notif-dot {
            background-color: var(--danger);
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 12px;
            margin-left: 5px;
        }

        /* Modals */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            padding: 20px;
        }

        .modal-content {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: var(--gray);
            transition: var(--transition);
        }

        .close-btn:hover {
            color: var(--danger);
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }

        .form-group input:focus, .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-group input[readonly] {
            background: #f5f5f5;
            color: var(--gray);
        }

        .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            width: 100%;
            transition: var(--transition);
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Chat Modal */
        .chat-modal {
            width: 500px;
            max-height: 600px;
            display: flex;
            flex-direction: column;
            padding: 0;
            overflow: hidden;
        }

        .chat-box {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
            max-height: 400px;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            margin-bottom: 15px;
            max-width: 80%;
            position: relative;
            word-wrap: break-word;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .message-bubble.user {
            background: #e2eaff;
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }

        .message-bubble.admin {
            background: #d1f5d3;
            align-self: flex-end;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }

        .message-bubble small {
            display: block;
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .chat-input {
            display: flex;
            border-top: 1px solid #eee;
            background: #fff;
            padding: 15px;
        }

        .chat-input textarea {
            flex: 1;
            resize: none;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            height: 50px;
            font-family: 'Poppins', sans-serif;
        }

        .chat-input button {
            margin-left: 10px;
            background: var(--primary);
            border: none;
            color: #fff;
            border-radius: 8px;
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .chat-input button:hover {
            background: var(--primary-dark);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                overflow: hidden;
            }
            
            .sidebar-header h2 span,
            .sidebar-nav a span {
                display: none;
            }
            
            .sidebar-nav a {
                justify-content: center;
                padding: 16px;
            }
            
            .sidebar-nav a i {
                font-size: 20px;
            }
            
            .logout-btn span {
                display: none;
            }
            
            .main {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .table-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 0;
            }
            
            .main {
                margin-left: 0;
            }
            
            .modal-content, .chat-modal {
                width: 95%;
            }
        }

          .user-dropdown {
    position: relative;
    display: inline-block;
    font-family: 'Poppins', sans-serif;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #333;
    font-weight: 500;
    transition: color 0.3s ease;
}

.user-info:hover {
    color: #007bff;
}

.user-info i.fa-caret-down {
    font-size: 0.9rem;
}

.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    margin-top: 8px;
    min-width: 160px;
    z-index: 1000;
    overflow: hidden;
}

/* Styled Logout Button */
.logout-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    color: #e74c3c;
    font-weight: 300;
    text-decoration: none;
    transition: all 0.3s ease;
    background-color: #fff;
    border-top: 1px solid #eee;
}

.logout-btn i {
    font-size: 16px;
}

.logout-btn:hover {
    background-color: #e74c3c;
    color: #fff;
    transform: translateX(3px);
}

.logout-btn:hover i {
    color: #fff;
}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
        <img src="../image/logo.png" alt="Logo" class="sidebar-logo">
        <span>St4nger Message</span>
    </h2>
        </div>
        
        <div class="sidebar-nav">
            <a href="home.php"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
            <a href="message.php" class="active"><i class="fa-solid fa-envelope"></i> <span>Messages</span></a>
          <a href="../portfolio/port.php"><i class="fa-solid fa-briefcase"></i> <span>Portfolio</span></a>
            <a href="../analytic/analytic.php"><i class="fa-solid fa-chart-bar"></i> <span>Analytics</span></a>
            <a href="../settings/settings.php"><i class="fa-solid fa-gear"></i> <span>Settings</span></a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Messages</h1>
           <!-- User Info Dropdown -->
<div class="user-dropdown">
    <div class="user-info" onclick="toggleDropdown()">
        <i class="fa-solid fa-user-circle"></i>
        <span><?php echo htmlspecialchars($user_name); ?></span>
        <i class="fa-solid fa-caret-down"></i>
    </div>

    <div id="dropdownMenu" class="dropdown-menu">
        <a href="../logout.php" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3><i class="fa-solid fa-inbox"></i> Inbox</h3>
                <div class="table-actions">
                    <button><i class="fa-solid fa-filter"></i> Filter</button>
                    <button><i class="fa-solid fa-download"></i> Export</button>
                </div>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sender</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Date Sent</th>
                            <th>Conversation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php
    $count = 1; // Start numbering from 1
    if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
            $email = $row['email'];
            
            // Check for unread messages
            $checkUnread = $conn->query("SELECT COUNT(*) AS unread_count FROM messages WHERE email='$email' AND is_read=0");
            $unread = $checkUnread->fetch_assoc()['unread_count'];
    ?>
    <tr id="msg-<?php echo $row['id']; ?>">
        <td><?php echo $count++; ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td>
            <?php if (!empty($email)): ?>
                <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="email-link">
                    <?php echo htmlspecialchars($email); ?>
                </a>
            <?php else: ?>
                <span style="color: #999;">(No email provided)</span>
            <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($row['subject']); ?></td>
        <td><?php echo date("M d, Y h:i A", strtotime($row['date_sent'])); ?></td>
        <td>
            <?php if (!empty($email)): ?>
                <button class="view-convo-btn" data-email="<?php echo htmlspecialchars($email); ?>">
                    <i class="fa-solid fa-comments"></i> View
                    <?php if ($unread > 0): ?>
                        <span class="notif-dot"><?php echo $unread; ?></span>
                    <?php endif; ?>
                </button>
            <?php else: ?>
                <span style="color:#aaa;">N/A</span>
            <?php endif; ?>
        </td>
        <td>
            <div class="action-buttons">
                <?php if (!empty($email)): ?>
                    <button class="reply-btn" 
                        data-email="<?php echo htmlspecialchars($email); ?>" 
                        data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                        data-subject="Re: <?php echo htmlspecialchars($row['subject']); ?>">
                        <i class="fa-solid fa-reply"></i> Reply
                    </button>
                <?php endif; ?>

                <button class="delete-btn" data-id="<?php echo $row['id']; ?>">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </div>
        </td>
    </tr>
    <?php
        endwhile;
    else:
    ?>
    <tr>
        <td colspan="7" style="text-align:center; color:#888;">No messages found.</td>
    </tr>
    <?php endif; ?>
</tbody>

                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fa-regular fa-envelope-open"></i>
                    <h3>No Messages Yet</h3>
                    <p>When users contact you, their messages will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reply Modal -->
    <div id="replyModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reply to Message</h3>
                <button class="close-btn" onclick="closeReplyModal()">&times;</button>
            </div>
            <form id="replyForm">
                <div class="form-group">
                    <label>To:</label>
                    <input type="email" id="replyEmail" name="email" readonly required>
                </div>
                
                <div class="form-group">
                    <label>Subject:</label>
                    <input type="text" id="replySubject" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label>Message:</label>
                    <textarea id="replyMessage" name="message" rows="5" required placeholder="Type your reply here..."></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Send Reply</button>
            </form>
        </div>
    </div>

    <!-- View Conversation Modal -->
    <div id="conversationModal" class="modal" style="display:none;">
        <div class="modal-content chat-modal">
            <div class="modal-header">
                <h3>Conversation with <span id="convoEmailLabel"></span></h3>
                <button class="close-btn">&times;</button>
            </div>

            <!-- Chat Messages -->
            <div id="conversationContent" class="chat-box"></div>

            <!-- Chat Reply Form -->
            <form id="chatReplyForm" class="chat-input" autocomplete="off">
                <input type="hidden" name="email" id="chatEmail">
                <input type="hidden" id="chatSubject" name="subject">
                <textarea id="chatMessage" name="message" placeholder="Type your reply..." required></textarea>
                <button type="submit">Send <i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <script>
        
        // =============================
        // REPLY MODAL
        // =============================

        // Open Reply Modal
        document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('replyEmail').value = this.dataset.email;
                document.getElementById('replySubject').value = this.dataset.subject;
                document.getElementById('replyModal').style.display = 'flex';
            });
        });

        // Close Reply Modal
        function closeReplyModal() {
            document.getElementById('replyModal').style.display = 'none';
        }

        // Handle Reply Form
        document.getElementById('replyForm').addEventListener('submit', function(e) {
            e.preventDefault();

            fetch('reply_message.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) closeReplyModal();
            })
            .catch(() => alert('Error sending reply.'));
        });

        // =============================
        // DELETE MESSAGE
        // =============================
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;

                if (confirm('Are you sure you want to delete this message?')) {
                    fetch('delete_message.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + encodeURIComponent(id)
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            document.getElementById('msg-' + id).remove();
                        }
                    })
                    .catch(() => alert('Error deleting message.'));
                }
            });
        });

        // =============================
        // CONVERSATION MODAL
        // =============================
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('conversationModal');
            const modalBody = document.getElementById('conversationContent');
            const convoEmailLabel = document.getElementById('convoEmailLabel');
            const chatEmail = document.getElementById('chatEmail');
            const chatMessage = document.getElementById('chatMessage');
            const closeBtn = modal.querySelector('.close-btn');

            // Event delegation for View buttons
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.view-convo-btn');
                if (!btn) return;

                const email = btn.dataset.email;
                if (!email) {
                    console.error('No email found for this conversation.');
                    return;
                }

                // Show loading state
                modalBody.innerHTML = '<p>Loading conversation...</p>';
                convoEmailLabel.textContent = email;
                chatEmail.value = email;

                // Fetch conversation HTML
                fetch('get_conversation.php?email=' + encodeURIComponent(email))
                    .then(res => {
                        if (!res.ok) throw new Error('Failed to fetch conversation: ' + res.status);
                        return res.text();
                    })
                    .then(html => {
                        modalBody.innerHTML = html;
                        // Open modal
                        modal.style.display = 'flex';
                        // Remove notification dot visually
                        const notif = btn.querySelector('.notif-dot');
                        if (notif) notif.remove();
                        // Mark messages as read server-side
                        fetch('mark_as_read.php?email=' + encodeURIComponent(email)).catch(err => {
                            console.error('mark_as_read error:', err);
                        });
                        // Focus reply box
                        if (chatMessage) chatMessage.focus();
                    })
                    .catch(err => {
                        console.error(err);
                        modalBody.innerHTML = '<p class="error">Error loading conversation.</p>';
                    });
            });

            // Close modal by close button
            closeBtn.addEventListener('click', () => modal.style.display = 'none');

            // Close modal by clicking outside content
            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });

            // Handle reply form submit
            const replyForm = document.getElementById('chatReplyForm');
            if (replyForm) {
                replyForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const message = chatMessage.value.trim();
                    if (!message) {
                        alert('Please type a message before sending.');
                        chatMessage.focus();
                        return;
                    }
                    
                    // Send and reload conversation
                    fetch('send_conversation_reply.php', {
                        method: 'POST',
                        body: new FormData(replyForm)
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            chatMessage.value = '';
                            // Reload conversation
                            const email = chatEmail.value;
                            // Small delay so DB has new message
                            setTimeout(() => {
                                fetch('get_conversation.php?email=' + encodeURIComponent(email))
                                    .then(r => r.text())
                                    .then(html => modalBody.innerHTML = html);
                            }, 400);
                        } else {
                            alert(data.message || 'Failed to send.');
                        }
                    })
                    .catch(err => {
                        console.error('Reply send error:', err);
                        alert('Network or server error.');
                    });
                });
            }
        });

         function toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

// Close dropdown when clicking outside
window.addEventListener('click', function(e) {
    const dropdown = document.querySelector('.user-dropdown');
    if (!dropdown.contains(e.target)) {
        document.getElementById('dropdownMenu').style.display = 'none';
    }
});
 
    </script>
</body>
</html>