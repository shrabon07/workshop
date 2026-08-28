/** Tailwind CSS config — AURORA CYBER design system */
module.exports = {
  content: [
    './*.php',
    './includes/**/*.php',
    './account/**/*.php',
    './admin/**/*.php',
    './api/**/*.php',
    './assets/js/**/*.js',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Brand gradient: Deep Teal → Bright Cyan
        brand: {
          DEFAULT: '#0F766E',
          deep: '#0F766E',
          light: '#06B6D4',
          50: '#ecfdf9',
          100: '#d2f6ef',
          400: '#2dd4bf',
          500: '#14b8a6',
        },
        // Accent gradient: Neon Indigo → Electric Purple
        accent: {
          DEFAULT: '#6366F1',
          neon: '#6366F1',
          electric: '#A855F7',
        },
        ink: {
          950: '#020617', // slate-950
          900: '#0f172a', // slate-900
        },
      },
      fontFamily: {
        sans: ['Outfit', "'Noto Sans Bengali'", 'system-ui', 'sans-serif'],
        bangla: ["'Noto Sans Bengali'", 'Outfit', 'system-ui', 'sans-serif'],
      },
      backdropBlur: {
        xs: '2px',
        glass: '22px',
        '4xl': '48px',
      },
      lineHeight: {
        tighter: '1.1',
      },
      keyframes: {
        'aurora-shift': {
          '0%, 100%': { transform: 'translate3d(0,0,0) scale(1) rotate(0deg)', opacity: '.7' },
          '33%':       { transform: 'translate3d(6%,-4%,0) scale(1.15) rotate(8deg)', opacity: '1' },
          '66%':       { transform: 'translate3d(-5%,5%,0) scale(.95) rotate(-6deg)', opacity: '.8' },
        },
        'float-y': {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%':      { transform: 'translateY(-12px)' },
        },
        'shimmer': {
          '0%': { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
        'pulse-ring': {
          '0%': { transform: 'scale(.9)', opacity: '.7' },
          '70%': { transform: 'scale(1.6)', opacity: '0' },
          '100%': { opacity: '0' },
        },
        'gradient-x': {
          '0%, 100%': { backgroundPosition: '0% 50%' },
          '50%': { backgroundPosition: '100% 50%' },
        },
      },
      animation: {
        'aurora': 'aurora-shift 18s ease-in-out infinite',
        'float-y': 'float-y 7s ease-in-out infinite',
        'shimmer': 'shimmer 6s linear infinite',
        'pulse-ring': 'pulse-ring 2.2s ease-out infinite',
        'gradient-x': 'gradient-x 6s ease infinite',
      },
      boxShadow: {
        'neon-teal': '0 0 0 1px rgba(6,182,212,.25), 0 8px 40px -8px rgba(15,118,110,.55)',
        'neon-accent': '0 0 0 1px rgba(168,85,247,.3), 0 8px 40px -8px rgba(99,102,241,.5)',
        glass: 'inset 0 1px 0 rgba(255,255,255,.06), 0 24px 60px -24px rgba(2,6,23,.9)',
      },
    },
  },
  plugins: [],
};