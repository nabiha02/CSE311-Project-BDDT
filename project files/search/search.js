const searchInput = document.getElementById('searchInput');
const liveResults = document.getElementById('liveResults');

let timeout = null;

searchInput.addEventListener('input', () => {
    clearTimeout(timeout);
    const query = searchInput.value.trim();
    if (!query) {
        liveResults.innerHTML = '';
        return;
    }

    timeout = setTimeout(() => {
        fetch(`search.php?q=${encodeURIComponent(query)}&ajax=1`)
            .then(res => res.text())
            .then(data => {
                liveResults.innerHTML = data;
            });
    }, 300); // waits 300ms after typing stops
});
