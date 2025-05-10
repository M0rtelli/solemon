// Обновленный скрипт с анимацией
document.querySelectorAll('.close-notification').forEach(btn => {
    btn.addEventListener('click', () => {
        const notification = btn.parentElement;
        notification.classList.add('hide');
        notification.addEventListener('animationend', () => {
            notification.remove();
        }, { once: true });
    });
});

// Автоматическое закрытие с анимацией
const notification = document.querySelector('.logout-notification');
if (notification) {
    setTimeout(() => {
        notification.classList.add('hide');
        notification.addEventListener('animationend', () => {
            notification.remove();
        }, { once: true });
    }, 5000);
}

// Обработка ввода в поиске
document.querySelector('.search-input').addEventListener('input', function (e) {
    const button = document.querySelector('.search-button');
    button.style.color = e.target.value.length > 0 ? '#6366f1' : '#94a3b8';
});



// Закрытие уведомления
const closeNotification = document.querySelector('.close-notification');
if (closeNotification) {
    closeNotification.addEventListener('click', function () {
        this.parentElement.style.display = 'none';
    });
}