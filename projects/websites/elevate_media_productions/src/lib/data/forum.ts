export const forumCategories: ForumCategory[] = [
  {
    id: '1',
    name: 'Announcements',
    slug: 'announcements',
    description: 'Official Elevate Media news, product launches and community updates.',
    icon: 'megaphone',
    color: '#6366f1',
    thread_count: 4,
    post_count: 12,
    order: 1
  },
  {
    id: '2',
    name: 'Projects & Portfolio',
    slug: 'projects-portfolio',
    description: 'Showcase your projects, request reviews or discover work from the community.',
    icon: 'rocket',
    color: '#ec4899',
    thread_count: 8,
    post_count: 31,
    order: 2
  },
  {
    id: '3',
    name: 'Web Development',
    slug: 'web-development',
    description: 'Svelte, Next.js, React, TypeScript, CSS — all things building for the web.',
    icon: 'code',
    color: '#14b8a6',
    thread_count: 14,
    post_count: 58,
    order: 3
  },
  {
    id: '4',
    name: 'Mobile & Native',
    slug: 'mobile-native',
    description: 'Kotlin, Compose, React Native and everything that ships to a pocket.',
    icon: 'smartphone',
    color: '#8b5cf6',
    thread_count: 6,
    post_count: 22,
    order: 4
  },
  {
    id: '5',
    name: 'Backend & Databases',
    slug: 'backend-databases',
    description: 'Supabase, PostgreSQL, APIs, auth and serverless architecture.',
    icon: 'database',
    color: '#f59e0b',
    thread_count: 9,
    post_count: 27,
    order: 5
  },
  {
    id: '6',
    name: 'Show & Tell',
    slug: 'show-and-tell',
    description: 'Bragging rights section. Share screenshots, demos and wins.',
    icon: 'trophy',
    color: '#22c55e',
    thread_count: 11,
    post_count: 40,
    order: 6
  }
];

export const forumThreads: Array<
  Omit<ForumThread, 'slug'> & { author_name: string; category_name: string }
> = [
  {
    id: '1',
    title: 'Welcome to the Elevate Media community! Read this first',
    content:
      'Welcome to the Elevate Media Productions community forum!\n\nThis space is for developers, designers, clients and curious minds to connect around the work we build — and the craft behind it.\n\nSome ground rules:\n\n1. Be kind and constructive.\n2. Stay on topic — there is a category for everything.\n3. Share generously; you learn fastest when you teach.\n4. No spam or self-promotion outside the Show & Tell category.\n\nSay hello below and introduce yourself! Tell us what you are building and where you are from.',
    author_id: 'admin-1',
    author_name: 'Emmanuel K.',
    category_id: '1',
    category_name: 'Announcements',
    pinned: true,
    locked: true,
    upvotes: 42,
    reply_count: 8,
    last_reply_at: '2025-09-10T09:30:00Z',
    created_at: '2025-01-20T08:00:00Z',
    updated_at: '2025-09-10T09:30:00Z'
  },
  {
    id: '2',
    title: 'Elevate Media Productions site is live — build notes',
    content:
      'The new brand platform is finally live! Here are the build notes:\n\n- Powered by SvelteKit + Svelte 5 runes\n- Tailwind CSS v4 with a custom design-token system\n- Forum, blog and dashboard all first-party\n- Deployed to Vercel with adaptive edge rendering\n\nI would love your feedback on the animations and the forum experience. Anything that feels heavy or sluggish — tell me in the comments!',
    author_id: 'admin-1',
    author_name: 'Emmanuel K.',
    category_id: '1',
    category_name: 'Announcements',
    pinned: true,
    locked: false,
    upvotes: 24,
    reply_count: 5,
    last_reply_at: '2025-09-11T07:45:00Z',
    created_at: '2025-09-01T10:00:00Z',
    updated_at: '2025-09-11T07:45:00Z'
  },
  {
    id: '3',
    title: 'Showcase your best project — lets see the stacks',
    content:
      'Drop a link to the project you are most proud of. Include your stack, what problem it solves and one hard-won lesson. I will review everything and give honest feedback.',
    author_id: '2',
    author_name: 'Nyambura W.',
    category_id: '2',
    category_name: 'Projects & Portfolio',
    pinned: false,
    locked: false,
    upvotes: 18,
    reply_count: 12,
    last_reply_at: '2025-09-09T15:20:00Z',
    created_at: '2025-08-12T11:00:00Z',
    updated_at: '2025-09-09T15:20:00Z'
  },
  {
    id: '4',
    title: 'Svelte 5 runes vs React hooks — 6 months in',
    content:
      'After six months of production Svelte 5, here is my honest take:\n\nPros:\n- $state / $derived are so much cleaner than useState/useMemo\n- Zero dependency overhead; the compiler does the work\n- Fine-grained reactivity with no re-render waterfalls\n\nCons:\n- Smaller ecosystem than React\n- Team onboarding requires unlearning hooks\n\nWould I switch a React codebase? Not wholesale. Would I start new Svelte projects? Absolutely. Anyone else made the jump?',
    author_id: '3',
    author_name: 'Carlos M.',
    category_id: '3',
    category_name: 'Web Development',
    pinned: false,
    locked: false,
    upvotes: 37,
    reply_count: 15,
    last_reply_at: '2025-09-10T18:05:00Z',
    created_at: '2025-08-22T14:00:00Z',
    updated_at: '2025-09-10T18:05:00Z'
  },
  {
    id: '5',
    title: 'Supabase Auth vs rolling your own JWT flow',
    content:
      'I keep going back and forth. Supabase gives me auth, storage, realtime and a Postgres in one dashboard. But there is magic hand-waving I do not fully control.\n\nThoughts on when to go managed vs self-hosted auth for a community product?',
    author_id: '4',
    author_name: 'Linda A.',
    category_id: '5',
    category_name: 'Backend & Databases',
    pinned: false,
    locked: false,
    upvotes: 21,
    reply_count: 9,
    last_reply_at: '2025-09-08T10:10:00Z',
    created_at: '2025-08-30T09:00:00Z',
    updated_at: '2025-09-08T10:10:00Z'
  },
  {
    id: '6',
    title: 'Tracking bank SMS on Android without being creepy',
    content:
      'Building TrackSpend taught me a lot about the line between useful and invasive. The SMS receiver only sees messages from known bank senders, does zero network calls itself and keeps everything on-device until you choose to sync.\n\nPrivacy-first automation is the only sustainable way to build this. Happy to answer questions about the architecture.',
    author_id: '5',
    author_name: 'Kamau T.',
    category_id: '4',
    category_name: 'Mobile & Native',
    pinned: false,
    locked: false,
    upvotes: 29,
    reply_count: 7,
    last_reply_at: '2025-09-05T19:30:00Z',
    created_at: '2025-09-01T08:00:00Z',
    updated_at: '2025-09-05T19:30:00Z'
  }
];

const replies: Array<ForumReply & { author_name: string; author_role: string }> = [];

export function getForumCategories(): ForumCategory[] {
  return forumCategories;
}

export function getForumThreads(): typeof forumThreads {
  return forumThreads;
}

export function getThreadById(id: string) {
  return forumThreads.find((t) => t.id === id);
}

export { replies as forumReplies };