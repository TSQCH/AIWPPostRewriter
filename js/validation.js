document.addEventListener('DOMContentLoaded', function () {
    const postRangeInput = document.getElementById('post_range');
    if (postRangeInput) {
        const errorMessage = document.createElement('span');
        errorMessage.id = 'error-message';
        errorMessage.style.color = 'red';
        errorMessage.style.display = 'none';
        errorMessage.textContent = 'The left value must be smaller than the right value.';
        postRangeInput.parentNode.appendChild(errorMessage);

        postRangeInput.addEventListener('input', function () {
            const value = this.value.trim();
            const regex = /^\d+-\d+$/;

            if (regex.test(value)) {
                const [left, right] = value.split('-').map(Number);
                if (left > right) {
                    errorMessage.style.display = 'inline';
                    this.setCustomValidity('The left value must be smaller than or equal to the right value.');
                } else {
                    errorMessage.style.display = 'none';
                    this.setCustomValidity('');
                }
            } else {
                errorMessage.style.display = 'none';
                this.setCustomValidity('');
            }
        });
    }
});