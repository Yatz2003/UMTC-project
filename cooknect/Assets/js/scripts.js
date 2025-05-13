// Function to toggle password visibility
function togglePassword(id) {
  var x = document.getElementById(id);
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}

document.addEventListener("DOMContentLoaded", function () {
  // Get SITE_URL from a data attribute or inline script
  const siteUrl =
    document.querySelector("header").getAttribute("data-site-url") || "/";

  // Handle Reaction Button Clicks
  document.querySelectorAll(".reaction-btn").forEach((button) => {
    button.addEventListener("click", function () {
      if (button.hasAttribute("disabled")) {
        alert("Please log in to react to recipes!");
        return;
      }

      const recipeId = button.getAttribute("data-recipe-id");
      const reactionCountSpan = button.querySelector(".reaction-count");
      let reactionCount = parseInt(reactionCountSpan.textContent) || 0;

      console.log("Reacting to recipe ID:", recipeId); // Debug log

      // Send AJAX request to toggle reaction
      fetch(`${siteUrl}/api/reactions.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          recipe_id: recipeId,
          type: "like",
        }),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then((data) => {
          console.log("API Response:", data); // Debug log
          if (data.error) {
            alert(data.error);
          } else if (data.success) {
            // Update UI with new count and toggle reacted state
            reactionCountSpan.textContent = data.count;
            if (data.reacted) {
              button.classList.add("reacted");
            } else {
              button.classList.remove("reacted");
            }
          } else {
            alert("Error updating reaction. Please try again.");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred. Please check the console for details.");
        });
    });
  });

  // Handle Share Button Clicks
  document.querySelectorAll(".share-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const recipeId = button.getAttribute("data-recipe-id");
      const recipeTitle = button.getAttribute("data-recipe-title");
      const recipeUrl = `${siteUrl}/recipes/view.php?id=${recipeId}`;
      const shareText = `Check out this recipe: "${recipeTitle}" on Cooknect! ${recipeUrl}`;

      console.log("Sharing recipe:", recipeTitle, recipeUrl); // Debug log

      if (navigator.share) {
        // Use Web Share API if supported
        navigator
          .share({
            title: `Cooknect - ${recipeTitle}`,
            text: shareText,
            url: recipeUrl,
          })
          .then(() => console.log("Successfully shared"))
          .catch((err) => {
            console.error("Error sharing:", err);
            alert("Sharing failed. Falling back to copy: " + shareText);
          });
      } else {
        // Fallback to copying to clipboard
        navigator.clipboard
          .writeText(shareText)
          .then(() => {
            alert(
              "Recipe link and message copied to clipboard! Share it with your friends."
            );
          })
          .catch((err) => {
            console.error("Error copying link:", err);
            alert("Failed to copy link. Please copy it manually: " + shareText);
          });
      }
    });
  });
});
