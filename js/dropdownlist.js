document.addEventListener("DOMContentLoaded", function () {
    const categories = document.querySelectorAll(".category-item");
    const dropdowns = document.querySelectorAll(".mega-dropdown");

    categories.forEach(category => {
        category.addEventListener("mouseenter", function () {
            // Hide all dropdowns
            dropdowns.forEach(dropdown => dropdown.style.display = "none");

            // Show the correct dropdown
            const categoryName = category.getAttribute("data-category");
            const dropdown = document.getElementById(categoryName);
            if (dropdown) {
                dropdown.style.display = "block";
            }
        });
    });

    // Hide dropdowns when mouse leaves the navigation
    document.querySelector(".category-nav").addEventListener("mouseleave", function () {
        dropdowns.forEach(dropdown => dropdown.style.display = "none");
    });
});
