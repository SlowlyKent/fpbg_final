document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Sorting functionality
    const sortableHeaders = document.querySelectorAll('th.sortable');
    let currentSort = {
        column: 'date', // Default sort by date
        direction: 'desc' // Default sort direction
    };

    function compareValues(a, b, isAsc) {
        // Remove currency symbol and commas for amount comparison
        if (a.includes('₱')) {
            a = parseFloat(a.replace('₱', '').replace(/,/g, ''));
            b = parseFloat(b.replace('₱', '').replace(/,/g, ''));
        }
        
        // Handle date comparison
        if (a.includes('AM') || a.includes('PM')) {
            return isAsc ? 
                new Date(a) - new Date(b) :
                new Date(b) - new Date(a);
        }
        
        // Regular string/number comparison
        if (typeof a === 'number' && typeof b === 'number') {
            return isAsc ? a - b : b - a;
        }
        
        return isAsc ? 
            a.toString().localeCompare(b.toString()) :
            b.toString().localeCompare(a.toString());
    }

    sortableHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const column = header.dataset.sort;
            const columnIndex = Array.from(header.parentElement.children).indexOf(header);

            // Reset other headers
            sortableHeaders.forEach(h => {
                if (h !== header) {
                    h.classList.remove('asc', 'desc');
                }
            });

            // Toggle sort direction
            if (currentSort.column === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
            }

            // Update header classes
            header.classList.remove('asc', 'desc');
            header.classList.add(currentSort.direction);

            // Sort the table
            const tbody = document.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            const sortedRows = rows.sort((a, b) => {
                let aValue, bValue;
                
                // Get the correct cell based on the column
                const aCell = a.children[columnIndex];
                const bCell = b.children[columnIndex];
                
                // Extract values based on column type
                switch(column) {
                    case 'product':
                        aValue = aCell.querySelector('strong').textContent.trim();
                        bValue = bCell.querySelector('strong').textContent.trim();
                        break;
                    case 'amount':
                        aValue = aCell.textContent.trim();
                        bValue = bCell.textContent.trim();
                        break;
                    case 'date':
                        aValue = aCell.textContent.trim();
                        bValue = bCell.textContent.trim();
                        break;
                    default:
                        aValue = aCell.textContent.trim();
                        bValue = bCell.textContent.trim();
                }

                return compareValues(aValue, bValue, currentSort.direction === 'asc');
            });

            // Clear and re-append sorted rows
            while (tbody.firstChild) {
                tbody.removeChild(tbody.firstChild);
            }

            sortedRows.forEach(row => tbody.appendChild(row));
        });
    });

    // Trigger initial sort on Transaction Date (desc)
    const dateHeader = document.querySelector('th[data-sort="date"]');
    if (dateHeader) {
        dateHeader.click();
    }
});