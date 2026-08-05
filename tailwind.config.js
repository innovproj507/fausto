/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./views/**/*.php'],
  theme: {
    extend: {
      colors: {
        primary: '#ed1c24',
        'primary-dark': '#b8141a',
        secondary: '#1F2937',
        accent: '#f5a623',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['"Barlow Condensed"', 'ui-sans-serif', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
