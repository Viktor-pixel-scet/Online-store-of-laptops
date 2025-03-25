document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('advanced-filter');
    const productContainer = document.querySelector('.row .col-md-9 .row');
    const minPriceInput = form.querySelector('input[name="min_price"]');
    const maxPriceInput = form.querySelector('input[name="max_price"]');

    function resetPriceInputs() {
        minPriceInput.value = 0;
        maxPriceInput.value = 0;
    }

    resetPriceInputs();

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);

        fetch('index.php?' + new URLSearchParams(formData).toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newProducts = doc.querySelector('.row .col-md-9 .row');
                productContainer.innerHTML = newProducts.innerHTML;
            })
            .catch(error => {
                console.error('Помилка фільтрації:', error);
            });
    });

    form.addEventListener('reset', function() {
        resetPriceInputs();

        setTimeout(() => {
            form.dispatchEvent(new Event('submit'));
        }, 0);
    });
});