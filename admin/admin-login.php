<?php
session_start();
include '../database/dbcon.php';
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $email_check_sql = "SELECT * FROM admin WHERE admin_email = '$email'";
    $email_check_result = mysqli_query($conn, $email_check_sql);

    if (mysqli_num_rows($email_check_result) === 0) {
        $error_message = "Admin does not exist!";
    } else {
        $user = mysqli_fetch_assoc($email_check_result);
        if ($user['admin_password'] === $password) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['admin_email'];
            header('Location: admin-dashboard.php');

            exit();
        } else {
            $error_message = "Wrong password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Raleway:400,700');

        *,
        *:before,
        *:after {
            box-sizing: border-box
        }

        body {

            font-family: 'Raleway', sans-serif;
        }

        .container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;

            &:hover,
            &:active {

                .top,
                .bottom {

                    &:before,
                    &:after {
                        margin-left: 200px;
                        transform-origin: -200px 50%;
                        transition-delay: 0s;
                    }
                }

                .center {
                    opacity: 1;
                    transition-delay: 0.2s;
                }
            }
        }

        .top,
        .bottom {

            &:before,
            &:after {
                content: '';
                display: block;
                position: absolute;
                width: 200vmax;
                height: 200vmax;
                top: 50%;
                left: 50%;
                margin-top: -100vmax;
                transform-origin: 0 50%;
                transition: all 0.5s cubic-bezier(0.445, 0.05, 0, 1);
                z-index: 10;
                opacity: 0.65;
                transition-delay: 0.2s;
            }
        }

        .top {
            &:before {
                transform: rotate(45deg);
                background: #e46569;
            }

            &:after {
                transform: rotate(135deg);
                background: #ecaf81;
            }
        }

        .bottom {
            &:before {
                transform: rotate(-45deg);
                background: #60b8d4;
            }

            &:after {
                transform: rotate(-135deg);
                background: #3745b5;
            }
        }

        .center {
            position: absolute;
            width: 400px;
            height: 400px;
            top: 50%;
            left: 50%;
            margin-left: -200px;
            margin-top: -200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.445, 0.05, 0, 1);
            transition-delay: 0s;
            color: #333;

            input {
                width: 100%;
                padding: 15px;
                margin: 5px;
                border-radius: 1px;
                border: 1px solid #ccc;
                font-family: inherit;
            }
        }
    </style>
</head>

<body>
    <div class="container" onclick="handleClick()">
        <div class="top"></div>
        <div class="bottom"></div>
        <div class="center">
            <form action="admin-login.php" method="post">
                <section id="formContainer" class="<?= !empty(trim($error_message)) ? 'show' : '' ?>">
                    <div class="ring">
                        <i style="--clr:#d7dbdd;"></i>
                        <i style="--clr:#d7dbdd;"></i>
                        <i style="--clr:#d7dbdd;"></i>
                        <div class="login">
                            <h2>Welcome Admin</h2>
                            <div class="inputBx">
                                <?php if (!empty($error_message)) : ?>
                                    <p id="errorMessage" style="color: white;"><?= $error_message; ?></p>
                                <?php endif; ?>
                                <input type="text" name="email" placeholder="Username" required>
                            </div>
                            <div class="inputBx">
                                <input type="password" name="password" placeholder="Password" required>
                            </div>
                            <div class="inputBx">
                                <input type="submit" name="login" value="Log in">
                            </div>
                            <div class="links">
                            </div>
                        </div>
                    </div>
                </section>
            </form>
            <h2>&nbsp;</h2>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const errorMessage = document.getElementById('errorMessage');
            if (errorMessage) {
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 3000);
            }
        });
    </script>
    <script>
        function handleClick() {
            console.log('Container clicked!');
        }
    </script>
</body>

</html>