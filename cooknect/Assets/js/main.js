document.addEventListener("DOMContentLoaded", function () {
  // Make reply buttons clickable
  document.querySelectorAll(".reply-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const commentId = this.getAttribute("data-comment");
      const replyForm = document.querySelector(
        `.reply-form[data-comment="${commentId}"]`
      );

      // Hide all other reply forms
      document.querySelectorAll(".reply-form").forEach((form) => {
        if (form !== replyForm) form.style.display = "none";
      });

      // Toggle current reply form
      if (replyForm) {
        replyForm.style.display =
          replyForm.style.display === "none" ? "block" : "none";

        // Scroll to form if opening
        if (replyForm.style.display === "block") {
          replyForm.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }
      }
    });
  });

  // Cancel reply buttons
  document.querySelectorAll(".cancel-reply").forEach((button) => {
    button.addEventListener("click", function () {
      this.closest(".reply-form").style.display = "none";
    });
  });
});

// Save buttons
document.querySelectorAll(".save-btn").forEach((button) => {
  button.addEventListener("click", function () {
    const recipeId = this.dataset.recipe;
    const isSaved = this.dataset.saved === "true";

    if (!isLoggedIn()) {
      window.location.href = "/auth/login.php";
      return;
    }

    fetch("/api/saved.php", {
      method: isSaved ? "DELETE" : "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        recipe_id: recipeId,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const icon = this.querySelector(".icon");
          const text = this.querySelector(".text");

          if (data.action === "saved") {
            icon.textContent = "❤️";
            text.textContent = "Saved";
            this.dataset.saved = "true";
          } else {
            icon.textContent = "♡";
            text.textContent = "Save";
            this.dataset.saved = "false";
          }
        }
      });
  });
});

// Share buttons
document.querySelectorAll(".share-btn").forEach((button) => {
  button.addEventListener("click", function () {
    const url = this.dataset.url;

    if (navigator.share) {
      navigator
        .share({
          title: "Check out this recipe on Cooknect",
          url: url,
        })
        .catch(console.error);
    } else {
      // Fallback for browsers that don't support Web Share API
      const tempInput = document.createElement("input");
      document.body.appendChild(tempInput);
      tempInput.value = url;
      tempInput.select();
      document.execCommand("copy");
      document.body.removeChild(tempInput);

      alert("Link copied to clipboard!");
    }
  });
});

// Reply buttons
document.querySelectorAll(".reply-btn").forEach((button) => {
  button.addEventListener("click", function () {
    const commentId = this.dataset.comment;
    const replyForm = this.closest(".comment").querySelector(".reply-form");

    if (replyForm.style.display === "none" || !replyForm.style.display) {
      replyForm.style.display = "block";
    } else {
      replyForm.style.display = "none";
    }
  });
});

// Cancel reply buttons
document.querySelectorAll(".cancel-reply").forEach((button) => {
  button.addEventListener("click", function () {
    this.closest(".reply-form").style.display = "none";
  });
});
function isLoggedIn() {
  // This would be better handled by checking a session cookie or similar
  // For demo purposes, we'll assume a logged-in user has a user_id in localStorage
  return localStorage.getItem("user_id") !== null;
}
