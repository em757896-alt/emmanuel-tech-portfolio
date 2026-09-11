export type ThemeMode = 'light' | 'dark';

const THEME_KEY = 'emp-theme';

export function getInitialTheme(): ThemeMode {
  if (typeof window === 'undefined') return 'dark';
  const saved = localStorage.getItem(THEME_KEY);
  if (saved === 'light' || saved === 'dark') return saved;
  return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
}

export function applyTheme(theme: ThemeMode) {
  const root = document.documentElement;
  if (!root) return;
  if (theme === 'dark') {
    root.classList.add('dark');
    root.setAttribute('data-theme', 'dark');
  } else {
    root.classList.remove('dark');
    root.setAttribute('data-theme', 'light');
  }
  localStorage.setItem(THEME_KEY, theme);
}