// Function to preview image after selection or drop
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) {
        console.error("Preview element not found:", previewId);
        return;
    }
    const file = input.files[0];
    const reader = new FileReader();
    const placeholderTextElement = preview.querySelector('span'); // Get the placeholder span

    reader.onloadend = function () {
        // Clear previous content (image and potentially placeholder)
        preview.innerHTML = '';
        // Create and append the new image
        const img = document.createElement('img');
        img.src = reader.result;
        img.alt = "Image preview";
        preview.appendChild(img);
         // Add the placeholder text back for replacing
        const newPlaceholder = document.createElement('span');
        // Use the original text content if available, otherwise default
        const originalText = placeholderTextElement ? placeholderTextElement.textContent : 'Image';
        newPlaceholder.textContent = originalText.includes('upload')
                                        ? originalText.replace('upload', 'replace')
                                        : (originalText.includes('replace') ? originalText : `Drag & drop or click to replace ${originalText}`);
        preview.appendChild(newPlaceholder);
    }

    if (file) {
        reader.readAsDataURL(file);
    } else {
        // Optional: Reset preview if no file is selected (e.g., user cancels)
        // preview.innerHTML = '<span>Drag & drop or click to upload</span>'; // Reset to original placeholder
    }
}

// Function to trigger the hidden file input when the preview box is clicked
function triggerFileInput(inputId) {
    const inputElement = document.getElementById(inputId);
    if (inputElement) {
        inputElement.click();
    } else {
        console.error("File input element not found:", inputId);
    }
}

// Drag and Drop Handlers
function handleDragOver(event) {
    event.preventDefault(); // Prevent default behavior (opening file)
    event.stopPropagation();
    event.currentTarget.classList.add('dragover'); // Add visual cue
}

function handleDragLeave(event) {
    event.preventDefault();
    event.stopPropagation();
    event.currentTarget.classList.remove('dragover'); // Remove visual cue
}

function handleDrop(event, inputId) {
    event.preventDefault();
    event.stopPropagation();
    event.currentTarget.classList.remove('dragover');

    const files = event.dataTransfer.files;
    const fileInput = document.getElementById(inputId);
    const previewBox = event.currentTarget; // The div where the drop happened

    if (!fileInput) {
        console.error("File input element not found for drop:", inputId);
        return;
    }

    if (files.length > 0) {
        // Assign the first dropped file to the hidden file input
        // Use DataTransferItemList for better compatibility if needed
        const dt = new DataTransfer();
        dt.items.add(files[0]);
        fileInput.files = dt.files;


        // Manually trigger the preview function
        previewImage(fileInput, previewBox.id);
    }
}

// Add other general drag/drop functions from your dragdrop.js if needed
// ... existing code in dragdrop.js ...