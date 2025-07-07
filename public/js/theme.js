function initThemeToggle() {
    const toggleButton = document.getElementById('theme-toggle');
    const html = document.documentElement;

    if (!toggleButton) return;

    const savedTheme = localStorage.getItem('theme');
    const isDark = savedTheme === 'dark';
    html.classList.toggle('dark', isDark);

    // Удаляем старый обработчик
    const newBtn = toggleButton.cloneNode(true);
    toggleButton.parentNode.replaceChild(newBtn, toggleButton);

    newBtn.addEventListener('click', () => {
        const nowDark = html.classList.toggle('dark');
        localStorage.setItem('theme', nowDark ? 'dark' : 'light');
    });
}

document.addEventListener('DOMContentLoaded', initThemeToggle);
document.addEventListener('turbo:load', initThemeToggle);
