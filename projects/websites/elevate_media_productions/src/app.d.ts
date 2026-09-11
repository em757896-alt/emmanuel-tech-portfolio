// See https://svelte.dev/docs/kit/types#app.d.ts
// for information about these interfaces
declare global {
  namespace App {
    // interface Error {}
    // interface Locals {}
    // interface PageData {}
    // interface PageState {}
    // interface Platform {}
  }

  type Profile = {
    id: string;
    username: string | null;
    full_name: string | null;
    avatar_url: string | null;
    bio: string | null;
    role: 'admin' | 'member' | 'moderator';
    created_at: string;
  };

  type Project = {
    id: string;
    title: string;
    slug: string;
    description: string;
    long_description: string | null;
    image_url: string | null;
    demo_url: string | null;
    github_url: string | null;
    tech_tags: string[];
    category: string;
    featured: boolean;
    order: number;
    created_at: string;
    updated_at: string;
  };

  type Post = {
    id: string;
    title: string;
    slug: string;
    content: string;
    excerpt: string | null;
    cover_image: string | null;
    author_id: string;
    category: string;
    tags: string[];
    reading_time: number;
    published: boolean;
    published_at: string | null;
    created_at: string;
    updated_at: string;
  };

  type ForumCategory = {
    id: string;
    name: string;
    slug: string;
    description: string;
    icon: string;
    color: string;
    thread_count: number;
    post_count: number;
    order: number;
  };

  type ForumThread = {
    id: string;
    title: string;
    slug: string;
    content: string;
    author_id: string;
    category_id: string;
    pinned: boolean;
    locked: boolean;
    upvotes: number;
    reply_count: number;
    last_reply_at: string | null;
    created_at: string;
    updated_at: string;
  };

  type ForumReply = {
    id: string;
    content: string;
    thread_id: string;
    author_id: string;
    parent_id: string | null;
    upvotes: number;
    created_at: string;
    updated_at: string;
  };

  type Testimonial = {
    id: string;
    name: string;
    role: string;
    company: string;
    content: string;
    avatar_url: string | null;
    rating: number;
    featured: boolean;
    created_at: string;
  };
}

export {};