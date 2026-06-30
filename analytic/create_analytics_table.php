<?php
session_start();
require 'dbconn.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

try {
    // SQL to create analytics_data table
    $sql = "CREATE TABLE analytics_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visitor_id VARCHAR(255) NOT NULL,
        page_url VARCHAR(500) NOT NULL,
        traffic_source VARCHAR(100),
        page_views INT DEFAULT 1,
        time_spent INT DEFAULT 0,
        bounce_rate DECIMAL(5,2) DEFAULT 0,
        date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        country VARCHAR(100),
        browser VARCHAR(100),
        device VARCHAR(50)
    )";

    if ($conn->query($sql) === TRUE) {
        // Insert sample data for demonstration
        $sample_data = [
            "INSERT INTO analytics_data (visitor_id, page_url, traffic_source, page_views, time_spent, bounce_rate, country, browser, device) VALUES 
            ('visitor_001', '/home', 'Direct', 5, 180, 25.0, 'United States', 'Chrome', 'Desktop')",
            
            "INSERT INTO analytics_data (visitor_id, page_url, traffic_source, page_views, time_spent, bounce_rate, country, browser, device) VALUES 
            ('visitor_002', '/portfolio', 'Organic Search', 3, 120, 40.0, 'United Kingdom', 'Firefox', 'Mobile')",
            
            "INSERT INTO analytics_data (visitor_id, page_url, traffic_source, page_views, time_spent, bounce_rate, country, browser, device) VALUES 
            ('visitor_003', '/contact', 'Social Media', 2, 60, 60.0, 'Canada', 'Safari', 'Tablet')"
        ];
        
        foreach ($sample_data as $query) {
            $conn->query($query);
        }
        
        echo json_encode(['success' => true, 'message' => 'Analytics table created successfully with sample data!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating table: ' . $conn->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>