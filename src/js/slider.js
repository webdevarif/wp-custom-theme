// Function to resolve target elements relative to the Swiper container
function resolveTarget(selector, element) {
    if (!selector) return null;
    // If selector starts with a dot or hash, search within the parent element
    if (selector.startsWith('.') || selector.startsWith('#')) {
        return element.querySelector(selector);
    }
    // Otherwise return the selector as is
    return selector;
}

// Function to initialize Swiper instances
export function initializeSwipers() {
    const swiperElements = document.querySelectorAll('.swiper');
    swiperElements.forEach(element => {
        // Get options from data-swiper-options attribute
        let options = {};
        try {
            const optionsStr = element.dataset.swiperOptions || element.dataset.swiperConfig;
            if (optionsStr) {
                options = JSON.parse(optionsStr);
            }
        } catch (error) {
            console.error('Error parsing Swiper options:', error);
        }

        // Dynamically resolve navigation and pagination targets
        if (options.navigation) {
            options.navigation = {
                ...options.navigation,
                nextEl: resolveTarget(options.navigation.nextEl, element),
                prevEl: resolveTarget(options.navigation.prevEl, element)
            };
        }
        if (options.pagination) {
            options.pagination = {
                ...options.pagination,
                el: resolveTarget(options.pagination.el, element)
            };
        }

        // Initialize Swiper with options
        if (typeof window.Swiper !== 'undefined') {
            new window.Swiper(element, options);
        } else {
            console.error('Swiper is not loaded properly');
        }
    });
}

// Export function to initialize slider
export function initSlider() {
    if (typeof window.Swiper !== 'undefined') {
        initializeSwipers();
        console.log('Swiper initialized successfully');
    } else {
        const checkSwiper = setInterval(() => {
            if (typeof window.Swiper !== 'undefined') {
                clearInterval(checkSwiper);
                initializeSwipers();
                console.log('Swiper initialized after waiting');
            }
        }, 100);

        // Set a timeout to stop checking after 5 seconds
        setTimeout(() => {
            clearInterval(checkSwiper);
            if (typeof window.Swiper === 'undefined') {
                console.error('Swiper failed to load after 5 seconds');
            }
        }, 5000);
    }
}