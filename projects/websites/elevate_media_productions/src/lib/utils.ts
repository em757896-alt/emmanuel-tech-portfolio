export function slugify(text: string): string {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export function formatDate(date: string | Date | null | undefined): string {
  if (!date) return '—';
  const d = new Date(date);
  return d.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}

export function timeAgo(date: string | Date | null | undefined): string {
  if (!date) return '—';
  const seconds = Math.floor((Date.now() - new Date(date).getTime()) / 1000);
  const intervals: [number, string][] = [
    [31536000, 'year'],
    [2592000, 'month'],
    [604800, 'week'],
    [86400, 'day'],
    [3600, 'hour'],
    [60, 'minute']
  ];
  for (const [secs, label] of intervals) {
    const count = Math.floor(seconds / secs);
    if (count >= 1) return `${count} ${label}${count > 1 ? 's' : ''} ago`;
  }
  return 'just now';
}

export function readingTime(text: string): number {
  const words = text.trim().split(/\s+/).length;
  return Math.max(1, Math.ceil(words / 200));
}

export function truncate(text: string, length = 120): string {
  if (text.length <= length) return text;
  return text.slice(0, length).trimEnd() + '…';
}

export function initials(name: string | null | undefined): string {
  if (!name) return '?';
  return name
    .split(' ')
    .map((n) => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();
}

export function avatarColor(seed: string): string {
  const colors = [
    'linear-gradient(135deg, #6366f1, #a855f7)',
    'linear-gradient(135deg, #ec4899, #f97316)',
    'linear-gradient(135deg, #14b8a6, #6366f1)',
    'linear-gradient(135deg, #8b5cf6, #ec4899)',
    'linear-gradient(135deg, #f59e0b, #ec4899)',
    'linear-gradient(135deg, #06b6d4, #6366f1)'
  ];
  let hash = 0;
  for (let i = 0; i < seed.length; i++) {
    hash = (hash << 5) - hash + seed.charCodeAt(i);
    hash |= 0;
  }
  return colors[Math.abs(hash) % colors.length];
}