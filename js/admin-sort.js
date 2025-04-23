/**
 * Table sorting functionality for admin tables
 */
document.addEventListener('DOMContentLoaded', function() {
    // Find all tables with sortable headers
    const tables = document.querySelectorAll('.admin-table');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th.sortable');
        
        headers.forEach(header => {
            header.addEventListener('click', function() {
                const index = Array.from(header.parentNode.children).indexOf(header);
                const currentIsAsc = header.classList.contains('asc');
                
                // Remove all sort classes from all headers
                headers.forEach(h => {
                    h.classList.remove('asc', 'desc');
                });
                
                // Add appropriate class to the clicked header
                if (currentIsAsc) {
                    header.classList.add('desc');
                } else {
                    header.classList.add('asc');
                }
                
                // Sort the table
                sortTable(table, index, !currentIsAsc);
            });
        });
    });
    
    /**
     * Sort table rows based on the content of cells in the specified column
     * @param {HTMLTableElement} table - The table to sort
     * @param {number} column - The index of the column to sort by
     * @param {boolean} asc - Whether to sort in ascending order
     */
    function sortTable(table, column, asc) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Remove sorted class from all cells
        table.querySelectorAll('td.sorted, th.sorted').forEach(cell => {
            cell.classList.remove('sorted');
        });
        
        // Add sorted class to the column cells
        table.querySelectorAll(`tr > :nth-child(${column + 1})`).forEach(cell => {
            cell.classList.add('sorted');
        });
        
        // Sort the rows
        const sortedRows = rows.sort((a, b) => {
            const aValue = getCellValue(a, column);
            const bValue = getCellValue(b, column);
            
            // Check if values are numbers
            const aNum = parseFloat(aValue);
            const bNum = parseFloat(bValue);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return asc ? aNum - bNum : bNum - aNum;
            }
            
            // Otherwise sort as strings
            return asc 
                ? aValue.localeCompare(bValue, undefined, {sensitivity: 'base'})
                : bValue.localeCompare(aValue, undefined, {sensitivity: 'base'});
        });
        
        // Remove all existing rows
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }
        
        // Add sorted rows
        tbody.append(...sortedRows);
    }
    
    /**
     * Get the text content of a cell
     * @param {HTMLTableRowElement} row - The table row
     * @param {number} index - The index of the cell
     * @returns {string} The text content of the cell
     */
    function getCellValue(row, index) {
        const cell = row.querySelector(`td:nth-child(${index + 1}), th:nth-child(${index + 1})`);
        return cell ? cell.textContent.trim() : '';
    }
});