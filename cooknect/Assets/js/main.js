document.addEventListener('DOMContentLoaded', function() {
    // Reaction buttons
    document.querySelectorAll('.reaction-btn').forEach(button => {
        button.addEventListener('click', function() {
            const recipeId = this.dataset.recipe;
            
            if (!isLoggedIn()) {
                window.location.href = '/auth/login.php';
                return;
            }
            
            fetch('/api/reactions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipe_id: recipeId,
                    type: 'like'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const countElement = this.querySelector('.count');
                    countElement.textContent = data.count;
                    
                    // Visual feedback
                    this.classList.add('active');
                    setTimeout(() => {
                        this.classList.remove('active');
                    }, 300);
                }
            });
        });
    });
    
    // Save buttons
    document.querySelectorAll('.save-btn').forEach(button => {
        button.addEventListener('click', function() {
            const recipeId = this.dataset.recipe;
            const isSaved = this.dataset.saved === 'true';
            
            if (!isLoggedIn()) {
                window.location.href = '/auth/login.php';
                return;
            }
            
            fetch('/api/saved.php', {
                method: isSaved ? 'DELETE' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipe_id: recipeId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const icon = this.querySelector('.icon');
                    const text = this.querySelector('.text');
                    
                    if (data.action === 'saved') {
                        icon.textContent = '❤️';
                        text.textContent = 'Saved';
                        this.dataset.saved = 'true';
                    } else {
                        icon.textContent = '♡';
                        text.textContent = 'Save';
                        this.dataset.saved = 'false';
                    }
                }
            });
        });
    });
    
    // Share buttons
    document.querySelectorAll('.share-btn').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            
            if (navigator.share) {
                navigator.share({
                    title: 'Check out this recipe on Cooknect',
                    url: url
                }).catch(console.error);
            } else {
                // Fallback for browsers that don't support Web Share API
                const tempInput = document.createElement('input');
                document.body.appendChild(tempInput);
                tempInput.value = url;
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                
                alert('Link copied to clipboard!');
            }
        });
    });
    
    // Reply buttons
    document.querySelectorAll('.reply-btn').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.dataset.comment;
            const replyForm = this.closest('.comment').querySelector('.reply-form');
            
            if (replyForm.style.display === 'none' || !replyForm.style.display) {
                replyForm.style.display = 'block';
            } else {
                replyForm.style.display = 'none';
            }
        });
    });
    
    // Cancel reply buttons
    document.querySelectorAll('.cancel-reply').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.reply-form').style.display = 'none';
        });
    });
});

function isLoggedIn() {
    // This would be better handled by checking a session cookie or similar
    // For demo purposes, we'll assume a logged-in user has a user_id in localStorage
    return localStorage.getItem('user_id') !== null;
}