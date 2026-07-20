/** @type {import('tailwindcss').Config} */
// Tema token'larının tek kaynağı. Bileşenlerde sabit hex yasak; renkler
// yalnız bu token'lar üzerinden kullanılır (primary runtime'da settings'ten
// gelen --color-primary ile ezilir).
module.exports = {
  content: ['./src/**/*.{ts,tsx}'],
  presets: [require('nativewind/preset')],
  theme: {
    extend: {
      colors: {
        primary: 'var(--color-primary)',
        'on-primary': '#FFFFFF',
        'primary-press': '#115E59',
        ink: '#0F172A',
        'ink-soft': '#475569',
        'ink-muted': '#94A3B8',
        surface: '#FFFFFF',
        'surface-alt': '#F8FAFC',
        border: '#E2E8F0',
        danger: '#DC2626',
        success: '#16A34A',
        warning: '#D97706',
        'pending-fg': '#92400E',
        'pending-bg': '#FEF3C7',
        'assigned-fg': '#1E40AF',
        'assigned-bg': '#DBEAFE',
        'inprogress-fg': '#3730A3',
        'inprogress-bg': '#E0E7FF',
        'resolved-fg': '#065F46',
        'resolved-bg': '#D1FAE5',
        'closed-fg': '#334155',
        'closed-bg': '#E2E8F0',
        'cancelled-fg': '#475569',
        'cancelled-bg': '#F1F5F9',
        'rejected-fg': '#991B1B',
        'rejected-bg': '#FEE2E2',
      },
      fontFamily: {
        display: ['Inter_700Bold'],
        title: ['Inter_600SemiBold'],
        heading: ['Inter_600SemiBold'],
        semibold: ['Inter_600SemiBold'],
        medium: ['Inter_500Medium'],
        body: ['Inter_400Regular'],
      },
    },
  },
  plugins: [],
};
