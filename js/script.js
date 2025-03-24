document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('a[href^="cart.php?action=add"]');
    const comparisonList = document.querySelector('.comparison-list');
    const compareModal = document.getElementById('compareModal') ? new bootstrap.Modal(document.getElementById('compareModal')) : null;
    const compareButtons = document.querySelectorAll('.compare-toggle');
    const fullCompareLink = document.getElementById('full-compare-link');

    let productComparison = JSON.parse(localStorage.getItem('productComparison') || '[]');

    function updateComparisonUI() {
        // Оновлення кнопок на сторінці
        compareButtons.forEach(button => {
            const productId = button.getAttribute('data-product-id');
            button.classList.toggle('active', productComparison.includes(productId));
        });

        if (comparisonList) {
            comparisonList.innerHTML = productComparison.map(id => {
                const product = document.querySelector(`.compare-toggle[data-product-id="${id}"]`);
                return product ? `
                    <div class="col-3 mb-3">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                ${product.getAttribute('data-product-name')}
                                <div>
                                    <button class="btn btn-sm btn-danger remove-compare me-2" data-product-id="${id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary add-more-compare" data-product-id="${id}">
                                        <i class="bi bi-plus-square"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                ` : '';
            }).join('');

            document.querySelectorAll('.remove-compare').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    productComparison = productComparison.filter(id => id !== productId);
                    localStorage.setItem('productComparison', JSON.stringify(productComparison));
                    updateComparisonUI();
                });
            });

            document.querySelectorAll('.add-more-compare').forEach(button => {
                button.addEventListener('click', function() {
                    const currentProductId = this.getAttribute('data-product-id');
                    const compareAddButtons = Array.from(compareButtons)
                        .filter(btn => !productComparison.includes(btn.getAttribute('data-product-id')));

                    if (compareAddButtons.length > 0) {
                        compareAddButtons[0].click();
                    } else {
                        alert('Максимум 4 товари для порівняння');
                    }
                });
            });

            if (fullCompareLink) {
                if (productComparison.length > 0) {
                    fullCompareLink.href = `compare.php?products=${productComparison.join(',')}`;
                    fullCompareLink.classList.remove('disabled');
                } else {
                    fullCompareLink.classList.add('disabled');
                }
            }

            const compareCountBadge = document.getElementById('compare-count');
            if (compareCountBadge) {
                compareCountBadge.textContent = productComparison.length;
            }
        }
    }

    compareButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();

            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');

            if (productComparison.includes(productId)) {
                productComparison = productComparison.filter(id => id !== productId);
            } else if (productComparison.length < 4) {
                productComparison.push(productId);
            } else {
                alert('Максимум 4 товари для порівняння');
                return;
            }

            // Оновлення localStorage
            localStorage.setItem('productComparison', JSON.stringify(productComparison));
            updateComparisonUI();

            if (compareModal && productComparison.length > 0) {
                compareModal.show();
            }
        });
    });

    updateComparisonUI();

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const productElement = button.closest('.card') ?
                button.closest('.card').querySelector('.card-title') :
                document.querySelector('h1');

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

    setupProductGallery();
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

function setupProductGallery() {
    const mainProductImage = document.querySelector('.col-md-6 img.img-fluid');
    if (!mainProductImage) return;

    const galleryContainer = document.createElement('div');
    galleryContainer.className = 'product-thumbnails d-flex mt-3 flex-wrap';

    const mainImageSrc = mainProductImage.getAttribute('src');
    const mainImageAlt = mainProductImage.getAttribute('alt');

    const imageUrls = mainImageSrc.trim().split('\n').map(url => url.trim()).filter(url => url);

    const allImagesData = mainProductImage.getAttribute('data-all-images');
    const thumbnails = allImagesData ? JSON.parse(allImagesData) : [mainImageSrc];
    thumbnails.forEach((src, index) => {
        const thumbnail = document.createElement('div');
        thumbnail.className = 'thumbnail-container me-2 mb-2';
        thumbnail.innerHTML = `
            <img src="${src}" class="img-thumbnail ${index === 0 ? 'active' : ''}" 
                alt="${mainImageAlt} - Зображення ${index + 1}" 
                style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                data-index="${index}">
        `;

        thumbnail.querySelector('img').addEventListener('click', function() {
            mainProductImage.src = src;

            document.querySelectorAll('.product-thumbnails img').forEach(img => {
                img.classList.remove('active');
            });
            this.classList.add('active');

            currentIndex = parseInt(this.getAttribute('data-index'));
        });

        galleryContainer.appendChild(thumbnail);
    });

    mainProductImage.style.cursor = 'zoom-in';

    const zoomOverlay = document.createElement('div');
    zoomOverlay.className = 'zoom-overlay position-fixed top-0 start-0 w-100 h-100 d-none';
    zoomOverlay.style.backgroundColor = 'rgba(0,0,0,0.9)';
    zoomOverlay.style.zIndex = '1050';
    zoomOverlay.style.cursor = 'zoom-out';

    const zoomImage = document.createElement('img');
    zoomImage.className = 'position-absolute top-50 start-50 translate-middle';
    zoomImage.style.maxHeight = '90vh';
    zoomImage.style.maxWidth = '90vw';
    zoomImage.setAttribute('alt', mainImageAlt);

    zoomOverlay.appendChild(zoomImage);
    document.body.appendChild(zoomOverlay);

    let currentIndex = 0;

    mainProductImage.addEventListener('click', function() {
        zoomImage.src = this.src;
        zoomOverlay.classList.remove('d-none');
        document.body.style.overflow = 'hidden';
    });

    zoomOverlay.addEventListener('click', function(e) {
        if (e.target === this || e.target === zoomImage) {
            this.classList.add('d-none');
            document.body.style.overflow = 'auto';
        }
    });

    const prevArrow = document.createElement('button');
    prevArrow.className = 'btn btn-dark position-absolute start-0 top-50 translate-middle-y mx-3';
    prevArrow.innerHTML = '&lt;';
    prevArrow.style.zIndex = '1051';
    prevArrow.style.opacity = '0.7';
    prevArrow.setAttribute('aria-label', 'Попереднє зображення');

    const nextArrow = document.createElement('button');
    nextArrow.className = 'btn btn-dark position-absolute end-0 top-50 translate-middle-y mx-3';
    nextArrow.innerHTML = '&gt;';
    nextArrow.style.zIndex = '1051';
    nextArrow.style.opacity = '0.7';
    nextArrow.setAttribute('aria-label', 'Наступне зображення');

    zoomOverlay.appendChild(prevArrow);
    zoomOverlay.appendChild(nextArrow);

    function navigateGallery(direction) {
        if (direction === 'prev') {
            currentIndex = (currentIndex - 1 + thumbnails.length) % thumbnails.length;
        } else {
            currentIndex = (currentIndex + 1) % thumbnails.length;
        }

        zoomImage.src = thumbnails[currentIndex];

        mainProductImage.src = thumbnails[currentIndex];

        document.querySelectorAll('.product-thumbnails img').forEach(img => {
            img.classList.remove('active');
            if (parseInt(img.getAttribute('data-index')) === currentIndex) {
                img.classList.add('active');
            }
        });
    }

    prevArrow.addEventListener('click', function(e) {
        e.stopPropagation();
        navigateGallery('prev');
    });

    nextArrow.addEventListener('click', function(e) {
        e.stopPropagation();
        navigateGallery('next');
    });

    document.addEventListener('keydown', function(e) {
        if (zoomOverlay.classList.contains('d-none')) return;

        if (e.key === 'ArrowLeft') {
            navigateGallery('prev');
        } else if (e.key === 'ArrowRight') {
            navigateGallery('next');
        } else if (e.key === 'Escape') {
            zoomOverlay.classList.add('d-none');
            document.body.style.overflow = 'auto';
        }
    });

    const loadingIndicator = document.createElement('div');
    loadingIndicator.className = 'position-absolute top-50 start-50 translate-middle text-white';
    loadingIndicator.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Завантаження...</span></div>';
    zoomOverlay.appendChild(loadingIndicator);

    zoomImage.addEventListener('load', function() {
        loadingIndicator.style.display = 'none';
    });

    zoomImage.addEventListener('error', function() {
        loadingIndicator.innerHTML = '<p>Помилка завантаження зображення</p>';
    });

    const imageCounter = document.createElement('div');
    imageCounter.className = 'position-absolute bottom-0 start-50 translate-middle-x text-white p-2';
    imageCounter.style.backgroundColor = 'rgba(0,0,0,0.5)';
    imageCounter.style.borderRadius = '4px';
    zoomOverlay.appendChild(imageCounter);

    function updateCounter() {
        imageCounter.textContent = `${currentIndex + 1} / ${thumbnails.length}`;
    }

    prevArrow.addEventListener('click', updateCounter);
    nextArrow.addEventListener('click', updateCounter);
    document.addEventListener('keydown', function(e) {
        if (!zoomOverlay.classList.contains('d-none') &&
            (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
            updateCounter();
        }
    });

    updateCounter();

    const closeButton = document.createElement('button');
    closeButton.className = 'btn btn-dark position-absolute top-0 end-0 m-3';
    closeButton.innerHTML = '×';
    closeButton.style.zIndex = '1051';
    closeButton.style.opacity = '0.7';
    closeButton.setAttribute('aria-label', 'Закрити галерею');

    closeButton.addEventListener('click', function(e) {
        e.stopPropagation();
        zoomOverlay.classList.add('d-none');
        document.body.style.overflow = 'auto';
    });

    zoomOverlay.appendChild(closeButton);

    mainProductImage.parentNode.appendChild(galleryContainer);

    const style = document.createElement('style');
    style.textContent = `
        .img-thumbnail.active {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .thumbnail-container {
            transition: transform 0.2s;
        }
        
        .thumbnail-container:hover {
            transform: scale(1.05);
        }
        
        .zoom-overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .zoom-overlay.d-none {
            opacity: 0;
        }
        
        .zoom-overlay:not(.d-none) {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);

    checkImagesExistence(thumbnails);
}

function checkImagesExistence(imageSources) {
    imageSources.forEach((src, index) => {
        if (index === 0) return;

        const img = new Image();
        img.onload = function() {

        };

        img.onerror = function() {
            const thumbnail = document.querySelector(`.product-thumbnails img[data-index="${index}"]`);
            if (thumbnail) {
                thumbnail.parentElement.style.display = 'none';
            }
        };

        img.src = src;
    });
}