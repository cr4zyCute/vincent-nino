<?php
include '../database/dbcon.php';

$forms = $conn->query("SELECT * FROM forms")->fetch_all(MYSQLI_ASSOC);
$students = $conn->query("SELECT * FROM student")->fetch_all(MYSQLI_ASSOC);
$approved_students = $conn->query("SELECT id, firstname, image FROM student WHERE approved = 1")->fetch_all(MYSQLI_ASSOC);
$new_students = $conn->query("SELECT * FROM student WHERE is_approved = 0 AND admin_notified = 0")->fetch_all(MYSQLI_ASSOC);

$conn->query("UPDATE student SET admin_notified = 1 WHERE is_approved = 0 AND admin_notified = 0");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to left, rgb(81, 79, 79), black);
            color: rgb(255, 255, 255);
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        header {
            background: linear-gradient(to left, rgb(81, 79, 79), black);
            color: white;

            text-align: center;
            font-size: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        main {
            display: flex;
            flex: 1;
            height: calc(100vh - 5rem);
        }

        .button-container {
            display: flex;
            position: relative;
            left: 70%;
        }

        #searchInput {
            width: 90%;
            padding: 10px 15px;
            margin-bottom: 15px;
            font-size: 16px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        #searchInput:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            background-color: #ffffff;
        }

        .sidebar {
            width: 25%;
            background-color: #ffffff;
            padding: 1rem;
            box-shadow: 2px 0 6px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            height: 100%;
        }

        .content {
            width: 75%;
            padding: 2rem;
            overflow-y: auto;
        }

        h1,
        h2 {
            margin-bottom: 1rem;
        }

        section {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            margin: 0.5rem 0;
            padding: 1rem;
            background: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        ul li:hover {
            background: #f1f3f5;
        }

        .fieldBtn {
            background: linear-gradient(to left, rgb(81, 79, 79), black);

            color: white;
            border: none;
            padding: 20px 25px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-right: 0.5rem;
            border-radius: 50%;
        }

        .fieldBtn:hover {
            background: linear-gradient(to left, rgb(81, 79, 79), black);

            transform: scale(1.05);
        }

        .createBtn {
            background: linear-gradient(to right, rgb(81, 79, 79), black);

            color: white;
            border: none;
            width: 20%;
            padding: 0.7rem 1.5rem;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .createBtn:hover {
            background: linear-gradient(to left, rgb(81, 79, 79), black);

            transform: scale(1.05);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        form label {
            margin-bottom: 0.2rem;
            font-weight: bold;
        }

        form input,
        form select,
        form button {
            padding: 0.7rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
        }

        #fields .field {
            margin-bottom: 1rem;
            background: #f8f9fa;
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 5px;
        }

        #sendModal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            display: none;
            z-index: 1000;
            width: 90%;
            max-width: 500px;
        }

        #sendModal h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #007bff;
        }

        #sendModal button {
            margin-top: 1rem;
        }

        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            z-index: 999;
        }


        .sendbtn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 50%;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-left: 10px;
        }

        .sendbtn:hover {
            background-color: #0056b3;
            transform: scale(1.1);
        }

        .deletebtn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-left: 10px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .deletebtn:hover {
            background-color: #c82333;
            transform: scale(1.1);
        }

        li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            transition: box-shadow 0.2s ease;
        }

        li:hover {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        a {
            text-decoration: none;
            color: #007bff;
            font-weight: 600;
            flex-grow: 1;
        }

        a:hover {
            text-decoration: underline;
        }

        #formList {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }


        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
            }

            .content {
                width: 100%;
            }

            main {
                flex-direction: column;
            }
        }

        #studentSearch {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .student-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .student-list li {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #f1f1f1;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .student-list li:hover {
            background: linear-gradient(to left, rgb(81, 79, 79), black);

        }

        .student-list label {
            display: flex;
            align-items: center;
            cursor: pointer;
            width: 100%;
        }

        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        input[type="radio"] {
            margin-right: 10px;
        }


        .student-name {
            font-size: 14px;
            color: #333;
        }
    </style>
</head>

<body>
    <main>
        <div class="sidebar">
            <a href="./admin-dashboard.php"><i class="bi bi-arrow-90deg-left"></i></a>
            <h2>Available Forms</h2>
            <input type="text" id="searchInput" placeholder="Search forms..." onkeyup="liveSearch()">
            <ul id="formList">
                <?php foreach ($forms as $form): ?>
                    <li>
                        <a href="view_form.php?form_id=<?= $form['id']; ?>"><?= htmlspecialchars($form['form_name']); ?></a>
                        <button class="sendbtn" onclick="showSendModal(<?= $form['id']; ?>)"><i class="bi bi-send-fill"></i></button>
                        <form action="delete_form.php" method="POST" style="display: inline;">
                            <input type="hidden" name="form_id" value="<?= $form['id']; ?>">
                            <button type="submit" class="deletebtn" onclick="return confirm('Are you sure you want to delete this form?')">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>

            </ul>
        </div>
        <div class="content">
            <section>
                <h2>Create a New Form</h2>
                <form id="createForm" method="POST" action="create_form.php">
                    <div class="button-container">
                        <button type="button" class="fieldBtn" onclick="addField()"><i class="bi bi-clipboard2-plus-fill"></i></button>
                        <button type="submit" class="createBtn">Create Form</button>
                    </div>
                    <label for="form_name">Form Name:</label>
                    <input type="text" name="form_name" id="form_name" required><br><br>
                    <div id="fields"></div>
                </form>
            </section>
        </div>
    </main>


    <div id="overlay"></div>
    <div id="sendModal">
        <button onclick="closeSendModal()"><i class="bi bi-x-circle-fill"></i></button>
        <h3>Send Form to Student</h3>
        <form method="POST" action="send_form.php">
            <input type="hidden" name="form_id" id="modalFormId">

            <label for="studentSearch">Search Students:</label>
            <input type="text" id="studentSearch" placeholder="Type to search..." onkeyup="filterStudentList()">

            <label for="student_id">Select a Student:</label>
            <ul class="student-list" id="studentList">
                <?php if (!empty($approved_students)): ?>
                    <?php foreach ($approved_students as $student): ?>
                        <li>
                            <label>
                                <input type="radio" name="student_id" value="<?= htmlspecialchars($student['id']); ?>" required>
                                <img src="../images-data/<?= htmlspecialchars($student['image']); ?>" alt="Profile Image" class="profile-image">
                                <span class="student-name"><?= htmlspecialchars($student['firstname']); ?></span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No approved students available. Please approve students first.</li>
                <?php endif; ?>
            </ul>

            <br><br>
            <button type="submit">Send Form</button>
        </form>
    </div>

    <script>
        let fieldCount = 0;

        function addField() {
            const fieldsDiv = document.getElementById('fields');
            const fieldId = `field_${fieldCount}`;

            const fieldHTML = `
    <center>
        <div class="field" id="${fieldId}">
            <label style='color: black;' >Field Name:</label>
            <input type="text" name="fields[${fieldCount}][name]" required>
            <label>Field Type:</label>
            <select name="fields[${fieldCount}][type]">
                <option value="text">Text</option>
                <option value="number">Number</option>
                <option value="email">Email</option>
                <option value="textarea">Textarea</option>
            </select>
            
            <button type="button" style="background-color: red" onclick="removeField('${fieldId}')"> <i class="bi bi-trash-fill"></i></button>
        </div>
        </center>
    `;
            fieldsDiv.insertAdjacentHTML('beforeend', fieldHTML);
            fieldCount++;
        }

        function filterStudentList() {
            const searchInput = document.getElementById("studentSearch").value.toLowerCase();
            const studentList = document.getElementById("studentList");
            const students = studentList.getElementsByTagName("li");

            for (let i = 0; i < students.length; i++) {
                const studentName = students[i].getElementsByClassName("student-name")[0].innerText.toLowerCase();

                if (studentName.includes(searchInput)) {
                    students[i].style.display = "flex";
                } else {
                    students[i].style.display = "none";
                }
            }
        }

        function removeField(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.remove();
            }
        }
        document.getElementById('student_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const studentImage = selectedOption.getAttribute('data-image');
            const studentName = selectedOption.getAttribute('data-name');

            if (studentImage && studentName) {
                document.getElementById('studentImage').src = studentImage;
                document.getElementById('studentName').textContent = studentName;
                document.getElementById('studentPreview').style.display = 'block';
            } else {
                document.getElementById('studentPreview').style.display = 'none';
            }
        });


        function liveSearch() {
            const searchQuery = document.getElementById('searchInput').value;

            fetch(`live_search.php?search=${encodeURIComponent(searchQuery)}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('formList').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }


        function showSendModal(formId) {
            document.getElementById('modalFormId').value = formId;
            document.getElementById('sendModal').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeSendModal() {
            document.getElementById('sendModal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>

</body>

</html>