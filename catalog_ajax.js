$(document).ready(function() {
    let lastUpdateTime = 0; // Время последнего обновления
    let pollingInterval = 300; // Интервал опроса в миллисекундах (30 секунд)
    let pollingTimer;

    function startPolling() {
        // Останавливаем предыдущий таймер, если он был
        if (pollingTimer) clearTimeout(pollingTimer);
        
        // Запускаем новый таймер
        pollingTimer = setTimeout(function() {
            checkForUpdates();
        }, pollingInterval);
    }

    function checkForUpdates() {
        $.ajax({
            url: 'ajax_catalog.php',
            type: 'GET',
            data: { ...getCurrentFilters(), last_update: lastUpdateTime },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Если есть обновления или это первый запрос
                    if (response.last_updated > lastUpdateTime || lastUpdateTime === 0) {
                        lastUpdateTime = response.last_updated;
                        renderProducts(response.products);
                    }
                }
                // В любом случае продолжаем опрос
                startPolling();
            },
            error: function() {
                // При ошибке тоже продолжаем опрос
                startPolling();
            }
        });
    }

    function loadProducts(filters = {}) {
        $('#products-container').html(`
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Загрузка товаров...</p>
            </div>
        `);

        $.ajax({
            url: 'ajax_catalog.php',
            type: 'GET',
            data: { ...filters, last_update: lastUpdateTime },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    lastUpdateTime = response.last_updated || Date.now();
                    renderProducts(response.products);
                } else {
                    showErrorMessage(response.message || 'Ошибка загрузки товаров');
                }
                startPolling(); // Запускаем опрос после загрузки
            },
            error: function() {
                showErrorMessage('Ошибка соединения с сервером');
                startPolling(); // Запускаем опрос даже при ошибке
            }
        });
    }

    function renderProducts(products) {
        if (products.length === 0) {
            $('#products-container').html(`
                <div class="no-products">
                    <p>Товары не найдены</p>
                </div>
            `);
            return;
        }

        let html = '';
        products.forEach(product => {
            html += `
                <div class="product-card" data-product-id="${product.id}">
                    <a href="product.php?id=${product.id}" class="product-link">
                        <div class="product-image">
                            ${product.image_path ? 
                                `<img src="${product.image_path}" alt="${product.name}">` : 
                                '<div class="image-placeholder"></div>'}
                        </div>
                        <h3>${product.name}</h3>
                        <p class="price">${product.formatted_price}</p>
                        ${product.gold_sample ? 
                            `<p class="sample">Проба: ${product.gold_sample}</p>` : ''}
                        <p class="category">Категория: ${product.category}</p>
                    </a>
                </div>
            `;
        });

        $('#products-container').html(html);
    }

    function showErrorMessage(message) {
        $('#products-container').html(`
            <div class="error-message">
                <p>${message}</p>
            </div>
        `);
    }

    $('#product-search').on('input', function() {
        const searchText = $(this).val().trim();
        const filters = getCurrentFilters();
        filters.search = searchText;
        loadProducts(filters);
    });

    $('.filter-select').change(function() {
        loadProducts(getCurrentFilters());
    });

    function getCurrentFilters() {
        return {
            category: $('#category-filter').val(),
            sort: $('#sort-filter').val(),
            search: $('#product-search').val().trim()
        };
    }

    $('#product-search').focus(function() {
        if ($(this).val() === 'Я ищу...') {
            $(this).val('');
        }
    }).blur(function() {
        if ($(this).val() === '') {
            $(this).val('Я ищу...');
        }
    });

    // Инициализация
    loadProducts();
    
    // Очищаем таймер при закрытии страницы
    $(window).on('beforeunload', function() {
        if (pollingTimer) clearTimeout(pollingTimer);
    });
});