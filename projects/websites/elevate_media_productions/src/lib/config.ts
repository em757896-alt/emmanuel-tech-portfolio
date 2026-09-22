export const site = {
  name: 'Elevate Media Productions',
  shortName: 'Elevate Media',
  tagline: 'We craft digital experiences that elevate brands.',
  description:
    'Elevate Media Productions is a digital studio building web applications, mobile apps and brand platforms that help organizations launch, grow and engage their communities.',
  email: 'elevatemediaproductions1@gmail.com',
  whatsapp: 'https://wa.me/254775333673',
  phone: 'tel:+254111275630',
  github: 'https://github.com/emmanuel-tech-resources',
  linkedin: 'https://www.linkedin.com',
  discord: 'https://discord.com',
  repoUrl: 'https://github.com/emmanuel-tech-resources/elevate-media-productions'
} as const;

export const nav = {
  main: [
    { label: 'Home', href: '/' },
    { label: 'About', href: '/about' },
    { label: 'Services', href: '/services' },
    { label: 'Work', href: '/portfolio' },
    { label: 'Skills', href: '/skills' },
    { label: 'Blog', href: '/blog' },
    { label: 'Forum', href: '/forum' },
    { label: 'Contact', href: '/contact' }
  ],
  footer: [
    { label: 'Home', href: '/' },
    { label: 'About', href: '/about' },
    { label: 'Services', href: '/services' },
    { label: 'Portfolio', href: '/portfolio' },
    { label: 'Blog', href: '/blog' },
    { label: 'Forum', href: '/forum' },
    { label: 'Contact', href: '/contact' }
  ]
} as const;

export const stats = [
  { value: '4+', label: 'Products shipped' },
  { value: '5+', label: 'Technologies mastered' },
  { value: '100%', label: 'Client satisfaction' },
  { value: '2025', label: 'Founded' }
] as const;

export const services = [
  {
    title: 'Web Applications',
    description:
      'Full-stack platforms from idea to production — portals, dashboards and tools that scale with your users.',
    icon: 'globe',
    features: ['Next.js & SvelteKit', 'REST & Serverless APIs', 'Realtime features', 'Role-based portals']
  },
  {
    title: 'Mobile Apps',
    description:
      'Native Android applications engineered for performance, privacy and delightful interaction.',
    icon: 'smartphone',
    features: ['Kotlin & Jetpack Compose', 'Biometric security', 'Offline-first sync', 'SMS intelligence']
  },
  {
    title: 'Brand & Engagement Platforms',
    description:
      'Community-centric websites and forums that turn visitors into active, loyal audiences.',
    icon: 'users',
    features: ['Discussion forums', 'Content CMS', 'Newsletters', 'Analytics dashboards']
  },
  {
    title: 'Digital Product Design',
    description:
      'Design systems, dark-mode-first interfaces and motion design that make products feel inevitable.',
    icon: 'pen-tool',
    features: ['Design tokens', 'Motion systems', 'Accessible contrast', 'Design-to-code']
  },
  {
    title: 'Civic & Social Platforms',
    description:
      'Purpose-built tools for civic organizations — compliance tracking, awareness and incident reporting.',
    icon: 'landmark',
    features: ['Bilingual support', 'Data visualization', 'Incident reporting', 'Legal awareness']
  },
  {
    title: 'Performance Engineering',
    description:
      'We measure, profile and squeeze every millisecond so your site loads fast and ranks higher.',
    icon: 'zap',
    features: ['Core Web Vitals', 'Image optimization', 'Edge caching', 'Lighthouse 95+']
  }
] as const;

export const skills = [
  {
    category: 'Frontend',
    icon: 'layout',
    items: [
      { name: 'Svelte / SvelteKit', level: 90 },
      { name: 'React / Next.js', level: 85 },
      { name: 'TypeScript', level: 88 },
      { name: 'Tailwind CSS', level: 95 },
      { name: 'JavaScript', level: 92 }
    ]
  },
  {
    category: 'Backend & Data',
    icon: 'database',
    items: [
      { name: 'PHP', level: 80 },
      { name: 'Node.js', level: 84 },
      { name: 'Supabase / PostgreSQL', level: 86 },
      { name: 'MySQL', level: 78 },
      { name: 'REST APIs', level: 88 }
    ]
  },
  {
    category: 'Mobile',
    icon: 'smartphone',
    items: [
      { name: 'Kotlin', level: 82 },
      { name: 'Jetpack Compose', level: 84 },
      { name: 'Android SDK', level: 80 },
      { name: 'Biometric & Secure Storage', level: 75 }
    ]
  },
  {
    category: 'Design & Motion',
    icon: 'palette',
    items: [
      { name: 'UI / UX Design', level: 87 },
      { name: 'Design Systems', level: 85 },
      { name: 'Three.js / WebGL', level: 76 },
      { name: 'Motion Design', level: 80 },
      { name: 'Figma', level: 82 }
    ]
  }
] as const;

export const workflow = [
  {
    step: '01',
    title: 'Discover',
    description: 'We interview stakeholders, map the problem and define success metrics before a single pixel is drawn.'
  },
  {
    step: '02',
    title: 'Design',
    description: 'Prototypes, motion studies and design systems come first — fast and honest, so decisions happen early.'
  },
  {
    step: '03',
    title: 'Build',
    description: 'Clean, typed, testable code. We ship the product in slices you can use from week one.'
  },
  {
    step: '04',
    title: 'Elevate',
    description: 'Performance audits, accessibility passes and community loops keep raising the bar after launch.'
  }
] as const;

export const faqs = [
  {
    question: 'What kind of projects does Elevate Media take on?',
    answer:
      'We build full-stack web applications, native Android mobile apps and community/brand platforms. If it needs a clean interface and a reliable backend, it is in our lane.'
  },
  {
    question: 'How long does a typical project take?',
    answer:
      'A focused MVP typically lands in 2–6 weeks depending on scope. Larger platforms are broken into milestones so you see progress from the very first sprint.'
  },
  {
    question: 'Do you work with clients outside Kenya?',
    answer:
      'Yes. We collaborate fully remote across time zones, with async-first communication and overlapping hours for synchronous design reviews.'
  },
  {
    question: 'Can you maintain a project after launch?',
    answer:
      'Absolutely. Many of our engagements include post-launch care: performance monitoring, feature evolution, security patching and community moderation tooling.'
  },
  {
    question: 'How do we get started?',
    answer:
      'Reach out through the contact page with a rough idea of your goal. We reply within 48 hours, ask sharp questions and propose a concrete first milestone.'
  }
] as const;

export const team = [
  {
    name: 'Emmanuel K.',
    role: 'Founder & Lead Developer',
    bio: 'Builder of web apps, mobile experiences and the platforms that power them.',
    initials: 'EK',
    gradient: 'linear-gradient(135deg, #6366f1, #a855f7)'
  },
  {
    name: 'You?',
    role: 'Join the team',
    bio: 'Elevate Media grows through collaboration. Contributors, designers and engineers welcome in the forum.',
    initials: '+',
    gradient: 'linear-gradient(135deg, #ec4899, #f59e0b)'
  }
] as const;