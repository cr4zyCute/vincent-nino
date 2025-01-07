<?php
include '../database/dbcon.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all students and their data
$studentsQuery = "SELECT id, firstname, lastname, image FROM student";
$studentsResult = $conn->query($studentsQuery);

// Create a temporary file to store the CSV content
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students_data.csv"');

$output = fopen('php://output', 'w');

// Write the header row
fputcsv($output, ['Profile Picture', 'ID', 'First Name', 'Last Name', 'Status', 'Section', 'Year Lvl']);

// Write the data rows
if ($studentsResult->num_rows > 0) {
    while ($student = $studentsResult->fetch_assoc()) {
        // Fetch Year Level, Section, and Status from the form_responses table
        $student_id = $student['id'];
        $query = "
            SELECT field_id, response 
            FROM form_responses 
            WHERE student_id = $student_id";
        $result = $conn->query($query);

        $status = '';
        $section = '';
        $year_lvl = '';

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['field_id'] == 80) {
                    $year_lvl = $row['response'];
                } elseif ($row['field_id'] == 81) {
                    $section = $row['response'];
                } elseif ($row['field_id'] == 82) {
                    $status = $row['response'];
                }
            }
        }

        // Write the student's data as a row in the CSV
        fputcsv($output, [
            !empty($student['image']) ? $student['image'] : 'default-profile.jpg',
            $student['id'],
            $student['firstname'],
            $student['lastname'],
            $status,
            $section,
            $year_lvl
        ]);
    }
} else {
    // Write a message if no data is available
    fputcsv($output, ['No students found']);
}

fclose($output);
$conn->close();
exit;
