document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('a[href^="cart.php?action=add"]');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const productElement = button.closest('.card').querySelector('.card-title');
            if (productElement) {
                const productName = productElement.textContent;

                showAddToCartMessage(productName);
            }
        });
    });

    document.addEventListener('click', function(event) {
        if (event.target.matches('.btn-close')) {
            const toast = event.target.closest('.toast');
            if (toast) {
                toast.remove();
            }
        }
    });
});

function showAddToCartMessage(productName) {
    const messageElement = document.createElement('div');
    messageElement.className = 'toast show position-fixed bottom-0 end-0 m-3';
    messageElement.setAttribute('role', 'alert');
    messageElement.setAttribute('aria-live', 'assertive');
    messageElement.setAttribute('aria-atomic', 'true');

    messageElement.innerHTML = `
        <div class="toast-header bg-success text-white">
            <strong class="me-auto">Товар додано до кошика</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Закрити"></button>
        </div>
        <div class="toast-body">
            ${productName} було успішно додано до вашого кошика.
        </div>
    `;

    document.body.appendChild(messageElement);

    setTimeout(() => {
        messageElement.remove();
    }, 3000);
}