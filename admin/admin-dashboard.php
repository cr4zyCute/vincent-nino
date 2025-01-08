<?php
include '../database/dbcon.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}
$admin_id = $_SESSION['admin_id'];

$query = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    echo "Admin profile not found.";
    exit();
}

$forms = $conn->query("SELECT * FROM forms")->fetch_all(MYSQLI_ASSOC);
$students = $conn->query("SELECT * FROM student")->fetch_all(MYSQLI_ASSOC);
$approved_students = $conn->query("SELECT * FROM student WHERE approved = 1")->fetch_all(MYSQLI_ASSOC);
$new_students = $conn->query("SELECT * FROM student WHERE is_approved = 0 AND admin_notified = 0")->fetch_all(MYSQLI_ASSOC);


$conn->query("UPDATE student SET admin_notified = 1 WHERE is_approved = 0 AND admin_notified = 0");

$students = $conn->query("SELECT * FROM student")->fetch_all(MYSQLI_ASSOC);
$forms = $conn->query("SELECT * FROM forms")->fetch_all(MYSQLI_ASSOC);
include '../database/dbcon.php';

$unread_notifications_query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE is_read = 0";
$unread_notifications_result = $conn->query($unread_notifications_query);

if (!$unread_notifications_result) {
    die("Error fetching unread notifications: " . $conn->error);
}

$unread_notifications_count = $unread_notifications_result->fetch_assoc()['unread_count'] ?? 0;

$unapproved_students_query = "SELECT COUNT(*) AS unapproved_count FROM student WHERE approved = 0";
$unapproved_students_result = $conn->query($unapproved_students_query);

if (!$unapproved_students_result) {
    die("Error fetching unapproved students: " . $conn->error);
}

$unapproved_students_count = $unapproved_students_result->fetch_assoc()['unapproved_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./admin-css/dashboard.css">
    <link rel="stylesheet" href="./admin-css/admin-home.css">

    <title>Admin Page</title>
</head>

<body>
    <div class="sidebar">
        <center>
            <h2>Admin Panel</h2>
        </center>
        <img src="../images/bsitlogo.png" alt="logo">
        <ul style="margin-top: 15px;">
            <li><a href="#" onclick="showSection('dashboard')" aria-controls="dashboard" aria-selected="true"><i style="margin: 10px;" class="bi bi-clipboard-data-fill"></i>Dashboard</a></li>

            <li><a href="#" onclick="showSection('student')" aria-controls="student"><i style="margin: 10px;" class="bi bi-mortarboard-fill"></i>Manage Student</a></li>
            <li><a href="#" onclick="showSection('announcements')" aria-controls="announcements"><i style="margin: 10px;" class="bi bi-newspaper"></i>announcements</a></li>
            <li><a href="adminForm.php" onclick="showSection('form')" aria-controls="form"><i style="margin: 10px;" class="bi bi-send-plus-fill"></i>Form</a></li>
        </ul>

    </div>

    <div class="main-content">
        <div class="header">
            <div class="logo">
                <img src="../images/bsitlogo.png" alt="Logo">
                <span>BSIT</span>
            </div>
            <div class="icons">

                <a href="#" onclick="showSection('notifications')" aria-controls="notifications">
                    <i class="bi bi-envelope-fill"></i>
                    <span class="notification-count">
                        <?= htmlspecialchars($unapproved_students_count); ?>
                    </span>
                </a>
                <a href="#" onclick="showSection('announcements')" aria-controls="announcements"><i class="bi bi-megaphone-fill announcement-icon"></i></a>

                <div class="dropdown">
                    <a href="./adminProfile.php">
                        <img src="../images-data/<?= htmlspecialchars($admin['adminProfile']) ?>" alt="Profile Image" class="profile-image">
                        <div class="dropdown-content">
                            <a href="./logout.php"><i style="padding-right: 5px; color: red; font-size: 20px;" class="bi bi-power"></i>Log out</a>
                        </div>
                </div>
            </div>
        </div>

        <section id="dashboard" class="active">
            <?php
            include '../database/dbcon.php';
            function getStudentCount($conn, $year)
            {
                $query = "SELECT COUNT(*) AS count FROM form_responses WHERE response = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $year);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc()['count'] ?? 0;
            }
            $total_students_query = "SELECT COUNT(*) AS total_students FROM student";
            $total_students_result = $conn->query($total_students_query);

            if (!$total_students_result) {
                die("Error fetching total students: " . $conn->error);
            }

            $total_students = $total_students_result->fetch_assoc()['total_students'] ?? 0;

            $first_year_students = getStudentCount($conn, 'First Year');
            $second_year_students = getStudentCount($conn, 'Second Year');
            $third_year_students = getStudentCount($conn, 'Third Year');
            $fourth_year_students = getStudentCount($conn, 'Fourth Year');

            $approved_students_query = "SELECT COUNT(*) AS approved_students FROM student WHERE approved = 1";
            $approved_students_result = $conn->query($approved_students_query);
            $approved_students = $approved_students_result->fetch_assoc()['approved_students'] ?? 0;

            ?>
            <h1>Dashboard</h1>
            <div class="dashboard-boxes">

                <div class="dashboard-box">
                    <h3>Total Students</h3>
                    <p><?= htmlspecialchars($total_students) ?></p>
                </div>

                <div class="dashboard-box">
                    <h3>First Year Students</h3>
                    <p><?= htmlspecialchars($first_year_students) ?></p>
                </div>
                <div class="dashboard-box">
                    <h3>Second Year Students</h3>
                    <p><?= htmlspecialchars($second_year_students) ?></p>
                </div>
                <div class="dashboard-box">
                    <h3>Third Year Students</h3>
                    <p><?= htmlspecialchars($third_year_students) ?></p>
                </div>
                <div class="dashboard-box">
                    <h3>Fourth Year Students</h3>
                    <p><?= htmlspecialchars($fourth_year_students) ?></p>
                </div>
                <div class="dashboard-box">
                    <h3>Approved Students</h3>
                    <p><?= htmlspecialchars($approved_students) ?></p>
                </div>

            </div>

        </section>
        <section id="report">
            <h2>Manage Students</h2>
            <h3>Student List</h3>

            <button style="background-color: #4CAF50; color: white;           padding: 10px 20px;       border: none;             border-radius: 5px;       cursor: pointer;       font-size: 16px;         transition: background-color 0.3s ease; " onclick="window.location.href='download_table.php'">Download Table</button>

            <table class="dashboard-table" id="student-table">
                <thead>
                    <tr>
                        <th>Profile Picture</th>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Status</th>
                        <th>Section</th>
                        <th>Year Lvl</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($students as $student):
                        $student_id = $student['id'];
                        $stmt = $conn->prepare("SELECT field_id, response FROM form_responses WHERE student_id = ?");
                        $stmt->bind_param("i", $student_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        $status = '';
                        $section = '';
                        $year_lvl = '';

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {

                                if ($row['field_id'] == 79) {
                                    $year_lvl = $row['response'];
                                } elseif ($row['field_id'] == 80) {
                                    $section = $row['response'];
                                } elseif ($row['field_id'] == 81) {
                                    $status = $row['response'];
                                }
                            }
                        } else {
                            echo "No form responses for student ID: $student_id<br>";
                        }
                    ?>
                        <tr>
                            <td>
                                <center>
                                    <img src="../images-data/<?= !empty($student['image']) ? htmlspecialchars($student['image']) : 'default-profile.jpg'; ?>"
                                        alt="Profile Picture"
                                        style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc;">
                                </center>
                            </td>
                            <td><?= htmlspecialchars($student['id']); ?></td>
                            <td><?= htmlspecialchars($student['firstname']); ?></td>
                            <td><?= htmlspecialchars($student['lastname']); ?></td>
                            <td><?= htmlspecialchars($status) ?: 'N/A'; ?></td>
                            <td><?= htmlspecialchars($section) ?: 'N/A'; ?></td>
                            <td><?= htmlspecialchars($year_lvl) ?: 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </section>

        <section id="student">
            <h2>Manage Students</h2>
            <h3>Student List</h3>
            <button style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease; ">
                <a href=" #" onclick="showSection('report')" aria-controls="report"><i style="margin: 10px;" class="bi bi-newspaper"></i>student List</a>
            </button>

            <input
                type="text"
                id="searchInput"
                placeholder="Search by ID, First Name, or Last Name..."
                style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">

            <table class="dashboard-table" id="student-table">
                <thead>
                    <tr>
                        <th>Profile Picture</th>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <center>
                                        <img src="../images-data/<?= !empty($student['image']) ? htmlspecialchars($student['image']) : 'default-profile.jpg'; ?>"
                                            alt="Profile Picture"
                                            style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc;">
                                    </center>
                                </td>
                                <td><?= htmlspecialchars($student['id']); ?></td>
                                <td><?= htmlspecialchars($student['firstname']); ?></td>
                                <td><?= htmlspecialchars($student['lastname']); ?></td>
                                <td>
                                    <!-- Edit Button -->
                                    <a href="studentUpdate.php?id=<?= htmlspecialchars(string: $student['id']); ?>"
                                        style="display: inline-block; background-color: #3bd20f; color: #fff; padding: 10px 15px; font-size: 16px; text-align: center; text-decoration: none; border-radius: 5px; border: 1px solid transparent; transition: background-color 0.3s ease;"
                                        onmouseover="this.style.backgroundColor='#00ff00 ';"
                                        onmouseout="this.style.backgroundColor='#5dff2e';">
                                        <i class="bi bi-pencil-square" style="font-size: 16px;"></i>
                                    </a>


                                    <!-- Delete Button -->
                                    <form action="deleteStudent.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($student['id']); ?>">
                                        <button type="submit" style="display: inline-block; background-color:rgb(255, 0, 0); color: #fff; padding: 10px 15px; font-size: 16px; text-align: center; text-decoration: none; border-radius: 5px; border: 1px solid transparent; transition: background-color 0.3s ease;"
                                            onmouseover="this.style.backgroundColor='#ff7860';"
                                            onmouseout="this.style.backgroundColor='#c11d00';"
                                            onclick="return confirm('Are you sure you want to delete this student?');"><i class="bi bi-trash3-fill"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>



        <script>
            document.getElementById('searchInput').addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#student-table tbody tr');

                rows.forEach(row => {
                    const id = row.cells[1].textContent.toLowerCase();
                    const firstName = row.cells[2].textContent.toLowerCase();
                    const lastName = row.cells[3].textContent.toLowerCase();

                    if (id.includes(filter) || firstName.includes(filter) || lastName.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        </script>

        <section id="announcements" style="background-color: #f9f9f9; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
            <h1 style="color: #333; font-size: 24px; margin-bottom: 10px;">Reports</h1>

            <form action="postAnnouncement.php" method="POST" style="margin-bottom: 20px;">
                <label for="title" style="display: block; margin-bottom: 5px; font-weight: bold;">Title:</label>
                <input type="text" id="title" name="title" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px;">

                <label for="content" style="display: block; margin-bottom: 5px; font-weight: bold;">Content:</label>
                <textarea id="content" name="content" required style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px;"></textarea>

                <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Post Announcement</button>
            </form>
            <center>
                <div class="right-section" style="margin-top: 20px;">
                    <?php

                    $sql = "SELECT a.title, a.content, a.created_at, ad.admin_username AS admin_username ,ad.admin_name AS admin_name 
            FROM announcements a 
            JOIN admin ad ON a.admin_id = ad.id 
            ORDER BY a.created_at DESC";

                    $result = mysqli_query($conn, $sql);


                    $query = "SELECT * FROM admin WHERE id = '$admin_id'";
                    $studentResult = mysqli_query($conn, $query);

                    if ($studentResult && mysqli_num_rows($studentResult) > 0) {
                        $student = mysqli_fetch_assoc($studentResult);
                    } else {
                        echo "Student profile not found.";
                        exit();
                    }
                    ?>

                    <div class="announcement" style="margin-top: 20px;">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <div class="card" style="background-color: #fff; padding: 15px; margin-bottom: 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                                    <div class="profile-info">
                                        <strong style="color: #333; font-size: 18px;"><?php echo htmlspecialchars($row['admin_name']); ?></strong>
                                        <small class="role" style="display: block; color: #666; margin-top: 5px;">
                                            <i class="bi bi-people-fill"></i>
                                            <small style="margin: 0px;"><?php echo htmlspecialchars($row['admin_username']); ?></small>
                                        </small>
                                        <small class="time" style="display: block; color: #aaa; margin-top: 5px;"><?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></small>
                                        <p style="color: #555; margin-top: 10px;"><?php echo htmlspecialchars($row['content']); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: #999;">No announcements available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </center>
        </section>


        <section id="notifications">
            <?php
            $notifications = $conn->query("SELECT * FROM notifications WHERE is_read = 0");
            if (!$notifications) {
                die("Error fetching notifications: " . $conn->error);
            }

            $unapproved_users = $conn->query("
            SELECT 
                student.id AS student_id, 
                student.firstname, 
                student.image, 
                credentials.email 
            FROM 
                student 
            INNER JOIN 
                credentials AS credentials 
            ON 
                student.id = credentials.student_id 
            WHERE 
                student.approved = 0
        ");
            if (!$unapproved_users) {
                die("Error fetching unapproved users: " . $conn->error);
            }


            $mark_read = $conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
            if (!$mark_read) {
                die("Error updating notifications: " . $conn->error);
            }


            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
                $imageName = basename($_FILES['profileImage']['name']);
                $imagePath = 'images-data/' . $imageName;

                if (!empty($imageName)) {
                    if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $imagePath)) {
                        echo "Image uploaded successfully.";
                    } else {
                        echo "Failed to upload image.";
                        exit();
                    }
                }
            }
            ?>
            <h2>Unapproved Students</h2>
            <?php if ($unapproved_users->num_rows > 0): ?>
                <form method="POST" action="approve_users.php">
                    <button type="submit" name="action" value="approve" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 5px; cursor: pointer; margin-right: 10px; transition: background-color 0.3s ease;">Approve Selected</button>
                    <button type="submit" name="action" value="reject" style="background-color: #dc3545; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;">Reject Selected</button>

                    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border: 1px solid #ddd;">
                        <thead>
                            <tr style="background-color: #f4f4f4; font-weight: bold;">
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Profile</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Username</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Email</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Approve</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Reject</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $unapproved_users->fetch_assoc()): ?>
                                <tr style="background-color: #f9f9f9; border: 1px solid #ddd;">
                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">
                                        <center>
                                            <img src="../images-data/<?= !empty($student['image']) ? htmlspecialchars($student['image']) : 'default-profile.jpg'; ?>"
                                                alt="Profile Picture"
                                                style="width: 150px; height: 150px;object-fit: cover; border: 1px solid #ccc;">
                                        </center>
                                    </td>

                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;"><?= htmlspecialchars($student['firstname']); ?></td>
                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;"><?= htmlspecialchars($student['email']); ?></td>
                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">
                                        <input type="checkbox" name="approve_users[]" value="<?= $student['student_id']; ?>">
                                    </td>
                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">
                                        <input type="checkbox" name="reject_users[]" value="<?= $student['student_id']; ?>">
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </form>
            <?php else: ?>
                <p>No students awaiting approval.</p>
            <?php endif; ?>
        </section>

        <section id="form">
            <h2>Create a New Form</h2>
            <form id="createForm" method="POST" action="create_form.php">
                <label for="form_name">Form Name:</label>
                <input type="text" name="form_name" id="form_name" required><br><br>
                <button type="button" onclick="addField()">Add Field</button>
                <button type="submit">Create Form</button>
                <div id="fields"></div>
            </form>


            <section>
                <h2>Available Forms</h2>
                <ul>
                    <?php foreach ($forms as $form): ?>
                        <li>
                            <?= htmlspecialchars($form['form_name']); ?>
                            <button onclick="showSendModal(<?= $form['id']; ?>)">Send to student</button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>


            <div id="sendModal" style="display:none;">
                <h3>Send Form to Student</h3>
                <form method="POST" action="send_form.php">
                    <input type="hidden" name="form_id" id="modalFormId">
                    <label for="student_id">Select Student:</label>
                    <select name="student_id" id="student_id" required>
                        <?php if (!empty($approved_students)): ?>
                            <?php foreach ($approved_students as $student): ?>
                                <option value="<?= htmlspecialchars($student['id']); ?>">
                                    <?= htmlspecialchars($student['firstname']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option disabled>No approved students available</option>
                        <?php endif; ?>

                    </select><br><br>
                    <button type="submit">Send Form</button>
                </form>
                <button onclick="document.getElementById('sendModal').style.display = 'none';">Close</button>
            </div>
        </section>

        <script>
            fieldCount = 0;

            function showSendModal(formId) {
                document.getElementById('modalFormId').value = formId;
                document.getElementById('sendModal').style.display = 'block';
            }

            function addField() {
                const fieldsDiv = document.getElementById('fields');
                const fieldHTML = `
            <div class="field">
                <label>Field Name:</label>
                <input type="text" name="fields[${fieldCount}][name]" required>
                <label>Field Type:</label>
                <select name="fields[${fieldCount}][type]">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="email">Email</option>
                    <option value="textarea">Textarea</option>
                </select>
                <label>Required:</label>
                <input type="checkbox" name="fields[${fieldCount}][required]">
            </div>
        `;
                fieldsDiv.insertAdjacentHTML('beforeend', fieldHTML);
                fieldCount++;
            }
        </script>
        </section>
    </div>
    <script src="../js/home.js"></script>
    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.main-content section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            const activeSection = document.getElementById(sectionId);
            activeSection.classList.add('active');
            localStorage.setItem('activeSection', sectionId);
        }
    </script>

</body>

</html>