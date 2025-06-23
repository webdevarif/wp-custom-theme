document.addEventListener('DOMContentLoaded', function() {
    const couponCodes = document.querySelectorAll('.coupon-code');
    const discountContent = document.querySelector('.discount-content');
    
    const copyToClipboard = async (text) => {
        // Try using the Clipboard API first
        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (err) {
                console.error('Clipboard API failed:', err);
            }
        }
        
        // Fallback for older browsers
        try {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            
            // Make the textarea out of viewport
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            
            textArea.focus();
            textArea.select();
            
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            
            return successful;
        } catch (err) {
            console.error('Fallback clipboard copy failed:', err);
            return false;
        }
    };
    
    const showPopup = () => {
        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.style.cssText = `
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(4px);
            z-index: 1;
            opacity: 0;
            border-radius: 8px;
        `;
        
        // Create popup container
        const popup = document.createElement('div');
        popup.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            background: white;
            color: #000;
            padding: 8px 16px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 500;
            z-index: 2;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            opacity: 0;
        `;
        popup.textContent = 'Copied to clipboard!';
        
        // Add to DOM
        discountContent.style.position = 'relative';
        discountContent.appendChild(backdrop);
        discountContent.appendChild(popup);
        
        // Animate with GSAP
        const tl = gsap.timeline();
        
        tl.to(backdrop, {
            opacity: 1,
            duration: 0.3,
            ease: "power2.out"
        })
        .to(popup, {
            opacity: 1,
            scale: 1,
            duration: 0.3,
            ease: "power2.out"
        }, "-=0.1")
        .to([popup, backdrop], {
            opacity: 0,
            duration: 0.3,
            ease: "power2.in",
            onComplete: () => {
                discountContent.removeChild(popup);
                discountContent.removeChild(backdrop);
            }
        }, "+=1.5");
    };
    
    couponCodes.forEach(couponCode => {
        couponCode.style.cursor = 'pointer';
        
        // Add hover effect
        couponCode.addEventListener('mouseenter', () => {
            gsap.to(couponCode, {
                scale: 1.05,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        
        couponCode.addEventListener('mouseleave', () => {
            gsap.to(couponCode, {
                scale: 1,
                duration: 0.3,
                ease: "power2.out"
            });
        });
        
        couponCode.addEventListener('click', async function() {
            const code = this.textContent.trim();
            const success = await copyToClipboard(code);
            
            if (success) {
                showPopup();
            }
        });
    });
});
