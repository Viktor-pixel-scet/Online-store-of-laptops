document.addEventListener('DOMContentLoaded', function() {
    const ErrorHandler = {
        logError: function(context, error) {
            console.error(`[${context}] Error:`, error);
            this.displayUserFriendlyError(context, error);
        },
        displayUserFriendlyError: function(context, error) {
            const errorContainer = document.getElementById('global-error-container');
            if (errorContainer) {
                errorContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Помилка:</strong> ${this.getErrorMessage(context, error)}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
        },
        getErrorMessage: function(context, error) {
            const errorMessages = {
                'fetchGames': 'Не вдалося завантажити список ігор. Спробуйте пізніше.',
                'compareProducts': 'Помилка при роботі з порівнянням товарів.',
                'addToCart': 'Не вдалося додати товар до кошика.',
                'performanceTest': 'Помилка під час тестування продуктивності.',
                'default': 'Сталася неочікувана помилка. Спробуйте оновити сторінку.'
            };
            return errorMessages[context] || errorMessages['default'];
        }
    };

    const LocalStorageManager = {
        getItem: function(key, defaultValue = []) {
            try {
                const item = localStorage.getItem(key);
                return item ? JSON.parse(item) : defaultValue;
            } catch (error) {
                ErrorHandler.logError('localStorage', error);
                return defaultValue;
            }
        },
        setItem: function(key, value) {
            try {
                localStorage.setItem(key, JSON.stringify(value));
            } catch (error) {
                ErrorHandler.logError('localStorage', error);
            }
        }
    };

    const ProductComparisonManager = {
        MAX_COMPARISON_ITEMS: 4,
        init: function() {
            this.comparisonList = document.querySelector('.comparison-list');
            this.compareButtons = document.querySelectorAll('.compare-toggle');
            this.fullCompareLink = document.getElementById('full-compare-link');
            this.compareModal = document.getElementById('compareModal') ?
                new bootstrap.Modal(document.getElementById('compareModal')) : null;

            this.productComparison = LocalStorageManager.getItem('productComparison');
            this.bindEvents();
            this.updateComparisonUI();
        },
        bindEvents: function() {
            this.compareButtons.forEach(button => {
                button.addEventListener('click', this.toggleProductComparison.bind(this));
            });
        },
        toggleProductComparison: function(event) {
            event.preventDefault();
            try {
                const button = event.currentTarget;
                const productId = button.getAttribute('data-product-id');

                if (this.productComparison.includes(productId)) {
                    this.removeFromComparison(productId);
                } else if (this.productComparison.length < this.MAX_COMPARISON_ITEMS) {
                    this.addToComparison(productId);
                } else {
                    alert('Максимум 4 товари для порівняння');
                    return;
                }

                LocalStorageManager.setItem('productComparison', this.productComparison);
                this.updateComparisonUI();

                if (this.compareModal && this.productComparison.length > 0) {
                    this.compareModal.show();
                }
            } catch (error) {
                ErrorHandler.logError('compareProducts', error);
            }
        },
        addToComparison: function(productId) {
            this.productComparison.push(productId);
        },
        removeFromComparison: function(productId) {
            this.productComparison = this.productComparison.filter(id => id !== productId);
        },
        updateComparisonUI: function() {
            try {
                this.updateCompareButtonStates();
                this.updateComparisonList();
                this.updateFullCompareLink();
                this.updateCompareCountBadge();
            } catch (error) {
                ErrorHandler.logError('compareProducts', error);
            }
        },
        updateCompareButtonStates: function() {
            this.compareButtons.forEach(button => {
                const productId = button.getAttribute('data-product-id');
                button.classList.toggle('active', this.productComparison.includes(productId));
            });
        },
        updateComparisonList: function() {
            if (!this.comparisonList) return;

            this.comparisonList.innerHTML = this.productComparison.map(productId => `
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">${this.getProductName(productId)}</h5>
                            <button class="btn btn-sm btn-danger remove-compare" data-product-id="${productId}">
                                Видалити
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            this.bindRemoveCompareButtons();
        },

        getProductName: function(productId) {
            const productButton = document.querySelector(`.compare-toggle[data-product-id="${productId}"]`);
            return productButton ? productButton.getAttribute('data-product-name') : `Товар #${productId}`;
        },
        bindRemoveCompareButtons: function() {
            const removeButtons = this.comparisonList.querySelectorAll('.remove-compare');
            removeButtons.forEach(button => {
                button.addEventListener('click', (event) => {
                    const productId = event.currentTarget.getAttribute('data-product-id');
                    this.removeFromComparison(productId);
                    LocalStorageManager.setItem('productComparison', this.productComparison);
                    this.updateComparisonUI();
                });
            });
        },

        updateFullCompareLink: function() {
            if (this.fullCompareLink) {
                if (this.productComparison.length > 0) {
                    this.fullCompareLink.href = `backend/utils/compare.php?products=${this.productComparison.join(',')}`;
                    this.fullCompareLink.classList.remove('disabled');
                } else {
                    this.fullCompareLink.classList.add('disabled');
                }
            }
        },
        updateCompareCountBadge: function() {
            const compareCountBadge = document.getElementById('compare-count');
            if (compareCountBadge) {
                compareCountBadge.textContent = this.productComparison.length;
            }
        },
        updateCompareLink: function() {
            const compareLink = document.getElementById('compare-link');
            if (compareLink) {
                compareLink.classList.toggle('disabled', this.productComparison.length === 0);
            }
        }
    };

    const GameTestManager = {
        init: function() {
            this.performanceTestButtons = document.querySelectorAll('.performance-test-btn');
            this.bindEvents();
        },
        bindEvents: function() {
            this.performanceTestButtons.forEach(button => {
                button.addEventListener('click', this.initPerformanceTest.bind(this));
            });
        },
        initPerformanceTest: function(event) {
            const card = event.currentTarget.closest('.card');
            const productName = card.querySelector('.card-title').textContent;
            const productId = card.getAttribute('data-product-id');

            this.showPerformanceTestModal(productName, productId);
        },
        showPerformanceTestModal: function(productName, productId) {
            try {
                let performanceTestModal = document.getElementById('performanceTestModal');
                if (!performanceTestModal) {
                    performanceTestModal = this.createPerformanceTestModal();
                    document.body.appendChild(performanceTestModal);
                }

                this.setupModalContent(performanceTestModal, productName, productId);

                const modalInstance = new bootstrap.Modal(performanceTestModal);
                modalInstance.show();

                this.fetchGamesForTesting();
            } catch (error) {
                ErrorHandler.logError('performanceTest', error);
            }
        },
        createPerformanceTestModal: function() {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'performanceTestModal';
            return modal;
        },
        setupModalContent: function(modal, productName, productId) {
            modal.innerHTML = `
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
                                        <div class="card-body game-checkbox-container"></div>
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
            modal.setAttribute('data-product-id', productId);
        },
        fetchGamesForTesting: function() {
            fetch('backend/products/get-games.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(this.updateGameTestModal.bind(this))
                .catch(error => {
                    ErrorHandler.logError('fetchGames', error);
                    this.handleGameFetchError();
                });
        },
        handleGameFetchError: function() {
            const gameCheckboxContainer = document.querySelector('#performanceTestModal .game-checkbox-container');
            if (gameCheckboxContainer) {
                gameCheckboxContainer.innerHTML = `
                    <div class="alert alert-danger">
                        Помилка завантаження ігор. Спробуйте пізніше.
                    </div>
                `;
            }
        },
        updateGameTestModal: function(games) {
            const gameCheckboxContainer = document.querySelector('#performanceTestModal .game-checkbox-container');

            if (!gameCheckboxContainer) {
                ErrorHandler.logError('fetchGames', new Error('Game checkbox container not found'));
                return;
            }

            gameCheckboxContainer.innerHTML = games.map(game => `
                <div class="form-check">
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
                </div>
            `).join('');

            this.setupGameTestEventListeners(games);
        },
        setupGameTestEventListeners: function(games) {
            const modalCheckboxes = document.querySelectorAll('#performanceTestModal .game-checkbox');
            const modalTestResults = document.getElementById('modal-test-results');

            modalCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const selectedGames = Array.from(modalCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => ({
                            code: cb.value,
                            name: cb.nextElementSibling.textContent,
                            minFps: parseInt(cb.getAttribute('data-min-fps')),
                            maxFps: parseInt(cb.getAttribute('data-max-fps'))
                        }));

                    this.updateTestResults(selectedGames);
                });
            });
        },
        updateTestResults: function(selectedGames) {
            const modalTestResults = document.getElementById('modal-test-results');
            const productName = document.querySelector('#performanceTestModal .modal-title').textContent.split(': ')[1];
            const productId = document.getElementById('performanceTestModal').getAttribute('data-product-id');

            if (selectedGames.length === 0) {
                modalTestResults.innerHTML = '<div class="alert alert-info">Оберіть ігри для тестування</div>';
                return;
            }

            const resultsHTML = selectedGames.map(game => {
                const result = this.runGameTest(game, productName, productId);
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
        },
        runGameTest: function(game, productName, productId) {
            const fps = Math.floor(Math.random() * (game.maxFps - game.minFps + 1)) + game.minFps;
            const stability = Math.floor(Math.random() * 20) + 80;

            this.savePerformanceTest(game, fps, stability, productId);

            return { fps, stability, productName: game.name };
        },
        savePerformanceTest: function(game, fps, stability, productId) {
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
            }).catch(error => {
                ErrorHandler.logError('performanceTest', error);
            });
        }
    };

    const CartManager = {
        init: function() {
            this.addToCartButtons = document.querySelectorAll('a[href^="cart.php?action=add"]');
            this.bindEvents();
        },
        bindEvents: function() {
            this.addToCartButtons.forEach(button => {
                button.addEventListener('click', this.handleAddToCart.bind(this));
            });
        },
        handleAddToCart: function(event) {
            try {
                const productElement = event.currentTarget.closest('.card')?.querySelector('.card-title')
                    || document.querySelector('h1');

                if (productElement) {
                    const productName = productElement.textContent;
                    this.showAddToCartMessage(productName);
                }
            } catch (error) {
                ErrorHandler.logError('addToCart', error);
            }
        },
        showAddToCartMessage: function(productName) {
            console.log(`Added ${productName} to cart`);
        }
    };

    function initializeManagers() {
        ProductComparisonManager.init();
        GameTestManager.init();
        CartManager.init();
    }
    initializeManagers();
});