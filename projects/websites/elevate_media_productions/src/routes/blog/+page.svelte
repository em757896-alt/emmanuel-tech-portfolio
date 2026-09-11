<script lang="ts">
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import { getBlogPosts } from '$lib/data/blog';
  import { formatDate } from '$lib/utils';
  import { Search } from 'lucide-svelte';

  const posts = getBlogPosts();
  let search = $state('');

  let filtered = $derived(
    search.trim()
      ? posts.filter(
          (p) =>
            p.title.toLowerCase().includes(search.toLowerCase()) ||
            p.category.toLowerCase().includes(search.toLowerCase()) ||
            p.tags.some((t) => t.toLowerCase().includes(search.toLowerCase()))
        )
      : posts
  );
</script>

<svelte:head>
  <title>Blog — Elevate Media Productions</title>
  <meta name="description" content="Engineering lessons, product decisions and behind-the-scenes builds from the Elevate Media studio." />
</svelte:head>

<div class="pt-28 md:pt-36">
  <section class="section-padding relative overflow-hidden">
    <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
    <div class="container-x relative">
      <SectionHeading
        badge="Blog"
        title="Latest writing"
        subtitle="Engineering lessons, product decisions and behind-the-scenes builds."
      />

      <Reveal>
        <div class="mb-12 mx-auto max-w-xl">
          <div class="relative">
            <Search size={18} class="absolute left-4 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500" />
            <input
              type="text"
              bind:value={search}
              placeholder="Search by title, category or tag..."
              class="w-full rounded-xl border border-slate-200/80 bg-fog-light py-3 pl-12 pr-4 text-sm text-ink outline-none transition-colors focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white"
            />
          </div>
        </div>
      </Reveal>

      <div class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
        {#each filtered as post (post.id)}
          <Reveal>
            <a href="/blog/{post.slug}" class="group glass card-hover flex flex-col overflow-hidden rounded-2xl">
              <div class="relative h-44 w-full bg-gradient-to-br from-primary-500/20 via-purple-500/15 to-secondary-500/20">
                {#if post.cover_image}
                  <img src={post.cover_image} alt={post.title} class="h-full w-full object-cover" />
                {:else}
                  <div class="flex h-full items-center justify-center text-primary-500/20">
                    <svg class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                  </div>
                {/if}
                <Badge className="absolute top-4 left-4">{post.category}</Badge>
              </div>
              <div class="flex flex-1 flex-col p-6">
                <div class="flex items-center gap-3 text-xs text-ink-light dark:text-slate-500">
                  <time>{formatDate(post.published_at)}</time>
                  <span>·</span>
                  <span>{post.reading_time} min read</span>
                </div>
                <h3 class="mt-3 font-display text-lg font-bold text-ink transition-colors group-hover:text-primary-500 dark:text-white">
                  {post.title}
                </h3>
                <p class="mt-2 line-clamp-2 text-sm text-ink-light dark:text-slate-400">{post.excerpt}</p>
                <div class="mt-auto flex flex-wrap gap-2 pt-4">
                  {#each post.tags.slice(0, 3) as tag}
                    <span class="rounded-md bg-ink/5 px-2 py-1 text-xs text-ink-light dark:bg-white/5 dark:text-slate-500">{tag}</span>
                  {/each}
                </div>
              </div>
            </a>
          </Reveal>
        {/each}
      </div>

      {#if filtered.length === 0}
        <p class="mt-10 text-center text-ink-light dark:text-slate-500">No posts match your search.</p>
      {/if}
    </div>
  </section>
</div>