<?php
require_once 'config.php';
require_once 'header.php';
?>

<main>
    <div class="sorting-filters">
        <div class="filter-group">
            <!-- Левая часть - фильтры категорий -->
            <div class="left-filters">
                <select class="filter-select" id="category-filter">
                    <option value="all">Все категории</option>
                    <option value="кольца">Кольца</option>
                    <option value="подвески">Подвески</option>
                    <option value="браслеты">Браслеты</option>
                    <option value="шнуры и цепи">Шнуры и цепи</option>
                </select>
            </div>
            
            <!-- Центральная часть - строка поиска -->
            <div class="search-container">
                <input type="text" id="product-search" class="search-input" placeholder="Я ищу..." value="">
            </div>
            
            <!-- Правая часть - фильтры сортировки -->
            <div class="right-filters">
                <select class="filter-select" id="sort-filter">
                    <option value="popular">По популярности</option>
                    <option value="price-asc">По возрастанию цены</option>
                    <option value="price-desc">По убыванию цены</option>
                    <option value="newest">Сначала новые</option>
                </select>
            </div>
        </div>
    </div>

    <div class="products-grid" id="products-container">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Загрузка товаров...</p>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="catalog_ajax.js"></script>

<?php require_once 'footer.php'; ?>