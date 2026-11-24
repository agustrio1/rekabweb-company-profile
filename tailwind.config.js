/** @type {import('tailwindcss').Config} */
export default {
  content: ['./app/Views/**/*.php', './resources/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        ink: 'rgb(var(--c-ink) / <alpha-value>)',
        paper: 'rgb(var(--c-paper) / <alpha-value>)',
        surface: 'rgb(var(--c-surface) / <alpha-value>)',
        line: 'rgb(var(--c-line) / <alpha-value>)',
        muted: 'rgb(var(--c-muted) / <alpha-value>)',
        accent: 'rgb(var(--c-accent) / <alpha-value>)',
        deep: 'rgb(var(--c-deep) / <alpha-value>)',
      },
      fontFamily: {
        serif: ['"Newsreader Variable"', 'Georgia', 'serif'],
        sans: [
          '"Public Sans Variable"',
          'system-ui',
          '-apple-system',
          'Segoe UI',
          'Roboto',
          'sans-serif',
        ],
      },
      fontSize: {
        display: [
          'clamp(2.5rem, 1.6rem + 4vw, 4.5rem)',
          { lineHeight: '1.05', letterSpacing: '-0.02em' },
        ],
        title: [
          'clamp(1.875rem, 1.4rem + 2vw, 3rem)',
          { lineHeight: '1.15', letterSpacing: '-0.015em' },
        ],
        heading: [
          'clamp(1.375rem, 1.2rem + 0.8vw, 1.75rem)',
          { lineHeight: '1.25' },
        ],
      },
      borderRadius: {
        sm: '2px',
        DEFAULT: '4px',
        md: '4px',
        lg: '6px',
      },
      maxWidth: {
        site: '1200px',
        prose: '68ch',
      },
      spacing: {
        section: 'clamp(4rem, 3rem + 5vw, 8rem)',
      },
    },
  },
  plugins: [],
};