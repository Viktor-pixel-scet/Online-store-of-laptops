document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('a[href^="cart.php?action=add"]');
    const comparisonList = document.querySelector('.comparison-list');
    const compareModal = document.getElementById('compareModal') ? new bootstrap.Modal(document.getElementById('compareModal')) : null;
    const compareButtons = document.querySelectorAll('.compare-toggle');
    const fullCompareLink = document.getElementById('full-compare-link');

    let productComparison = JSON.parse(localStorage.getItem('productComparison') || '[]');

    function updateComparisonUI() {
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

            // Update localStorage
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

    function fetchGamesForTesting() {
        fetch('backend/products/get-games.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Реакція мережі була незадовільною');
                }
                return response.json();
            })
            .then(games => {
                console.log('Ігри принесли:', games);
                updateGameTestModal(games);
            })
            .catch(error => {
                console.error('Ігри, що виводять помилки:', error);
                const gameCheckboxContainer = document.querySelector('#performanceTestModal .card-body');
                if (gameCheckboxContainer) {
                    gameCheckboxContainer.innerHTML = `
                    <div class="alert alert-danger">
                        Помилка завантаження ігор: ${error.message}
                    </div>
                `;
                }
            });
    }

    function updateGameTestModal(games) {
        const gameCheckboxContainer = document.querySelector('#performanceTestModal .card-body');

        if (!gameCheckboxContainer) {
            console.error('Контейнер з ігровими чекбоксами не знайдено');
            return;
        }

        gameCheckboxContainer.innerHTML = '';

        games.forEach(game => {
            const checkboxDiv = document.createElement('div');
            checkboxDiv.className = 'form-check';
            checkboxDiv.innerHTML = `
                <input class="form-check-input game-checkbox" 
                       type="checkbox" 
                       value="${game.game_code}" 
                       id="modal-${game.game_code}-test"
                       data-min-fps="${game.min_fps}"
                       data-max-fps="${game.max_fps}"
                       data-category="${game.category}">
                <label class="form-check-label" for="modal-${game.game_code}-test">
                    ${game.game_name} (${game.category})
                </label>
            `;
            gameCheckboxContainer.appendChild(checkboxDiv);
        });

        setupGameTestEventListeners(games);
    }

    function setupGameTestEventListeners(games) {
        const modalCheckboxes = document.querySelectorAll('#performanceTestModal .game-checkbox');
        const modalTestResults = document.getElementById('modal-test-results');

        modalCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const selectedGames = Array.from(modalCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => ({
                        code: cb.value,
                        name: cb.nextElementSibling.textContent,
                        minFps: parseInt(cb.getAttribute('data-min-fps')),
                        maxFps: parseInt(cb.getAttribute('data-max-fps'))
                    }));

                if (selectedGames.length === 0) {
                    modalTestResults.innerHTML = '<div class="alert alert-info">Оберіть ігри для тестування</div>';
                    return;
                }

                const productName = document.querySelector('#performanceTestModal .modal-title')
                    .textContent
                    .split(': ')[1];

                const productId = document.getElementById('performanceTestModal').getAttribute('data-product-id');

                const resultsHTML = selectedGames.map(game => {
                    const result = runGameTest(game, productName, productId);
                    return `
                        <div class="card mb-3">
                            <div class="card-header">${game.name}</div>
                            <div class="card-body">
                                <p>Середній FPS: <strong>${result.fps}</strong></p>
                                <p>Стабільність: <strong>${result.stability}%</strong></p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: ${result.stability}%; 
                                         background-color: ${result.stability > 90 ? 'green' : result.stability > 70 ? 'orange' : 'red'}" 
                                         aria-valuenow="${result.stability}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                modalTestResults.innerHTML = resultsHTML;
            });
        });
    }

    function runGameTest(game, productName, productId) {
        const fps = Math.floor(Math.random() * (game.maxFps - game.minFps + 1)) + game.minFps;
        const stability = Math.floor(Math.random() * 20) + 80;

        fetch('/save-performance-test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                game: game.code,
                fps: fps,
                stability: stability,
                productId: productId
            })
        });

        return { fps, stability, productName: game.name };
    }

    const productCards = document.querySelectorAll('.card');
    productCards.forEach(card => {
        const productName = card.querySelector('.card-title').textContent;
        const productId = card.getAttribute('data-product-id');

        const performanceTestButton = document.createElement('button');
        performanceTestButton.className = 'btn btn-outline-secondary btn-sm mt-2';
        performanceTestButton.innerHTML = '<i class="bi bi-controller"></i> Тест Продуктивності';

        performanceTestButton.addEventListener('click', function() {
            let performanceTestModal = document.getElementById('performanceTestModal');
            if (!performanceTestModal) {
                performanceTestModal = createPerformanceTestModal();
                document.body.appendChild(performanceTestModal);
            }

            performanceTestModal.innerHTML = `
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">🎮 Тестування Продуктивності: ${productName}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card mb-3">
                                        <div class="card-header">Ігрові Тести</div>
                                        <div class="card-body">

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header">Результати Тестування</div>
                                        <div class="card-body" id="modal-test-results">
                                            <div class="alert alert-info">Оберіть ігри для тестування</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            performanceTestModal.setAttribute('data-product-id', productId);

            const modalInstance = new bootstrap.Modal(performanceTestModal);
            modalInstance.show();

            fetchGamesForTesting();
        });

        card.querySelector('.card-body').appendChild(performanceTestButton);
    });

    function createPerformanceTestModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'performanceTestModal';
        return modal;
    }

    function showAddToCartMessage(productName) {
        console.log(`Added ${productName} to cart`);
    }

    function setupProductGallery() {
        console.log('Product gallery setup');
    }

    setupProductGallery();
});