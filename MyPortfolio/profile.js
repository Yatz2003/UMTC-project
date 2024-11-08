document.getElementById('uploadBtn').onclick = function() {
    document.getElementById('fileInput').click();
};

document.getElementById('fileInput').onchange = function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {

            const img = document.createElement("img");
            img.src = e.target.result;
            img.classList.add("uploaded-photo");

            const photoContainer = document.querySelector('.profile-photo');
            photoContainer.innerHTML = '';
            photoContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
};
