document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('productModal');
    const modalContent = document.getElementById('modalFormContent');
    const closeBtn = document.querySelector('.close-modal');

    // Add Product button
    document.querySelector('.admin-btn').addEventListener('click', function(e) {
        e.preventDefault();
        fetch('products.php?action=add')
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
                modal.style.display = 'block';
            });
    });

    // Edit buttons
    document.querySelectorAll('.action-btn.edit').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-productid');
            fetch(`products.php?action=edit&id=${productId}`)
                .then(response => response.text())
                .then(html => {
                    modalContent.innerHTML = html;
                    modal.style.display = 'block';
                });
        });
    });

    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close when clicking outside modal
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Handle cancel button
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('cancel-modal')) {
            modal.style.display = 'none';
        }
    });

    // Image preview
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    });
});

function confirmDelete(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        window.location.href = `products.php?action=delete&id=${productId}`;
    }
}