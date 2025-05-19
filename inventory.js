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
        column: null,
        direction: 'asc'
    };

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
                const aValue = a.children[columnIndex].textContent.trim();
                const bValue = b.children[columnIndex].textContent.trim();

                if (currentSort.direction === 'asc') {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });

            // Clear and re-append sorted rows
            while (tbody.firstChild) {
                tbody.removeChild(tbody.firstChild);
            }

            sortedRows.forEach(row => tbody.appendChild(row));
        });
    });
});