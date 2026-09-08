module.exports = {
  content: ['./resources/views/**/*.blade.php', './public/assets/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        brand: {
          blue: 'rgb(var(--brand-primary) / <alpha-value>)',
          dark: 'rgb(var(--brand-primary-hover) / <alpha-value>)',
          soft: 'rgb(var(--brand-soft) / <alpha-value>)',
        },
        cream: 'rgb(var(--background-default) / <alpha-value>)',
        stone: 'rgb(var(--background-muted) / <alpha-value>)',
        ink: 'rgb(var(--text-primary) / <alpha-value>)',
      },
      fontFamily: {
        sans: ['var(--font-sans)', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        serif: ['var(--font-serif)', 'Source Serif 4', 'Georgia', 'serif'],
      },
      borderRadius: {
        card: 'var(--radius-card)',
        pill: 'var(--radius-pill)',
      },
      boxShadow: {
        card: 'var(--shadow-card)',
      },
    },
  },
  plugins: [],
};
