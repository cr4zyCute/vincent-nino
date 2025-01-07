<?php
include 'database/dbcon.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve POST data
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Hash the password
    $admin_notified = 0; // Default value for admin notification
    $approved = 0; // Default value for student approval

    // File upload handling
    $file_name = '';
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
        $file_name = basename($_FILES['profilePicture']['name']);
        $tempname = $_FILES['profilePicture']['tmp_name'];
        $folder = 'images-data/' . $file_name;

        // Validate and move uploaded file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array(mime_content_type($tempname), $allowed_types)) {
            if (!move_uploaded_file($tempname, $folder)) {
                echo "Failed to upload image.";
                exit();
            }
        } else {
            echo "Invalid image file type.";
            exit();
        }
    }

    // Insert student data
    $sql = "INSERT INTO student (firstname, middlename, lastname, image, approved, admin_notified) 
            VALUES ('$firstname', '$middlename', '$lastname', '$file_name', '$approved', '$admin_notified')";

    if (mysqli_query($conn, $sql)) {
        $student_id = mysqli_insert_id($conn); // Get the last inserted ID

        // Insert credentials data
        $credentials_sql = "INSERT INTO credentials (student_id, email, password) 
                            VALUES ('$student_id', '$email', '$password')";

        if (mysqli_query($conn, $credentials_sql)) {
            // Add a notification for the admin
            $notification_message = "A new student has registered: $firstname ($email)";
            $notification_sql = "INSERT INTO notifications (message, student_id, created_at, is_read) 
                                 VALUES ('$notification_message', '$student_id', NOW(), 0)";

            if (mysqli_query($conn, $notification_sql)) {
                // Redirect with success message
                header("Location: RegistrationForm.php?update=success");
                exit();
            } else {
                echo "Error in adding notification: " . mysqli_error($conn);
            }
        } else {
            echo "Error in adding credentials: " . mysqli_error($conn);
        }
    } else {
        echo "Error in adding student: " . mysqli_error($conn);
    }

    mysqli_close($conn); // Close the database connection
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Step Form</title>
    <link rel="stylesheet" href="./css/registration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="main-container">
        <div class="left-section">
            <h1>Please Fill up the Following</h1>
            <img src="images/bsitlogo.png" alt="Image Description" style="width: 200px; height: auto; border-radius: 10px; margin-top: 20px;"><br>
            <a href="index.php">
                <button class="login-btn">Login</button>
            </a>
        </div>
        <div class="right-section">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-container">
                    <div class="form-step active">
                        <div class="form-group">
                            <label for="firstname">Firstname:</label>
                            <input type="text" id="firstname" name="firstname">
                            <label for="middlename">Middlename:</label>
                            <input type="text" id="middlename" name="middlename">
                        </div>
                        <div class="form-group">
                            <label for="lastname">Lastname:</label>
                            <input type="text" id="lastname" name="lastname">
                        </div>
                        <button type="button" class="next-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="form-step">
                        <div class="form-group">
                            <div class="profile-pic">
                                <img id="profileImage" src="./images/defaultProfile.jpg">
                                <label for="profilePicture">Profile Picture:</label>
                                <button type="button" class="edit-btn" id="editButton">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <input type="file" id="profilePicture" name="profilePicture" accept="image/*" style="display: none;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password">
                        </div>
                        <button type="button" class="prev-btn"><i class="fas fa-chevron-left"></i></button>
                        <button type="submit" name="submit" class="register-btn">Register</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <section class="modal-section">
        <span class="overlay"></span>
        <div class="modal-box">
            <i class="fa-regular fa-circle-check" style="font-size: 50px; color:green;"></i>
            <h2>Success</h2>
            <h3>You have successfully registered!</h3>
            <div class="buttons">
                <a href="index.php">
                    <button class="close-btn">OK</button>
                </a>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const steps = document.querySelectorAll('.form-step');
            const nextBtns = document.querySelectorAll('.next-btn');
            const prevBtns = document.querySelectorAll('.prev-btn');
            let currentStep = 0;

            const updateSteps = () => {
                steps.forEach((step, index) => {
                    step.classList.toggle('active', index === currentStep);
                });
            };

            const validateStep = (stepIndex) => {
                const inputs = steps[stepIndex].querySelectorAll('input[required]');
                let isValid = true;

                inputs.forEach((input) => {
                    if (!input.value.trim() || (input.type === 'radio' && !document.querySelector(`input[name="${input.name}"]:checked`))) {
                        input.classList.add('error');
                        isValid = false;
                    } else {
                        input.classList.remove('error');
                    }
                });

                return isValid;
            };

            nextBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (validateStep(currentStep) && currentStep < steps.length - 1) {
                        currentStep++;
                        updateSteps();
                    }
                });
            });

            prevBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (currentStep > 0) {
                        currentStep--;
                        updateSteps();
                    }
                });
            });

            updateSteps();
        });

        const profileInput = document.getElementById('profilePicture');
        const profileImage = document.getElementById('profileImage');
        const editButton = document.getElementById('editButton');

        editButton.addEventListener('click', function() {
            profileInput.click();
        });

        profileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const section = document.querySelector(".modal-section"),
                overlay = document.querySelector(".overlay"),
                closeBtn = document.querySelector(".close-btn");
            if (urlParams.get('update') === 'success') {
                section.classList.add("active");
            }
            overlay.addEventListener("click", () => section.classList.remove("active"));
            closeBtn.addEventListener("click", () => section.classList.remove("active"));

            window.history.replaceState({}, document.title, window.location.pathname);
        });
    </script>
</body>

</html>