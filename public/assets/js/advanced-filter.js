document.addEventListener('DOMContentLoaded', function() {
    const ERROR_MESSAGES = {
        networkError: 'Помилка мережі. Перевірте підключення до інтернету.',
        serverError: 'Помилка сервера. Спробуйте пізніше.',
        invalidInput: 'Введіть коректні числові значення для ціни',
        negativePrice: 'Ціна не може бути від\'ємною',
        invalidPriceRange: 'Мінімальна ціна не може бути більшою за максимальну',
        noProducts: 'Не знайдено товарів за заданими параметрами',
        parseError: 'Помилка обробки відповіді від сервера',
        timeout: 'Час очікування відповіді вичерпано'
    };

    const form = document.getElementById('advanced-filter');

    const errorMessageContainer = document.createElement('div');
    errorMessageContainer.classList.add('alert', 'alert-danger', 'error-container');
    errorMessageContainer.style.display = 'none';
    form.insertBefore(errorMessageContainer, form.firstChild);

    function logError(errorType, details = {}) {
        console.error(`[Помилка фільтрації - ${errorType}]`, {
            timestamp: new Date().toISOString(),
            ...details
        });
    }

    function displayErrorMessage(message, options = {}) {
        const {
            persistent = false,
            type = 'danger'
        } = options;

        if (errorMessageContainer.timeoutId) {
            clearTimeout(errorMessageContainer.timeoutId);
        }

        errorMessageContainer.textContent = message;
        errorMessageContainer.className = `alert alert-${type}`;
        errorMessageContainer.style.display = 'block';

        if (!persistent) {
            errorMessageContainer.timeoutId = setTimeout(() => {
                hideErrorMessage();
            }, 5000);
        }
    }

    function hideErrorMessage() {
        errorMessageContainer.textContent = '';
        errorMessageContainer.style.display = 'none';
    }

    function validatePriceInputs(minPriceInput, maxPriceInput) {
        const minPrice = parseFloat(minPriceInput.value);
        const maxPrice = parseFloat(maxPriceInput.value);

        if (isNaN(minPrice) || isNaN(maxPrice)) {
            displayErrorMessage(ERROR_MESSAGES.invalidInput);
            logError('ValidationError', {
                input: { minPrice, maxPrice }
            });
            return false;
        }

        if (minPrice < 0 || maxPrice < 0) {
            displayErrorMessage(ERROR_MESSAGES.negativePrice);
            logError('ValidationError', {
                input: { minPrice, maxPrice }
            });
            return false;
        }

        if (maxPrice > 0 && minPrice > maxPrice) {
            displayErrorMessage(ERROR_MESSAGES.invalidPriceRange);
            logError('ValidationError', {
                input: { minPrice, maxPrice }
            });
            return false;
        }

        return true;
    }

    function fetchProducts(formData) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => {
            controller.abort();
            displayErrorMessage(ERROR_MESSAGES.timeout);
            logError('TimeoutError');
        }, 10000);

        return fetch('index.php?' + new URLSearchParams(formData).toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: controller.signal
        })
            .then(response => {
                clearTimeout(timeoutId);

                if (!response.ok) {
                    logError('ServerError', {
                        status: response.status,
                        statusText: response.statusText
                    });
                    throw new Error(`HTTP помилка! Статус: ${response.status}`);
                }
                return response.text();
            })
            .catch(error => {
                clearTimeout(timeoutId);

                if (error.name === 'AbortError') {
                    displayErrorMessage(ERROR_MESSAGES.timeout, { persistent: true });
                } else if (error instanceof TypeError) {
                    displayErrorMessage(ERROR_MESSAGES.networkError);
                    logError('NetworkError', { message: error.message });
                } else {
                    displayErrorMessage(ERROR_MESSAGES.serverError);
                    logError('FetchError', { message: error.message });
                }
                throw error;
            });
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        hideErrorMessage();

        const minPriceInput = form.querySelector('input[name="min_price"]');
        const maxPriceInput = form.querySelector('input[name="max_price"]');
        const productContainer = document.querySelector('.row .col-md-9 .row');

        if (!validatePriceInputs(minPriceInput, maxPriceInput)) {
            return;
        }

        const formData = new FormData(form);

        fetchProducts(formData)
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newProducts = doc.querySelector('.row .col-md-9 .row');

                if (!newProducts || newProducts.children.length === 0) {
                    displayErrorMessage(ERROR_MESSAGES.noProducts, { type: 'warning' });
                    logError('NoProductsError');
                    return;
                }

                productContainer.innerHTML = newProducts.innerHTML;
            })
            .catch(error => {

            });
    }

    form.addEventListener('submit', handleFormSubmit);

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('advanced-filter');

        form.addEventListener('reset', function() {
            const minPriceInput = form.querySelector('input[name="min_price"]');
            const maxPriceInput = form.querySelector('input[name="max_price"]');

            // Явно встановлюємо 0
            minPriceInput.value = 0;
            maxPriceInput.value = 0;

            // Додаємо submit після скидання
            setTimeout(() => {
                form.dispatchEvent(new Event('submit'));
            }, 0);
        });
    });

    const priceInputs = form.querySelectorAll('input[name="min_price"], input[name="max_price"]');
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '');

            const matches = this.value.match(/\./g);
            if (matches && matches.length > 1) {
                this.value = this.value.replace(/\.+/, '.');
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = e.clipboardData.getData('text/plain').replace(/[^0-9.]/g, '');
            this.value = pastedText;
        });
    });

    window.addEventListener('unhandledrejection', function(event) {
        displayErrorMessage(ERROR_MESSAGES.serverError);
        logError('UnhandledRejection', {
            reason: event.reason
        });
        event.preventDefault();
    });
});