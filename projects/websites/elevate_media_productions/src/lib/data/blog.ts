export const blogPosts: Post[] = [
  {
    id: '1',
    title: 'Why Svelte 5 changed how I think about reactivity',
    slug: 'why-svelte-5-changed-reactivity',
    content:
      '# Runes\n\nRunes are the new reactivity primitives that give Svelte superlative fine-grained updates in plain JavaScript. Instead of `let count = 0` plus a compiler magic, you write `let count = $state(0)`.\n\n## What feels different\n\nDerived values become `$derived`, and effects become `$effect`. The mental model collapses to: what is state, what reacts to state, and what runs as a side-effect. No dependency arrays, no stale-closure traps, no memo proliferation.\n\n## Why it matters\n\nThe compiler still does the heavy lifting — it knows exactly which components depend on which value, and only those re-render. Your application ships faster because there is no virtual DOM diffing at runtime.\n\n## The practical upshot\n\nIf you have been on React for years, expect a short window of disorientation and then genuine joy. Runes are simply a better description of state.',
    excerpt:
      'Runes `$state` and `$derived` collapse years of hooks ceremony into plain JavaScript. Here is what six months of production Svelte 5 taught me.',
    cover_image: null,
    author_id: 'admin-1',
    category: 'Web Development',
    tags: ['Svelte', 'JavaScript', 'Reactivity'],
    reading_time: 4,
    published: true,
    published_at: '2025-09-05T08:00:00Z',
    created_at: '2025-09-01T08:00:00Z',
    updated_at: '2025-09-05T08:00:00Z'
  },
  {
    id: '2',
    title: 'Cutting your LCP from 4.2s to 1.1s without a CDN',
    slug: 'cutting-lcp-without-a-cdn',
    content:
      '# The moving parts\n\nLargest Contentful Paint is the meta moment a page proves itself. Here is the checklist that took one project from 4.2s to 1.1s.\n\n## 1. Serve the hero as WebP, sized to container\n\nA 300KB PNG optimized to a 45KB WebP at the exact rendered width cut down the biggest single asset we had. Use `srcset` and `sizes` liberally.\n\n## 2. Preload the critical font\n\nFonts are a top LCP contributor. Fetch the subset, add `font-display: swap`, and preload the main webfont with a `crossorigin` hint.\n\n## 3. Inline what first paint cannot wait for\n\nThe darkest pattern is blocking on a render-blocking script. Move it to `defer`, inline critical CSS above the fold and let the full stylesheet load async.\n\n## 4. Give the server nothing to do\n\nStatic pages should be static. CDN or not, cache aggressively with immutable headers on hashed assets, and never run dynamic queries on the critical path.\n\n## Result\n\n1.1s LCP, 100/100 Lighthouse performance, zero regressions. The playlist: measure, optimize the biggest asset, repeat.',
    excerpt:
      'WebP, font preloads, deferred scripts and aggressive caching turned a 4.2s LCP into 1.1s. The exact checklist that worked.',
    cover_image: null,
    author_id: 'admin-1',
    category: 'Performance',
    tags: ['Web Vitals', 'Performance', 'LCP'],
    reading_time: 5,
    published: true,
    published_at: '2025-08-01T08:00:00Z',
    created_at: '2025-07-28T08:00:00Z',
    updated_at: '2025-08-01T08:00:00Z'
  },
  {
    id: '3',
    title: 'From 3D campus to production: lessons from a Three.js portal',
    slug: 'threejs-campus-production-lessons',
    content:
      '# Graphics on the web, pragmatically\n\nShipping a 3D campus inside a student portal was ambitious. Here is what worked and what we would redo.\n\n## Progressive enhancement is everything\n\nOur 3D scene gracefully degrades to a 2D illustrated map when WebGL is unavailable, when the device is low-end or when the user prefers reduced motion. Detection is cheap; dignity for users is priceless.\n\n## Import maps over bundles\n\nWe used import-map-driven module graphs to load Three.js only where needed, shaving hundreds of kilobytes from the initial payload.\n\n## Budget your draw calls\n\nOur campus model was optimized to a few dozen draw calls. Additive blending and fancy materials look great in demos and melt framerates in the field.\n\n## The takeaway\n\nUsers remember the moment a school felt futuristic — but they never forgive a janky scroll. Performance is the first feature.',
    excerpt:
      'Shipping a 3D campus for real students taught us import maps, progressive enhancement and why draw calls are sacred.',
    cover_image: null,
    author_id: 'admin-1',
    category: 'Web Development',
    tags: ['Three.js', 'WebGL', 'Performance', '3D'],
    reading_time: 5,
    published: true,
    published_at: '2025-07-15T08:00:00Z',
    created_at: '2025-07-10T08:00:00Z',
    updated_at: '2025-07-15T08:00:00Z'
  },
  {
    id: '4',
    title: 'Building a privacy-first expense tracker with SMS parsing',
    slug: 'privacy-first-expense-tracker-sms',
    content:
      '# Respect the inbox\n\nTrackSpend automatically categorizes expenses from bank SMS. Getting the privacy model right was the hardest part of the project.\n\n## The architecture\n\nA background BroadcastReceiver only reacts to messages from trusted bank senders, matching against a local allow-list of sender IDs. Parsing happens entirely on-device. Nothing leaves the phone until the user explicitly syncs.\n\n## Why not server-side parsing?\n\nServer-side means your bank data is one breach from exposure. On-device parsing keeps the threat surface small: minimal permissions, zero network calls in the background path, encryption at rest via Android Keystore.\n\n## The lesson\n\nPrivacy is a feature you design in, not a checkbox you bolt on. Products that respect user data build trust that ads cannot buy.',
    excerpt:
      'How TrackSpend detects bank SMS without ever seeing bank data on a server — and why the on-device approach wins.',
    cover_image: null,
    author_id: 'admin-1',
    category: 'Mobile',
    tags: ['Kotlin', 'Android', 'Privacy', 'SMS'],
    reading_time: 4,
    published: true,
    published_at: '2025-06-20T08:00:00Z',
    created_at: '2025-06-15T08:00:00Z',
    updated_at: '2025-06-20T08:00:00Z'
  }
];

export function getBlogPosts(): Post[] {
  return blogPosts.filter((p) => p.published);
}

export function getPostBySlug(slug: string): Post | undefined {
  return blogPosts.find((p) => p.slug === slug);
}

export function getFeaturedPosts(): Post[] {
  return getBlogPosts().slice(0, 2);
}