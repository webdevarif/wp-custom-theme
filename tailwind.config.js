/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './acf-blocks/**/*.php',
    './inc/**/*.php',
    './template-parts/*.php',
    './woocommerce/**/*.php',
    './src/js/**/*.js'
  ],
  theme: {
    extend: {
      colors: {
        primary: '#ff324d',
        'secondary': '#23282d',
        'dark': '#212121',
      },
      fontFamily: {
        'sans': [
          'Anek Bangla',
          '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Oxygen-Sans', 'Ubuntu', 'Cantarell', 'Helvetica Neue', 'sans-serif'
        ],
      },
      container: {
        center: true,
        padding: '20px',
        screens: {
          DEFAULT: '100%',
          sm: '100%',
          md: '100%',
          lg: '1320px',
          xl: '1320px',
          '2xl': '1320px',
        },
      },
    },
  },
  plugins: [],
  safelist: [
    // Replace regex with explicit classes
    'swiper-button-next',
    'swiper-button-prev',
    'swiper-pagination',
    'swiper-pagination-bullet',
    'swiper-pagination-bullet-active'
  ]
};