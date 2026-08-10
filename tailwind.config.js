/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        // Dainely Premium Brand Palette
        navy: {
          50:  '#f0f4ff',
          100: '#e0eaff',
          200: '#c7d7fe',
          300: '#a5bafc',
          400: '#8193f8',
          500: '#6366f1',
          600: '#1e3a8a',   // primary brand navy
          700: '#1e3068',
          800: '#162554',
          900: '#0f1a3d',
          950: '#080e22',
        },
        gold: {
          50:  '#fffbeb',
          100: '#fef3c7',
          200: '#fde68a',
          300: '#fcd34d',
          400: '#d4a017',  // primary brand gold
          500: '#b8860b',
          600: '#92690a',
          700: '#785508',
          800: '#5c4106',
          900: '#3d2c04',
        },
        sage: {
          50:  '#f6faf5',
          100: '#eaf3e8',
          200: '#d2e8ce',
          300: '#aed4a8',
          400: '#82b87a',
          500: '#5c9e53',
          600: '#4a7f42',
          700: '#3d6637',
          800: '#315029',
          900: '#243d1e',
        },
        cream: {
          50:  '#fffef9',
          100: '#fefcf0',
          200: '#fdf8e1',
          300: '#fbf0c4',
          400: '#f7e39a',
          500: '#f0d060',
        },
        // Trust signals
        trust: {
          bg:    '#f8fafc',
          line:  '#e2e8f0',
          muted: '#64748b',
        },
      },
      fontFamily: {
        // One storefront typeface — Inter for body, headings, prices, CMS copy.
        sans: ['Inter', 'system-ui', 'sans-serif'],
        serif: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Inter', 'system-ui', 'sans-serif'],
      },
      fontSize: {
        'display-2xl': ['4.5rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
        'display-xl':  ['3.75rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
        'display-lg':  ['3rem',   { lineHeight: '1.15', letterSpacing: '-0.015em' }],
        'display-md':  ['2.25rem', { lineHeight: '1.2', letterSpacing: '-0.01em' }],
        'display-sm':  ['1.875rem', { lineHeight: '1.25' }],
        'display-xs':  ['1.5rem', { lineHeight: '1.3' }],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
        '144': '36rem',
      },
      borderRadius: {
        '4xl': '2rem',
        '5xl': '3rem',
      },
      boxShadow: {
        'soft':    '0 4px 24px rgba(0, 0, 0, 0.06)',
        'medium':  '0 8px 40px rgba(0, 0, 0, 0.10)',
        'strong':  '0 16px 64px rgba(0, 0, 0, 0.15)',
        'navy':    '0 8px 32px rgba(30, 58, 138, 0.20)',
        'gold':    '0 8px 32px rgba(212, 160, 23, 0.25)',
        'card':    '0 2px 12px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.08)',
        'card-hover': '0 8px 32px rgba(0, 0, 0, 0.10), 0 2px 8px rgba(0, 0, 0, 0.06)',
      },
      animation: {
        'fade-in':      'fadeIn 0.6s ease-out forwards',
        'slide-up':     'slideUp 0.6s ease-out forwards',
        'slide-in-right': 'slideInRight 0.5s ease-out forwards',
        'pulse-soft':   'pulseSoft 3s ease-in-out infinite',
        'float':        'float 6s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          '0%':   { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%':   { opacity: '0', transform: 'translateY(24px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideInRight: {
          '0%':   { opacity: '0', transform: 'translateX(24px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
        pulseSoft: {
          '0%, 100%': { opacity: '1' },
          '50%':      { opacity: '0.7' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%':      { transform: 'translateY(-12px)' },
        },
      },
      transitionTimingFunction: {
        'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
        'bounce-soft': 'cubic-bezier(0.34, 1.56, 0.64, 1)',
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'hero-pattern': "url('/images/hero-pattern.svg')",
        'noise': "url('/images/noise.png')",
      },
      maxWidth: {
        '8xl': '88rem',
        '9xl': '96rem',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
