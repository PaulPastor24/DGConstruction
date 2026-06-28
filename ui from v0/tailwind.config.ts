import type { Config } from 'tailwindcss'

export default {
  content: ['./app/**/*.{ts,tsx}', './components/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f5fdf4',
          100: '#e9f7e2',
          500: '#A3D65C',
          600: '#82b94a',
          700: '#5f8f34',
          900: '#1f3d12',
        },
        slate: {
          950: '#111827',
        },
      },
      boxShadow: {
        dashboard: '0 8px 24px rgba(15, 23, 42, 0.06)',
      },
      borderRadius: {
        dashboard: '18px',
      },
    },
  },
  plugins: [],
} satisfies Config
