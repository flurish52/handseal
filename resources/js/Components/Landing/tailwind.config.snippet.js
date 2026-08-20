/**
 * If these tokens aren't already registered in tailwind.config.js (per the
 * HandSeal design system referenced in the handoff doc), add this block to
 * theme.extend. Every class used across the Landing/* components below
 * assumes these exist — nothing uses arbitrary hex values directly.
 */
module.exports = {
  theme: {
    extend: {
      colors: {
        seal: {
          navy: '#101a30',
          'navy-2': '#16223e',
          'navy-3': '#0b1324',
          brass: '#c79a46',
          'brass-light': '#e7c577',
          'brass-dim': '#8a6a30',
          paper: '#f7f1e1',
          'paper-2': '#efe5cc',
          ink: '#1b1810',
          muted: '#75694f',
          'muted-dark': '#a9b2c8',
          sage: '#5f7a5b',
          'sage-light': '#e7ede4',
          danger: '#a8442e',
          'danger-light': '#f4e3de',
          line: 'rgba(27,24,16,.14)',
          'line-dark': 'rgba(231,197,119,.18)',
        },
      },
      fontFamily: {
        serif: ['Fraunces', 'Georgia', 'serif'],
        mono: ['"IBM Plex Mono"', 'monospace'],
      },
      borderRadius: {
        card: '14px',
      },
    },
  },
};
