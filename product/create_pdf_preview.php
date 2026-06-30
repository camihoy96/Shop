<?php
require 'dbconn.php';

header('Content-Type: application/json');

if (!isset($_GET['file_path'])) {
    echo json_encode(['success' => false, 'message' => 'No file path provided']);
    exit;
}

$file_path = $_GET['file_path'];
$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Check if file exists
if (!file_exists($file_path)) {
    // Try with current directory
    $file_path = './' . basename($file_path);
    if (!file_exists($file_path)) {
        echo json_encode([
            'success' => false, 
            'message' => 'File not found. It may have been moved or deleted.'
        ]);
        exit;
    }
}

if ($file_extension === 'pdf') {
    // File is already PDF - return success
    echo json_encode([
        'success' => true, 
        'message' => 'PDF file ready for preview',
        'pdf_path' => $file_path
    ]);
} elseif (in_array($file_extension, ['doc', 'docx'])) {
    // Try multiple conversion methods
    $conversion_result = tryMultipleConversionMethods($file_path);
    
    if ($conversion_result['success']) {
        echo json_encode([
            'success' => true, 
            'message' => 'Conversion successful',
            'pdf_path' => $conversion_result['pdf_path']
        ]);
    } else {
        // Provide helpful error message with alternatives
        echo json_encode([
            'success' => false, 
            'message' => 'Online preview is not available for Word documents on this server. ' .
                        'Please download the file and open it with Microsoft Word, Google Docs, or use free online conversion tools.',
            'download_url' => $file_path,
            'alternatives' => [
                'Download and open with Microsoft Word',
                'Upload to Google Docs for online viewing',
                'Use free online converters like SmallPDF or ILovePDF',
                'Use LibreOffice or other free office software'
            ]
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Unsupported file format: ' . $file_extension . '. ' .
                    'Please download the file to view it with appropriate software.'
    ]);
}

function tryMultipleConversionMethods($word_path) {
    // Method 1: Check if LibreOffice is available
    if (isLibreOfficeAvailable()) {
        $result = convertWithLibreOffice($word_path);
        if ($result['success']) {
            return $result;
        }
    }
    
    // Method 2: Check if Unoconv is available (alternative to LibreOffice)
    if (isUnoconvAvailable()) {
        $result = convertWithUnoconv($word_path);
        if ($result['success']) {
            return $result;
        }
    }
    
    // Method 3: Try using PHP COM extension (Windows only)
    if (isWindowsServer() && isCOMExtensionAvailable()) {
        $result = convertWithCOM($word_path);
        if ($result['success']) {
            return $result;
        }
    }
    
    return ['success' => false, 'message' => 'No conversion methods available'];
}

function isLibreOfficeAvailable() {
    $output = shell_exec('which libreoffice 2>/dev/null');
    return !empty($output);
}

function convertWithLibreOffice($word_path) {
    $output_dir = sys_get_temp_dir();
    $pdf_filename = pathinfo($word_path, PATHINFO_FILENAME) . '_converted.pdf';
    $pdf_path = $output_dir . '/' . $pdf_filename;
    
    $command = "libreoffice --headless --convert-to pdf --outdir " . 
               escapeshellarg($output_dir) . " " . 
               escapeshellarg($word_path) . " 2>&1";
    
    exec($command, $output, $return_code);
    
    if ($return_code === 0 && file_exists($pdf_path)) {
        // Make the file accessible via web
        $web_accessible_path = '/temp/' . $pdf_filename;
        return [
            'success' => true,
            'pdf_path' => $web_accessible_path
        ];
    }
    
    return ['success' => false, 'message' => 'LibreOffice conversion failed'];
}

function isUnoconvAvailable() {
    $output = shell_exec('which unoconv 2>/dev/null');
    return !empty($output);
}

function convertWithUnoconv($word_path) {
    $output_dir = sys_get_temp_dir();
    $pdf_filename = pathinfo($word_path, PATHINFO_FILENAME) . '_converted.pdf';
    $pdf_path = $output_dir . '/' . $pdf_filename;
    
    $command = "unoconv -f pdf -o " . escapeshellarg($pdf_path) . " " . escapeshellarg($word_path) . " 2>&1";
    
    exec($command, $output, $return_code);
    
    if ($return_code === 0 && file_exists($pdf_path)) {
        $web_accessible_path = '/temp/' . $pdf_filename;
        return [
            'success' => true,
            'pdf_path' => $web_accessible_path
        ];
    }
    
    return ['success' => false, 'message' => 'Unoconv conversion failed'];
}

function isWindowsServer() {
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

function isCOMExtensionAvailable() {
    return extension_loaded('com_dotnet');
}

function convertWithCOM($word_path) {
    // This only works on Windows servers with Microsoft Office installed
    try {
        $word = new COM("Word.Application") or die("Unable to instantiate Word");
        $word->Visible = 0;
        $word->DisplayAlerts = 0;
        
        $doc = $word->Documents->Open(realpath($word_path));
        $pdf_path = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
        
        $doc->ExportAsFixedFormat($pdf_path, 17); // 17 = PDF format
        $doc->Close(false);
        $word->Quit();
        
        if (file_exists($pdf_path)) {
            $web_accessible_path = '/temp/' . basename($pdf_path);
            return [
                'success' => true,
                'pdf_path' => $web_accessible_path
            ];
        }
    } catch (Exception $e) {
        error_log("COM conversion error: " . $e->getMessage());
    }
    
    return ['success' => false, 'message' => 'COM conversion failed'];
}
?>