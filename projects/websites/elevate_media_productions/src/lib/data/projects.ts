export const projects: Project[] = [
  {
    id: '1',
    title: 'Elevate Media University',
    slug: 'elevate-media-university',
    description:
      'A full-stack university management platform with dedicated portals for students, teachers and administrators — course management, attendance, results and digital learning.',
    long_description:
      'Elevate Media University is a complete institution management platform built to modernize the academic experience. It ships with three dedicated role-based portals: students can enrol in courses, view results and access learning materials; staff can manage classes, mark attendance and update grades; administrators get a full analytics dashboard with user and department management. The platform uses serverless API routes for rapid iteration and a PostgreSQL database for reliable, structured storage with Auth.js-powered JWT sessions for secure role-based access.',
    image_url: '',
    demo_url: 'https://elevate-media-dun.vercel.app',
    github_url: 'https://github.com/emmanuel-tech-resources/elevate-media-university',
    tech_tags: ['Next.js', 'React 19', 'TypeScript', 'Tailwind CSS', 'Supabase', 'Prisma', 'Auth.js'],
    category: 'Web Application',
    featured: true,
    order: 1,
    created_at: '2025-01-15T00:00:00Z',
    updated_at: '2025-06-01T00:00:00Z'
  },
  {
    id: '2',
    title: 'Civic Compliance Hub',
    slug: 'civic-compliance-system',
    description:
      'A civic participation and compliance platform for Kenya — legal awareness tools, compliance tracking, incident reporting and responsive analytics dashboards.',
    long_description:
      'Civic Compliance Hub is built for civic organizations and the public: a bilingual (English/Kiswahili) platform that raises legal awareness, provides compliance-maturity self-assessment tools, monitors civic space through incident reporting and visualises trends with interactive charts. It features a friendly AI legal-assistant chatbot that answers frequently asked questions around civic rights and compliance obligations.',
    image_url: '',
    demo_url: 'https://civiccompliancehub.gt.tc/',
    github_url: 'https://github.com/emmanuel-tech-resources/civic-compliance-system',
    tech_tags: ['PHP', 'MySQL', 'Bootstrap', 'JavaScript', 'Chart.js', 'jQuery'],
    category: 'Civic Platform',
    featured: true,
    order: 2,
    created_at: '2025-03-01T00:00:00Z',
    updated_at: '2025-08-10T00:00:00Z'
  },
  {
    id: '3',
    title: 'Student Management System',
    slug: 'student-management-system',
    description:
      'A student management platform with an interactive 3D campus, live timetables, digital library and a powerful admin dashboard — powered by Three.js.',
    long_description:
      'This platform reimagines the student portal with an immersive rendered 3D campus that students can explore, paired with practical everyday tools: live timetables, a searchable digital library and a comprehensive admin dashboard for academic records. Built with a custom vanilla-JavaScript design system and Three.js 3D rendering (with a graceful 2D fallback), it demonstrates performance-conscious engineering without heavyweight frameworks.',
    image_url: '',
    demo_url: 'https://studentmanagement.gt.tc/',
    github_url: 'https://github.com/emmanuel-tech-resources/student-management-system',
    tech_tags: ['JavaScript', 'Three.js', 'HTML5', 'CSS3', 'PHP', 'MySQL'],
    category: 'Web Application',
    featured: true,
    order: 3,
    created_at: '2025-05-01T00:00:00Z',
    updated_at: '2025-09-01T00:00:00Z'
  },
  {
    id: '4',
    title: 'TrackSpend Native',
    slug: 'trackspend-native',
    description:
      'A native Android finance tracker with automatic SMS-based expense detection, fingerprint authentication and a Supabase-backed sync engine.',
    long_description:
      'TrackSpend is a mobile finance companion for Android. A background SMS broadcast receiver intelligently detects banking messages and auto-categorises spend, while a clean Jetpack Compose UI makes budgeting effortless. Biometric fingerprint/PIN unlocks protect sensitive data, and the Supabase backend keeps expenses synchronised across devices with offline support.',
    image_url: '',
    demo_url: null,
    github_url: 'https://github.com/emmanuel-tech-resources/trackspend-native',
    tech_tags: ['Kotlin', 'Jetpack Compose', 'Supabase', 'Android', 'Gradle'],
    category: 'Mobile App',
    featured: false,
    order: 4,
    created_at: '2025-07-01T00:00:00Z',
    updated_at: '2025-09-10T00:00:00Z'
  },
  {
    id: '5',
    title: 'Elevate Media Productions',
    slug: 'elevate-media-productions',
    description:
      'This very website — a full brand platform built with SvelteKit, featuring an interactive forum, a blog and a rich portfolio of Elevate Media work.',
    long_description:
      'The Elevate Media Productions flagship brand platform. Beyond a business showcase, it is a developer community: threaded discussions, blog articles, a project gallery and a complete admin dashboard — all wrapped in a fast, accessible, animated experience built with SvelteKit and Tailwind CSS.',
    image_url: '',
    demo_url: null,
    github_url: 'https://github.com/emmanuel-tech-resources',
    tech_tags: ['SvelteKit', 'Svelte 5', 'TypeScript', 'Tailwind CSS', 'Supabase', 'Motion'],
    category: 'Brand Platform',
    featured: true,
    order: 0,
    created_at: '2025-09-11T00:00:00Z',
    updated_at: '2025-09-11T00:00:00Z'
  }
];

export function getProjectBySlug(slug: string): Project | undefined {
  return projects.find((p) => p.slug === slug);
}

export function getFeaturedProjects(): Project[] {
  return projects.filter((p) => p.featured);
}

export function getProjectCategories(): string[] {
  return [...new Set(projects.map((p) => p.category))];
}

export function getAllTechTags(): string[] {
  return [...new Set(projects.flatMap((p) => p.tech_tags))];
}