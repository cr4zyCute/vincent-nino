 
 
 document.addEventListener("DOMContentLoaded", () => {
    // Handle likes
    document.querySelectorAll(".like-button").forEach(button => {
        button.addEventListener("click", () => {
            const postId = button.getAttribute("data-post-id");
            fetch("likes.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `post_id=${postId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        const likeCount = button.querySelector("i");
                        let count = parseInt(likeCount.textContent.match(/\d+/)[0]);
                        likeCount.textContent = data.action === "liked" ? `Like (${++count})` : `Like (${--count})`;
                    }
                });
        });
    });

    // Handle comments
// Handle comments
document.querySelectorAll(".comment-form").forEach(form => {
    form.addEventListener("submit", e => {
        e.preventDefault();

        const postId = form.querySelector("[name='post_id']").value;
        const comment = form.querySelector("[name='comment']").value;

        fetch("comment_post.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `post_id=${postId}&comment=${encodeURIComponent(comment)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    // Append new comment dynamically
                    const commentsSection = document.querySelector(`#comments-${postId}`);
                    const newComment = document.createElement("div");
                    newComment.classList.add("comment");
                    newComment.innerHTML = `
                        <img src="images-data/${data.profile_image}" alt="Profile Image" class="profile-pic">
                        <strong>${data.firstname} ${data.lastname}:</strong> 
                        <p>${comment}</p>
                    `;
                    commentsSection.appendChild(newComment);
                    form.reset();
                } else {
                    console.error(data.message);
                    alert("Failed to post comment: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
    });
});

});

 function openPopup() {
    document.getElementById('post-popup').style.display = 'flex';
  }

  function closePopup() {
    document.getElementById('post-popup').style.display = 'none';
  }



  let allFiles = []; // Global array to store all selected files

  function triggerFileUpload() {
    document.getElementById('media-upload').click();
  }

  function previewFiles(event) {
    const newFiles = Array.from(event.target.files); // Get newly selected files

    // Append new files to the global file list
    allFiles = [...allFiles, ...newFiles];

    // Update the preview display
    displayFiles(allFiles);

  
    toggleDeleteButton(allFiles.length > 0);
  }

 function displayFiles(files) {
    const previewContainer = document.getElementById('media-preview');
    previewContainer.innerHTML = ''; 

    files.forEach((file, index) => {
      if (index >= 4) return; 

      const fileType = file.type.split('/')[0]; 
      const previewElement = document.createElement('div');
      previewElement.className = 'media-item';

      if (fileType === 'image') {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = file.name;
        previewElement.appendChild(img);
      } else if (fileType === 'video') {
        const video = document.createElement('video');
        video.src = URL.createObjectURL(file);
        video.controls = true;
        video.alt = file.name;
        previewElement.appendChild(video);
      }
      previewContainer.appendChild(previewElement);
    });

    if (files.length > 4) {
      const overlayElement = document.createElement('div');
      overlayElement.className = 'media-overlay';
      overlayElement.textContent = `+${files.length - 4}`;
      const fourthMediaItem = previewContainer.children[3];
      fourthMediaItem.appendChild(overlayElement);
    }
  }

  function clearFiles() {
    // Clear the global file array
    allFiles = [];

    // Clear the preview display
    const previewContainer = document.getElementById('media-preview');
    previewContainer.innerHTML = '';

    // Clear the file input (reset its value)
    document.getElementById('media-upload').value = '';

    // Hide the "Cancel Post" button
    toggleDeleteButton(false);
  }

  function toggleDeleteButton(show) {
    const deleteButton = document.getElementById('delete-post');
    deleteButton.style.display = show ? 'block' : 'none';
  }

// function previewFiles(event) {
//     const previewContainer = document.getElementById('media-preview');
//     previewContainer.innerHTML = '';
//     const files = event.target.files;

//     for (let file of files) {
//         const fileReader = new FileReader();
//         fileReader.onload = (e) => {
//             const media = document.createElement(file.type.startsWith('image/') ? 'img' : 'video');
//             media.src = e.target.result;
//             media.controls = file.type.startsWith('video/');
//             media.classList.add('preview-media');
//             previewContainer.appendChild(media);
//         };
//         fileReader.readAsDataURL(file);
//     }
// }
document.addEventListener('DOMContentLoaded', function () {
    const commentButtons = document.querySelectorAll('.comment-button');

    commentButtons.forEach(button => {
        button.addEventListener('click', function () {
            const postId = button.getAttribute('data-post-id');
            const commentsDiv = document.getElementById('comments-' + postId);

            if (commentsDiv.style.display === 'none' || commentsDiv.style.display === '') {
                commentsDiv.style.display = 'block';
            } else {
                commentsDiv.style.display = 'none';
            }
        });
    });
});
function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post?')) {
        // Send an AJAX request to delete the post
        fetch(`delete_post.php?id=${postId}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Post deleted successfully.');
                    location.reload(); // Reload the page or remove the post dynamically
                } else {
                    alert('Error deleting the post.');
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

    function deletePost(postId) {
        if (confirm('Are you sure you want to delete this post?')) {
            // Send an AJAX request to delete the post
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_post.php', true);  // Point to delete_post.php
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert(xhr.responseText);  // Display response from server
                    location.reload();  // Reload the page to reflect changes
                }
            };
            xhr.send('delete_post_id=' + postId);  // Send the post ID to delete
        }
    }

