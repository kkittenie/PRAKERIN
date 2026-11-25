import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;

Alpine.start();

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Desktop Hero 3D Cake & Decor (Member Portal, >= 1024px)
    if (window.matchMedia('(min-width: 1024px)').matches) {
        const hero3DWrapper = document.querySelector('#desktop-hero-3d-wrapper');
        if (hero3DWrapper) {
            import('./desktop-hero-3d/index').then(module => module.initHero3DCake(hero3DWrapper));
        }

        if (document.querySelector('.desktop-parallax-strawberry') && window.matchMedia('(min-width: 1280px)').matches) {
            import('./desktop-decor/strawberries').then(module => module.initStrawberries());
        }
    }
});
