<script lang="ts">
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import { blogPosts, getFeaturedPosts } from '$lib/data/blog';
  import { formatDate } from '$lib/utils';

  const posts = getFeaturedPosts();
</script>

<section class="section-padding relative">
  <div class="container-x">
    <SectionHeading
      badge="Blog"
      title="Latest writing"
      subtitle="Engineering lessons, product decisions and behind-the-scenes builds from the Elevate Media studio."
    />

    <div class="grid gap-7 sm:grid-cols-2">
      {#each posts as post (post.id)}
        <Reveal>
          <a href="/blog/{post.slug}" class="group glass card-hover flex flex-col overflow-hidden rounded-2xl">
            <div class="relative h-48 w-full bg-gradient-to-br from-primary-500/20 via-purple-500/15 to-secondary-500/20">
              {#if post.cover_image}
                <img src={post.cover_image} alt={post.title} class="h-full w-full object-cover" />
              {:else}
                <div class="flex h-full items-center justify-center text-primary-500/20">
                  <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
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
              <p class="mt-2 line-clamp-2 text-sm text-ink-light dark:text-slate-400">
                {post.excerpt}
              </p>
              <div class="mt-auto flex flex-wrap gap-2 pt-4">
                {#each post.tags.slice(0, 3) as tag}
                  <span class="rounded-md bg-ink/5 px-2 py-1 text-xs text-ink-light dark:bg-white/5 dark:text-slate-500">
                    {tag}
                  </span>
                {/each}
              </div>
            </div>
          </a>
        </Reveal>
      {/each}
    </div>

    <Reveal>
      <div class="mt-14 text-center">
        <a href="/blog" class="group inline-flex items-center gap-2 text-sm font-semibold text-primary-500">
          Read all posts
          <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
        </a>
      </div>
    </Reveal>
  </div>
</section>