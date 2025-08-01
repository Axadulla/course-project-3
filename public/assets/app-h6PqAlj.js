import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

document.addEventListener('turbo:load', () => {
    const toggleBtn = document.getElementById('menu-toggle');
    const menuWrapper = document.getElementById('menu-wrapper');

    if (!toggleBtn || !menuWrapper) return;

    toggleBtn.addEventListener('click', () => {
        menuWrapper.classList.toggle('hidden');
    });
});
