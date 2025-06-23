class SectionSlideshow extends HTMLElement {
  constructor() {
    super();
  }

  connectedCallback() {
    // Add data attribute to identify this as a slideshow
    this.setAttribute('data-slideshow', '');
  }

  disconnectedCallback() {
    // Clean up if needed
  }
}

customElements.define('section-slideshow', SectionSlideshow);
