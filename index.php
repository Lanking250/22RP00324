<?php
$host = 'localhost';
$db   = 'Appeal_database';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

$sessionId   = $_POST['sessionId'] ?? uniqid();
$serviceCode = $_POST['serviceCode'] ?? '*123#';
$phoneNumber = $_POST['phoneNumber'] ?? '2507XXXXXXX';
$text        = $_POST['text'] ?? '';

$input = explode("*", $text);

function ussd_proceed($message) {
    echo "CON $message";
    exit;
}

function ussd_end($message) {
    echo "END $message";
    exit;
}

// Step 0: Main menu
if ($text == "") {
    ussd_proceed("Welcome to RP Appeal\n1. See you marks\n2. Appeal your marks\n3. Quit");
}

// Option 1: Check marks
if ($input[0] == "1") {
    if (!isset($input[1])) {
        ussd_proceed("Please enter your student ID:");
    }

    $student_id = $conn->real_escape_string($input[1]);
    $student = $conn->query("SELECT * FROM students WHERE student_id='$student_id'")->fetch_assoc();
    if (!$student) ussd_end("Student not found.");

    $marks = $conn->query("
        SELECT m.module_name, mk.mark 
        FROM marks mk 
        JOIN modules m ON mk.module_id = m.module_id 
        WHERE mk.student_id='$student_id'
    ");

    $msg = "Your Marks:\n";
    while ($row = $marks->fetch_assoc()) {
        $msg .= "{$row['module_name']}: {$row['mark']}\n";
    }

    ussd_end($msg);
}

// Option 2: Appeal
if ($input[0] == "2") {
    // Step 1: Ask for student ID
    if (!isset($input[1])) {
        ussd_proceed("Please enter your student ID:");
    }

    $student_id = $conn->real_escape_string($input[1]);
    $student = $conn->query("SELECT * FROM students WHERE student_id='$student_id'")->fetch_assoc();
    if (!$student) ussd_end("Student ID not found.");

    // Step 2: Ask if user wants to submit new appeal 
    if (!isset($input[2])) {
        ussd_proceed("1. Submit your Appeal\n2. Check your Appeal ");
    }

    // ======= Submit New Appeal Flow =======
    if ($input[2] == "1") {
        // Step 3: Show module list
        if (!isset($input[3])) {
            $marks = $conn->query("
                SELECT m.module_name, mk.mark 
                FROM marks mk 
                JOIN modules m ON mk.module_id = m.module_id 
                WHERE mk.student_id='$student_id'
            ");

            $msg = "Select module to appeal:\n";
            $i = 1;
            while ($row = $marks->fetch_assoc()) {
                $msg .= "$i. {$row['module_name']} ({$row['mark']})\n";
                $i++;
            }
            $msg .= "0. Go Back";
            ussd_proceed($msg);
        }

        // Step 4: Handle module selection
        $module_number = intval($input[3]);
        if ($module_number == 0) ussd_end("Exited to main menu.");

        // Get selected module details
        $modules = [];
        $query = $conn->query("
            SELECT m.module_id, m.module_name, mk.mark 
            FROM marks mk 
            JOIN modules m ON mk.module_id = m.module_id 
            WHERE mk.student_id='$student_id'
        ");
        $i = 1;
        while ($row = $query->fetch_assoc()) {
            $modules[$i] = $row;
            $i++;
        }

        if (!isset($modules[$module_number])) {
            ussd_end("Invalid module selected.");
        }

        $selected_module = $modules[$module_number];
        $module_id = $selected_module['module_id'];
        $module_name = $selected_module['module_name'];

        // Step 5: Ask for reason
        if (!isset($input[4])) {
            ussd_proceed("Enter why you appeal  $module_name:");
        }

        $reason = $conn->real_escape_string($input[4]);
        $conn->query("
            INSERT INTO appeals (student_id, module_id, reason, status) 
            VALUES ('$student_id', '$module_id', '$reason', 'pending')
        ");

        ussd_end("okay submitted for $module_name.\nStatus: Pending.");
    }

    // ======= Check Appeal Status =======
    if ($input[2] == "2") {
        $result = $conn->query("
            SELECT m.module_name, a.status 
            FROM appeals a 
            JOIN modules m ON a.module_id = m.module_id 
            WHERE a.student_id = '$student_id' 
            ORDER BY a.appeal_id DESC
        ");

        if ($result->num_rows === 0) {
            ussd_end("You have no appeals submitted.");
        }

        $msg = "Your Appeal Status:\n";
        while ($row = $result->fetch_assoc()) {
            $msg .= "{$row['module_name']}: " . ucfirst($row['status']) . "\n";
        }
        ussd_end($msg);
    }

    // Invalid sub-option
    ussd_end("Invalid options TO Appeal.");
}

// Option 3: Exit
if ($input[0] == "3") {
    ussd_end("Thank you for using the RP Appeal System.");
}

// Fallback
ussd_end("Invalid input. Try again.");
?>
