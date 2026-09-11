<script lang="ts">
  import { page } from '$app/state';
  import { ArrowLeft, Clock, Tag } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import { getPostBySlug } from '$lib/data/blog';
  import { formatDate } from '$lib/utils';

  const slug = $derived(page.params.slug ?? '');
  const post = $derived(getPostBySlug(slug));
</script>

<svelte:head>
  <title>{post?.title ?? 'Article'} — Elevate Media Blog</title>
  <meta name="description" content={post?.excerpt} />
</svelte:head>

{#if post}
  <div class="pt-28 md:pt-36">
    <section class="section-padding relative overflow-hidden">
      <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
      <div class="container-x relative mx-auto max-w-3xl">
        <Reveal>
          <a href="/blog" class="group mb-10 inline-flex items-center gap-2 text-sm text-ink-light transition-colors hover:text-ink dark:text-slate-400 dark:hover:text-white">
            <ArrowLeft size={16} class="transition-transform group-hover:-translate-x-1" />
            Back to blog
          </a>
        </Reveal>

        <Reveal>
          <div class="mb-8 flex flex-wrap items-center gap-3">
            <Badge>{post.category}</Badge>
            <span class="flex items-center gap-1.5 text-xs text-ink-light dark:text-slate-500">
              <Clock size={13} /> {post.reading_time} min read
            </span>
            <span class="text-xs text-ink-light dark:text-slate-500">·</span>
            <time class="text-xs text-ink-light dark:text-slate-500">{formatDate(post.published_at)}</time>
          </div>
        </Reveal>

        <Reveal>
          <h1 class="font-display text-3xl font-bold leading-tight text-ink dark:text-white sm:text-4xl md:text-5xl">
            {post.title}
          </h1>
          <p class="mt-4 text-lg text-ink-light dark:text-slate-400">{post.excerpt}</p>
        </Reveal>

        <Reveal>
          <article class="glass mt-10 rounded-2xl p-8 md:p-10 prose-custom">
            {#each post.content.split('\n') as paragraph}
              {#if paragraph.startsWith('# ')}
                <h1 class="font-display text-2xl font-bold text-ink dark:text-white">{paragraph.replace('# ', '')}</h1>
              {:else if paragraph.startsWith('## ')}
                <h2 class="mt-8 mb-3 font-display text-xl font-bold text-ink dark:text-white">{paragraph.replace('## ', '')}</h2>
              {:else if paragraph.startsWith('### ')}
                <h3 class="mt-6 mb-2 font-display text-lg font-bold text-ink dark:text-white">{paragraph.replace('### ', '')}</h3>
              {:else if paragraph.startsWith('- ')}
                <li class="ml-4 text-ink-light dark:text-slate-300">{paragraph.replace('- ', '')}</li>
              {:else if paragraph.startsWith('```')}
                <pre class="overflow-x-auto rounded-xl bg-slate-900 p-4 text-sm text-slate-100"><code>{paragraph.replace(/```\w*/g, '')}</code></pre>
              {:else if paragraph.trim() !== ''}
                <p class="my-3 text-ink-light dark:text-slate-300 leading-relaxed">{paragraph}</p>
              {/if}
            {/each}
          </article>
        </Reveal>

        <Reveal>
          <div class="mt-8 flex flex-wrap gap-2">
            {#each post.tags as tag}
              <span class="inline-flex items-center gap-1.5 rounded-lg bg-ink/5 px-3 py-1.5 text-sm text-ink-light dark:bg-white/5 dark:text-slate-400">
                <Tag size={12} /> {tag}
              </span>
            {/each}
          </div>
        </Reveal>
      </div>
    </section>
  </div>
{:else}
  <div class="flex min-h-screen items-center justify-center">
    <p class="text-ink-light dark:text-slate-400">Post not found.</p>
  </div>
{/if}