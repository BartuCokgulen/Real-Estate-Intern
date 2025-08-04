<?php
require_once 'includes/functions.php';
require_once 'config.php';
require_login();

$page_title = 'Add New Property';
$extra_css = '
<style>
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        margin: 10px;
    }
    #imagePreviewContainer {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .preview-wrapper {
        position: relative;
        display: inline-block;
    }
    .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>';

$extra_js = '
<script>
function previewImages(input) {
    const container = document.getElementById("imagePreviewContainer");
    container.innerHTML = "";
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement("div");
                wrapper.className = "preview-wrapper";
                const img = document.createElement("img");
                img.src = e.target.result;
                img.className = "image-preview";
                const removeBtn = document.createElement("button");
                removeBtn.className = "remove-image";
                removeBtn.innerHTML = "\u00d7";
                removeBtn.onclick = function() {
                    wrapper.remove();
                    const newInput = document.createElement("input");
                    newInput.type = "file";
                    newInput.name = "property_images[]";
                    newInput.multiple = true;
                    newInput.accept = "image/*";
                    newInput.style.display = "none";
                    const currentFiles = Array.from(input.files);
                    currentFiles.splice(index, 1);
                    const dataTransfer = new DataTransfer();
                    currentFiles.forEach(file => dataTransfer.items.add(file));
                    newInput.files = dataTransfer.files;
                    input.parentNode.replaceChild(newInput, input);
                    newInput.addEventListener("change", function() {
                        previewImages(this);
                    });
                };
                wrapper.appendChild(img);
                wrapper.appendChild(removeBtn);
                container.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    }
}
</script>';

require_once 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Add New Property</h1>
    <form action="process-property.php" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="title" class="form-label">Property Title*</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description*</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price* ($)</label>
                    <input type="number" class="form-control" id="price" name="price" required>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Location*</label>
                    <input type="text" class="form-control" id="location" name="location" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="type" class="form-label">Property Type*</label>
                    <select class="form-control" id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="house">House</option>
                        <option value="apartment">Apartment</option>
                        <option value="villa">Villa</option>
                        <option value="land">Land</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="bedrooms" class="form-label">Number of Bedrooms</label>
                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" min="0">
                </div>
                <div class="mb-3">
                    <label for="bathrooms" class="form-label">Number of Bathrooms</label>
                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" min="0">
                </div>
                <div class="mb-3">
                    <label for="area" class="form-label">Area (sq ft)</label>
                    <input type="number" class="form-control" id="area" name="area" min="0" step="0.01">
                </div>
                <div class="mb-3">
                    <label for="property_images" class="form-label">Property Images* (You can select multiple)</label>
                    <input type="file" class="form-control" id="property_images" name="property_images[]" accept="image/*" multiple required onchange="previewImages(this)">
                    <div id="imagePreviewContainer"></div>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Submit Property</button>
            <a href="my-properties.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?> 