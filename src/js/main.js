// Your custom JS here
import './header';
import { initSlider } from './slider';
import { initAnimations } from './animations';

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize slider functionality
    initSlider();
    
    // Initialize animations
    initAnimations();

    // console.log("HELLO WORLD");

    // Quantity buttons for single product page
    if (document.body.classList.contains('single-product')) {
        const cartForm = document.querySelector('form.cart');
        // if (cartForm) {
        //     cartForm.classList.add('flex', 'items-center', 'gap-4', 'mb-6');
        // }

        const quantityBoxes = document.querySelectorAll('form.cart .quantity');

        if (quantityBoxes.length > 0) {
            quantityBoxes.forEach(box => {
                const input = box.querySelector('.qty');
                if (input) {
                    // Use a wrapper to keep original div for WooCommerce updates
                    const wrapper = document.createElement('div');
                    wrapper.className = 'quantity-wrapper flex items-center border border-gray-300 rounded-md';
                    
                    // Move input inside wrapper
                    box.appendChild(wrapper);
                    wrapper.appendChild(input);

                    const minusBtn = document.createElement('button');
                    minusBtn.type = 'button';
                    minusBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>';
                    minusBtn.className = 'minus p-3 transition-colors hover:text-teal-500';

                    const plusBtn = document.createElement('button');
                    plusBtn.type = 'button';
                    plusBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>';
                    plusBtn.className = 'plus p-3 transition-colors hover:text-teal-500';

                    input.className += ' w-12 text-center border-l border-r border-gray-300 focus:ring-0 focus:border-gray-300';
                    
                    wrapper.insertBefore(minusBtn, input);
                    wrapper.appendChild(plusBtn);

                    minusBtn.addEventListener('click', () => {
                        const currentValue = parseInt(input.value, 10);
                        const min = parseInt(input.min, 10) || 1;
                        if (currentValue > min) {
                            input.value = currentValue - 1;
                            const event = new Event('change', { bubbles: true });
                            input.dispatchEvent(event);
                        }
                    });

                    plusBtn.addEventListener('click', () => {
                        const currentValue = parseInt(input.value, 10);
                        const max = parseInt(input.max, 10);
                        if (!max || currentValue < max) {
                            input.value = currentValue + 1;
                            const event = new Event('change', { bubbles: true });
                            input.dispatchEvent(event);
                        }
                    });
                }
            });
        }

        // Style add to cart button
        const addToCartButton = document.querySelector('.single_add_to_cart_button');
        if (addToCartButton) {
            addToCartButton.classList.add(
                'bg-purple-600', 
                'text-white', 
                'font-semibold', 
                'py-3', 
                'px-6', 
                'rounded-md', 
                'hover:bg-purple-700',
                'transition-colors',
                'inline-flex',
                'items-center',
                'justify-center'
            );
            // Add new cart icon
            const cartIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.97 1.028H14.42a1 1 0 00.97-1.028l.305-1.222H17a1 1 0 000-2H3zM3 4l1.65 6.6a2 2 0 001.95 1.4H13.4a2 2 0 001.95-1.4L17 4H3z" /><path fill-rule="evenodd" d="M6 13.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm8.5 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" clip-rule="evenodd" /></svg>';
            addToCartButton.innerHTML = cartIcon + addToCartButton.innerHTML;

            // For variable products, the button can be disabled
            if (addToCartButton.classList.contains('disabled')) {
                addToCartButton.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                addToCartButton.classList.add('bg-gray-400', 'cursor-not-allowed');
            }
        }
    }
});
