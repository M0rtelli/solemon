// Активация мобильного меню
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-left');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            const isVisible = navMenu.style.display === 'flex';
            navMenu.style.display = isVisible ? 'none' : 'flex';
            
            // Анимация иконки
            menuToggle.classList.toggle('active', !isVisible);
        });
    }
});