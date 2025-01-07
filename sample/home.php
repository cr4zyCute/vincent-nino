<?php
include '../database/dbcon.php';
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
}

$student_id = $_SESSION['student_id'];
$query = "SELECT * FROM student WHERE id = '$student_id'";
$result = mysqli_query($conn, $query);
$querypost = "SELECT * FROM posts WHERE id = '$student_id'";
$content = $_POST['content'] ?? '';
$mediaFiles = $_FILES['media'] ?? [];

if ($result && mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);
} else {
    echo "Student profile not found.";
    exit();
}

if ($content || !empty($mediaFiles['name'][0])) {
    // Save post content to database
    $query = "INSERT INTO posts (student_id, content, created_at) VALUES ('$student_id', '$content', NOW())";
    mysqli_query($conn, $query);
    $postId = mysqli_insert_id($conn); // Get the ID of the newly created post

    // Handle media upload if any media files are provided
    if (!empty($mediaFiles['name'][0])) {
        foreach ($mediaFiles['tmp_name'] as $key => $tmp_name) {
            if ($mediaFiles['error'][$key] === 0) {
                $filePath = '../uploads/' . basename($mediaFiles['name'][$key]);
                move_uploaded_file($tmp_name, $filePath);

                // Update the post with media path
                $query = "UPDATE posts SET media = '$filePath' WHERE id = '$postId'";
                mysqli_query($conn, $query);
            }
        }
    }
}
if (!empty($post['media'])) {
    $mediaType = mime_content_type($post['media']);
    if (strpos($mediaType, 'image') !== false) {
        echo '<img src=".' . htmlspecialchars($post['media']) . '" alt="Post Media" class="post-image">';
    } elseif (strpos($mediaType, 'video') !== false) {
        echo '<video controls class="post-video"><source src="' . htmlspecialchars($post['media']) . '" type="' . $mediaType . '"></video>';
    }
} elseif (!empty($post['content'])) {
    echo '<p>' . htmlspecialchars($post['content']) . '</p>';
}

$posts = [];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}
$student_query = $conn->prepare("SELECT approved FROM student WHERE id = ?");
$student_query->bind_param('i', $student_id);
$student_query->execute();
$student_query->bind_result($approved);
$student_query->fetch();
$student_query->close();

$notifications_query = $conn->prepare("SELECT message FROM notifications WHERE student_id = ? AND is_read = 0");
$notifications_query->bind_param('i', $student_id);
$notifications_query->execute();
$notifications_result = $notifications_query->get_result();

$mark_read_query = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE student_id = ? AND is_read = 0");
$mark_read_query->bind_param('i', $student_id);
$mark_read_query->execute();

$forms_query = $conn->prepare("SELECT f.id AS form_id, f.form_name FROM student_forms sf
                               JOIN forms f ON sf.form_id = f.id
                               WHERE sf.student_id = ?");

$forms_query->bind_param('i', $student_id);
$forms_query->execute();
$forms_result = $forms_query->get_result();
$forms_query->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="./sample.css"> -->
</head>
<style>
    /* General Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
    }

    body {
        display: flex;
        flex-direction: column;
        height: 100vh;
        background-color: #f4f4f9;
        color: #333;
        overflow: hidden;
    }

    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #007bff;
        padding: 10px 20px;
        color: #fff;
    }

    .logo {
        display: flex;
        align-items: center;
    }

    .logo img {
        height: 40px;
        margin-right: 10px;
    }

    .icons {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .icons .dropdown {
        position: relative;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        z-index: 1000;
    }

    .dropdown-content a {
        display: block;
        padding: 10px 15px;
        color: #333;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .dropdown-content a:hover {
        background-color: #f0f0f0;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .profile-image {
        height: 40px;
        width: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
    }

    .container {
        display: flex;
        flex: 1;
        overflow: hidden;
    }

    .left-section {
        flex: 2;
        padding: 20px;
        overflow-y: auto;
    }

    .right-section {
        flex: 1;
        background-color: #fff;
        padding: 20px;
        overflow-y: auto;
        border-left: 1px solid #ddd;
    }

    .profile {
        display: flex;
        align-items: center;
        background-color: #fff;
        padding: 10px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .profile input {
        flex: 1;
        border: none;
        padding: 10px;
        margin-left: 10px;
        border-radius: 5px;
        background-color: #f4f4f4;
    }

    .profile button {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        margin-left: 10px;
    }

    .profile button:hover {
        background-color: #0056b3;
    }

    .popup-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .popup-overlay.active {
        display: flex;
    }

    .post-popup {
        background-color: #fff;
        border-radius: 10px;
        width: 400px;
        max-width: 90%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background-color: #007bff;
        color: #fff;
    }

    .postpopup-content {
        padding: 20px;
    }

    textarea {
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
        resize: none;
    }

    .add-photos {
        cursor: pointer;
        color: #007bff;
        text-decoration: underline;
    }

    .media-grid img {
        height: 80px;
        margin: 5px;
        object-fit: cover;
    }

    .post {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .post-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .post-header .profile-pic {
        height: 40px;
        width: 40px;
        border-radius: 50%;
        margin-right: 10px;
        object-fit: cover;
    }

    .post-user-info {
        display: flex;
        flex-direction: column;
    }

    .post-content {
        margin: 15px 0;
    }

    .post-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .comment-form textarea {
        width: calc(100% - 60px);
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
    }

    .comment-form button {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
        margin-left: 10px;
    }

    .announcement .card {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        background-color: #f9f9f9;
    }

    .announcement .card strong {
        display: block;
        margin-bottom: 10px;
    }

    .announcement .card p {
        margin-top: 10px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .container {
            flex-direction: column;
        }

        .right-section {
            border-left: none;
        }
    }
</style>

<body>
    <header>
        <div class="logo">
            <img src="../images/bsitlogo.png" alt="Logo">
            <span>BSIT</span>
        </div>
        <div class="icons">
            <a href="home.php"><i class="bi bi-house-door-fill"></i></a>
            <div class="dropdown">
                <a href="studentProfile.php">
                    <img src="../images-data/<?= htmlspecialchars($student['image']); ?>" alt="Profile Image" class="profile-image">
                </a>
                <div class="dropdown-content">
                    <a href="studentProfile.php">Profile Settings</a>
                    <a href="../includes/logout.php">Log out</a>
                </div>
            </div>
        </div>
    </header>
    <div class="container">
        <div class="left-section">
            <div class="profile" onclick="openPopup()">
                <img src="../images-data/<?= htmlspecialchars($student['image']) ?>" alt="Profile Image" class="profile-image">
                <input type="text" placeholder="Create a Post......">
                <button>POST</button>
            </div>
            <div class="popup-overlay" id="post-popup">
                <div class="post-popup">
                    <div class="popup-header">
                        <span>Create post</span>
                        <button onclick="closePopup()">×</button>
                    </div>
                    <form action="uploadPost.php" method="POST" enctype="multipart/form-data">
                        <div class="postpopup-content">
                            <div class="profile-container">
                                <a href="studentProfile.php">
                                    <img src="../images-data/<?= htmlspecialchars($student['image']) ?>" alt="Profile Image" class="profile-pic">
                                </a>
                                <p class="profile-name"><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></p>
                            </div>
                            <textarea name="content" placeholder="What on your mind? <?= htmlspecialchars($student['firstname']) ?>"></textarea>
                            <div class="add-photos" onclick="triggerFileUpload()">
                                <input type="file" id="media-upload" name="media[]" multiple accept="image/*,video/*" style="display: none;" onchange="previewFiles(event)">
                                <p>Add photos</p>
                            </div><br>
                            <div id="media-preview" class="media-grid"></div>
                        </div>
                        <button id="delete-post" class="cancel-button" style="display: none;" onclick="clearFiles()">Cancel Post</button>
                        <button class="post-btn" type="submit">Post</button>
                    </form>
                </div>
            </div>
            <div class="left-section">
                <?php
                date_default_timezone_set("Asia/Manila");
                function timeAgo($time, $tense = 'ago')
                {
                    static $periods = array('year', 'month', 'day', 'hour', 'minute');

                    if ((strtotime($time) <= 0)) {
                        trigger_error("Wrong time format: $time", E_USER_ERROR);
                    }
                    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
                    $then = new DateTime($time, new DateTimeZone('Asia/Manila'));
                    $diff = $now->diff($then)->format('%y %m %d %h %i');
                    $diff = explode(' ', $diff);
                    $diff = array_combine($periods, $diff);
                    $diff = array_filter($diff);

                    $period = key($diff);
                    $value = current($diff);

                    if ($period === 'minute' && $value == 0) {
                        // If less than 1 minute, show as "1 minute ago"
                        $value = 1;
                    }
                    if ($value) {
                        if ($value == 1) {
                            $period = rtrim($period, 's');
                        }
                        return "$value $period $tense";
                    }

                    return "just now";
                }
                $query = "SELECT p.*, s.firstname, s.lastname, s.image AS profile_image, 
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
              FROM posts p 
              JOIN student s ON p.student_id = s.id 
              ORDER BY p.created_at DESC";
                $result = mysqli_query($conn, $query);

                if ($result) {
                    while ($post = mysqli_fetch_assoc($result)) {
                        echo '<div class="post">';
                        echo '<div class="post-header">';
                        echo '<img src="../images-data/' . htmlspecialchars($post['profile_image']) . '" alt="Profile Image" class="profile-pic">';
                        echo '<div class="post-user-info">';
                        echo '<strong>' . htmlspecialchars($post['firstname'] . ' ' . $post['lastname']) . '</strong>';
                        echo '<span><i class="bi bi-mortarboard-fill"></i> Student</span>';
                        echo '<span class="post-time">' . htmlspecialchars(timeAgo($post['created_at'])) . '</span>';

                        echo '</div>';
                        echo '</div>';

                        echo '<div class="post-content">';
                        echo '<p>' . htmlspecialchars($post['content']) . '</p>';
                        echo '</div>';

                        if (!empty($post['media'])) {
                            echo '<div class="post-media">';
                            echo '<img src="' . htmlspecialchars($post['media']) . '" alt="Post Media">';
                            echo '</div>';
                        }
                        echo '<div class="post-footer">';
                        echo '<form method="POST" action="comment_post.php" class="comment-form">';
                        echo '<input type="hidden" name="post_id" value="' . htmlspecialchars($post['id']) . '">';
                        echo '<textarea name="comment" placeholder="Write a comment..." required></textarea>';
                        echo '<button type="submit">Post Comment</button>';
                        echo '</form>';

                        echo '<div class="post-actions">';
                        echo '<form method="POST" action="like_post.php" class="like-form">';
                        echo '<button type="submit" class="like-button">Like (' . $post['like_count'] . ')';
                        echo '<input type="hidden" class="like-button" name="post_id" value="' . htmlspecialchars($post['id']) . '">';
                        echo '<i class="bi bi-balloon-heart-fill" style="color: red;"></i> ';
                        echo '';
                        echo '</button>';
                        echo '</form>';
                        echo '<button class="comment-button" onclick="toggleComments(' . htmlspecialchars($post['id']) . ')">';
                        echo '<i class="bi bi-chat-square-dots-fill" style="color: blue;"></i> ';
                        echo '<span>Comment</span> (<span class="comment-count">' . htmlspecialchars($post['comment_count']) . '</span>)';
                        echo '</button>';
                        echo '</div>';

                        echo '</div>';
                        $commentQuery = "SELECT c.*, s.firstname, s.lastname, s.image AS profile_image
                         FROM comments c 
                         JOIN student s ON c.student_id = s.id 
                         WHERE c.post_id = " . intval($post['id']) . " 
                         ORDER BY c.created_at ASC";
                        $commentResult = mysqli_query($conn, $commentQuery);
                        echo '<div class="comments" id="comments-' . htmlspecialchars($post['id']) . '">';
                        echo '<h2>Comments</h2>';
                        if ($commentResult && mysqli_num_rows($commentResult) > 0) {
                            while ($comment = mysqli_fetch_assoc($commentResult)) {
                                echo '<div class="comment">';
                                echo '<img src="images-data/' . htmlspecialchars($comment['profile_image']) . '" alt="Profile Image" class="profile-pic">';
                                echo '<strong>' . htmlspecialchars($comment['firstname'] . ' ' . $comment['lastname']) . ':</strong> ';
                                echo '<p>' . htmlspecialchars($comment['content']) . '</p>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No comments available.</p>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
        <div class="right-section">
            <center>
                <h3>Announcement</h3>
            </center>
            <?php

            $sql = "SELECT a.title, a.content, a.created_at, ad.admin_username AS admin_username ,ad.admin_name AS admin_name
        FROM announcements a 
        JOIN admin ad ON a.admin_id = ad.id 
        ORDER BY a.created_at DESC";
            $result = mysqli_query($conn, $sql);
            $query = "SELECT * FROM student WHERE id = '$student_id'";
            $studentResult = mysqli_query($conn, $query);

            if ($studentResult && mysqli_num_rows($studentResult) > 0) {
                $student = mysqli_fetch_assoc($studentResult);
            } else {
                echo "Student profile not found.";
                exit();
            }
            ?>
            <div class="announcement">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="card">
                            <div class="profile-info">
                                <strong><?php echo htmlspecialchars($row['admin_name']); ?></strong>
                                <small class="role">
                                    <i class="bi bi-people-fill"></i>
                                    <small style="margin: 0px;"><?php echo htmlspecialchars($row['admin_username']); ?></small>
                                </small>
                                <small class="time"><?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></small>
                                <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                <p><?php echo htmlspecialchars($row['content']); ?></p>
                            </div>

                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No announcements available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="./js/home.js"></script>
    <script>
        function toggleComments(postId) {
            const commentsSection = document.getElementById(`comments-${postId}`);
            if (commentsSection) {
                commentsSection.style.display = commentsSection.style.display === 'block' ? 'none' : 'block';
            }
        }
    </script>
</body>

</html>