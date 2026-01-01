document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('fileInput');
    if (!fileInput) return;

    fileInput.addEventListener('change', () => {
        const f = fileInput.files[0];
        if (!f) return;
        const allowed = ['pdf','doc','docx','jpg','jpeg','png','csv','zip','xlsx'];
        const ext = f.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) {
            alert('File type not allowed. Allowed: ' + allowed.join(', '));
            fileInput.value = '';
        }
    });
});
