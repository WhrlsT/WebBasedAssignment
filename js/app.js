import '/js/dropdownlist.js';
import '/js/homepage.js';
import '/js/profile.js';
import '/js/register.js';
import '/js/cart.js';

// Add search filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchParams = new URLSearchParams(window.location.search);
    const searchQuery = searchParams.get('search');
    
    // Only add filter buttons if there's a search query
    if (searchQuery && document.querySelector('.search-container')) {
        const filterContainer = document.createElement('div');
        filterContainer.className = 'search-filter-buttons';
        
        // Create filter buttons
        const filters = [
            { name: 'All', field: 'all' },
            { name: 'Username', field: 'username' },
            { name: 'Name', field: 'name' },
            { name: 'Email', field: 'email' }
        ];
        
        // Get current filter
        const currentFilter = searchParams.get('filter') || 'all';
        
        filters.forEach(filter => {
            const button = document.createElement('a');
            button.href = `?search=${encodeURIComponent(searchQuery)}&filter=${filter.field}`;
            button.className = `filter-button ${currentFilter === filter.field ? 'active' : ''}`;
            button.textContent = filter.name;
            filterContainer.appendChild(button);
        });
        
        // Insert after search form
        const searchContainer = document.querySelector('.search-container');
        searchContainer.appendChild(filterContainer);
    }
});