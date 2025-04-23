// JavaScript to handle tab switching
document.addEventListener('DOMContentLoaded', function() {
    const menuItems = document.querySelectorAll('.menu-item');
    const sections = document.querySelectorAll('.profile-section');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (!this.classList.contains('logout')) {
                e.preventDefault();
                
                // Remove active class from all menu items
                menuItems.forEach(mi => mi.classList.remove('active'));
                
                // Add active class to clicked menu item
                this.classList.add('active');
                
                // Hide all sections
                sections.forEach(section => section.classList.add('hidden'));
                
                // Show the target section
                const targetId = this.getAttribute('href').substring(1);
                document.getElementById(targetId).classList.remove('hidden');
            }
        });
    });
    
    // Add confirmation dialog for profile update
    const confirmUpdateBtn = document.getElementById('confirm-update');
    if (confirmUpdateBtn) {
        confirmUpdateBtn.addEventListener('click', function() {
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('email').value;
            const currentPassword = document.getElementById('currentPassword').value;
            
            if (!currentPassword) {
                alert('Please enter your current password to confirm changes.');
                return;
            }
            
            const message = `Are you sure you want to save these changes?\n\nFirst Name: ${firstName}\nLast Name: ${lastName}\nEmail: ${email}\n\nYour password will be verified before changes are applied.`;
            
            if (confirm(message)) {
                document.getElementById('profile-form').submit();
            }
        });
    }
});