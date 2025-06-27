document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    const productGrid = document.querySelector('.products-grid');
    let productCards = Array.from(document.querySelectorAll('.product-card'));
    let allProducts = [];

    // Получаем исходные данные о продуктах (для сброса фильтрации)
    productCards.forEach(card => {
        allProducts.push({
            element: card,
            name: card.querySelector('h3').textContent.toLowerCase(),
            price: parseFloat(card.querySelector('.price').textContent.replace(/\s/g, '').replace('₽', '')),
            category: card.dataset.category || '',
            material: card.dataset.material || ''
        });
    });

    // Оптимизированный обработчик поиска с задержкой
    searchInput.addEventListener('input', debounce(function() {
        const searchTerm = this.value.trim().toLowerCase();
        
        if (searchTerm === '') {
            resetSearch();
            return;
        }

        filterProducts(searchTerm);
    }, 300));

    // Плейсхолдер с анимацией
    searchInput.addEventListener('focus', function() {
        this.placeholder = '';
        this.classList.add('focused');
    });
    
    searchInput.addEventListener('blur', function() {
        if (this.value === '') {
            this.placeholder = 'Я ищу...';
            this.classList.remove('focused');
        }
    });

    // Кнопка очистки поиска
    const clearSearch = document.createElement('span');
    clearSearch.className = 'clear-search';
    clearSearch.innerHTML = '&times;';
    clearSearch.style.display = 'none';
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        searchInput.focus();
        resetSearch();
        this.style.display = 'none';
    });
    searchInput.parentNode.appendChild(clearSearch);

    searchInput.addEventListener('input', function() {
        clearSearch.style.display = this.value ? 'block' : 'none';
    });

    // Функция фильтрации продуктов
    function filterProducts(term) {
        let hasResults = false;
        
        allProducts.forEach(product => {
            const matchesSearch = product.name.includes(term);
            
            if (matchesSearch) {
                product.element.style.display = 'block';
                hasResults = true;
            } else {
                product.element.style.display = 'none';
            }
        });

        showNoResultsMessage(!hasResults);
    }

    // Сброс поиска
    function resetSearch() {
        allProducts.forEach(product => {
            product.element.style.display = 'block';
        });
        showNoResultsMessage(false);
    }

    // Сообщение "Ничего не найдено"
    function showNoResultsMessage(show) {
        let noResults = document.getElementById('no-results-message');
        
        if (show && !noResults) {
            noResults = document.createElement('div');
            noResults.id = 'no-results-message';
            noResults.className = 'no-results';
            noResults.textContent = 'Ничего не найдено. Попробуйте изменить запрос.';
            productGrid.appendChild(noResults);
        } else if (!show && noResults) {
            noResults.remove();
        }
    }

    // Функция задержки для оптимизации поиска
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
});