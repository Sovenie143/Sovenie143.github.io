document.addEventListener('DOMContentLoaded', function() {
    // Обработка формы обратной связи
    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('ajax.php?action=save_feedback', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    feedbackForm.reset();
                    loadFeedback(); // Обновляем список отзывов
                } else {
                    alert('Ошибка: ' + (data.message || 'Произошла ошибка'));
                }
            })
            .catch(() => alert('Ошибка соединения с сервером'));
        });
    }

    // Функция загрузки товаров
    function loadItems() {
        fetch('ajax.php?action=get_items')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('itemsContainer');
                    container.innerHTML = data.data.map(item => `
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="${item.image_path}" class="card-img-top" alt="${item.title}">
                                <div class="card-body">
                                    <h5 class="card-title">${item.title}</h5>
                                    <p class="card-text">${item.description}</p>
                                    <p class="text-primary">${item.price.toLocaleString()} ₽</p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            });
    }

    // Функция загрузки отзывов
    function loadFeedback() {
        fetch('ajax.php?action=get_feedback')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('feedbackContainer');
                    container.innerHTML = data.data.map(item => `
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">${item.name}</h5>
                                <h6 class="card-subtitle mb-2 text-muted">${item.email}</h6>
                                <p class="card-text">${item.message}</p>
                                <small class="text-muted">${new Date(item.created_at).toLocaleString()}</small>
                            </div>
                        </div>
                    `).join('');
                }
            });
    }

    // Кнопка обновления
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            loadItems();
            loadFeedback();
        });
    }

    // Автоматическое обновление
    setInterval(() => {
        loadItems();
        loadFeedback();
    }, 30000);

    // Первоначальная загрузка
    loadItems();
    loadFeedback();
});