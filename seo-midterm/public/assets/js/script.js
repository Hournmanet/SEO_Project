document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tr');

    // Highlight After Optimization if it's better than Before
    rows.forEach((row, index) => {
        if(index === 0) return; // skip header
        const before = parseFloat(row.cells[1].textContent);
        const after = parseFloat(row.cells[2].textContent);

        if(!isNaN(before) && !isNaN(after)) {
            // If lower is better (page speed, image size, broken links)
            if(row.cells[0].textContent === "Page Speed (s)" || 
               row.cells[0].textContent === "Image Size (KB)" ||
               row.cells[0].textContent === "Broken Links") {
                if(after < before) row.cells[2].style.backgroundColor = '#d4edda'; // light green
            } else {
                // If higher is better (keyword coverage)
                if(after > before) row.cells[2].style.backgroundColor = '#d4edda';
            }
        }
    });
});