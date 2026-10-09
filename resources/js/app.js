//
const menuToggle = document.querySelector('.menu-toggle');
const mainNavigation = document.querySelector('#main-navigation');

menuToggle?.addEventListener('click', () => {
    const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';

    menuToggle.setAttribute('aria-expanded', String(!isExpanded));
    menuToggle.setAttribute('aria-label', isExpanded ? 'Mở menu' : 'Đóng menu');
    mainNavigation?.classList.toggle('is-open', !isExpanded);
});

const storefront = document.querySelector('.cup-original');
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

if (storefront) {
    const revealItems = [...storefront.querySelectorAll('.reveal')];

    if (!prefersReducedMotion.matches && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -6% 0px',
        });

        storefront.classList.add('motion-ready');
        revealItems.forEach((item) => revealObserver.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }
}

const cupSearch = document.querySelector('#cup-search');
const cupProducts = [...document.querySelectorAll('[data-cup-product]')];
const cupFilters = [...document.querySelectorAll('[data-cup-filter]')];
const cupNoResults = document.querySelector('#cup-no-results');
let selectedCupCategory = 'all';

const filterCups = () => {
    const searchTerm = cupSearch?.value.trim().toLocaleLowerCase('vi') ?? '';
    let visibleProducts = 0;

    cupProducts.forEach((product) => {
        const matchesCategory = selectedCupCategory === 'all' || product.dataset.category === selectedCupCategory;
        const matchesSearch = product.dataset.name?.toLocaleLowerCase('vi').includes(searchTerm) ?? false;
        const isVisible = matchesCategory && matchesSearch;

        product.hidden = !isVisible;
        visibleProducts += Number(isVisible);
    });

    if (cupNoResults) {
        cupNoResults.hidden = visibleProducts > 0;
    }
};

cupSearch?.addEventListener('input', filterCups);

cupFilters.forEach((filter) => {
    filter.addEventListener('click', () => {
        selectedCupCategory = filter.dataset.cupFilter ?? 'all';

        cupFilters.forEach((button) => {
            const isSelected = button === filter;

            button.classList.toggle('is-selected', isSelected);
            button.setAttribute('aria-pressed', String(isSelected));
        });

        filterCups();
    });
});
