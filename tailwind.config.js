module.exports = {
  content: ['./fallbacks/**/*.html', './app/Views/**/*.php', './public/assets/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        brand: { blue: '#114b9a', dark: '#0c3572', soft: '#eef6ff' },
        cream: '#fbfaf7',
        stone: '#f3f0e8',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        serif: ['Source Serif 4', 'Georgia', 'serif'],
      },
    },
  },
  plugins: [],
};
