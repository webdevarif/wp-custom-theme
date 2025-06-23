class SiteHeader extends HTMLElement {
  constructor() {
    super();
    // Future header-wide logic can go here
  }

  connectedCallback() {
    // This runs when the header is added to the DOM
    // Example: console.log('SiteHeader loaded');
  }
}

customElements.define('site-header', SiteHeader);

class BrowseCategory extends HTMLElement {
  constructor() {
    super();
    this.button = this.querySelector('[data-browse-toggle]');
    this.dropdown = this.querySelector('[data-browse-dropdown]');
    this.handleClick = this.handleClick.bind(this);
  }

  connectedCallback() {
    if (this.button && this.dropdown) {
      this.button.addEventListener('click', this.handleClick);
    }
  }

  disconnectedCallback() {
    if (this.button && this.dropdown) {
      this.button.removeEventListener('click', this.handleClick);
    }
  }

  handleClick(e) {
    e.preventDefault();
    this.dropdown.classList.toggle('hidden');
  }
}

customElements.define('browse-category', BrowseCategory);

// Mobile drawer toggle and accordion logic
window.addEventListener('DOMContentLoaded', () => {
  const drawer = document.getElementById('mobile-drawer');
  const panel = document.getElementById('drawer-panel');
  const backdrop = document.getElementById('drawer-backdrop');
  const closeBtn = document.getElementById('drawer-close');
  const toggle = document.getElementById('mobile-menu-toggle');

  function openDrawer() {
    drawer.classList.remove('pointer-events-none');
    backdrop.classList.remove('opacity-0', 'pointer-events-none');
    panel.classList.remove('-translate-x-full');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer() {
    drawer.classList.add('pointer-events-none');
    backdrop.classList.add('opacity-0', 'pointer-events-none');
    panel.classList.add('-translate-x-full');
    document.body.style.overflow = '';
  }
  if (toggle) toggle.addEventListener('click', openDrawer);
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
  if (backdrop) backdrop.addEventListener('click', closeDrawer);
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDrawer();
  });

  // Accordion logic
  document.querySelectorAll('.drawer-accordion [data-accordion-toggle]').forEach(btn => {
    btn.addEventListener('click', function() {
      const content = this.parentElement.querySelector('[data-accordion-content]');
      if (content) {
        content.classList.toggle('hidden');
        this.querySelector('svg').classList.toggle('rotate-180');
      }
    });
  });
}); 