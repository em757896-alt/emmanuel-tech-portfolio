<script lang="ts">
  import { page } from '$app/state';
  import { ArrowLeft, Plus, MessageCircle } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import Icon from '$lib/components/ui/Icon.svelte';
  import { forumCategories, getForumThreads } from '$lib/data/forum';
  import { timeAgo } from '$lib/utils';

  const slug = $derived(page.params.category ?? '');
  const category = $derived(forumCategories.find((c) => c.slug === slug));
  const threads = $derived(getForumThreads().filter((t) => t.category_name === category?.name));
</script>

<svelte:head>
  <title>{category?.name ?? 'Category'} — Elevate Media Forum</title>
</svelte:head>

{#if category}
  <div class="pt-28 md:pt-36">
    <section class="section-padding relative overflow-hidden">
      <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
      <div class="container-x relative mx-auto max-w-4xl">
        <Reveal>
          <a href="/forum" class="group mb-10 inline-flex items-center gap-2 text-sm text-ink-light transition-colors hover:text-ink dark:text-slate-400 dark:hover:text-white">
            <ArrowLeft size={16} class="transition-transform group-hover:-translate-x-1" />
            Back to forum
          </a>
        </Reveal>

        <Reveal>
          <div class="mb-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
              <span class="flex h-12 w-12 items-center justify-center rounded-xl text-white" style="background: {category.color}20; color: {category.color}">
                <Icon name={category.icon} size={22} />
              </span>
              <div>
                <h1 class="font-display text-2xl font-bold text-ink dark:text-white sm:text-3xl">{category.name}</h1>
                <p class="mt-0.5 text-sm text-ink-light dark:text-slate-400">{category.description}</p>
              </div>
            </div>
            <a href="/forum/new" class="btn-gradient !px-5 !py-2.5 !text-sm">
              <Plus size={16} /> New thread
            </a>
          </div>
        </Reveal>

        <div class="glass rounded-2xl p-6">
          <ul class="space-y-2">
            {#each threads as thread (thread.id)}
              <li>
                <a href="/forum/thread/{thread.id}" class="group flex items-start gap-3 rounded-xl px-4 py-3 transition-colors hover:bg-ink/5 dark:hover:bg-white/5">
                  <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-xs font-bold text-white">
                    {thread.author_name.split(' ').map((n) => n[0]).join('')}
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                      {#if thread.pinned}
                        <span class="rounded bg-secondary-500/10 px-1.5 py-0.5 text-[10px] font-bold uppercase text-secondary-500">pinned</span>
                      {/if}
                      <span class="text-sm font-semibold text-ink dark:text-white truncate">{thread.title}</span>
                    </div>
                    <div class="mt-1 flex items-center gap-2 text-xs text-ink-light dark:text-slate-500">
                      <span>{thread.author_name}</span>
                      <span>·</span>
                      <span>{timeAgo(thread.last_reply_at ?? thread.created_at)}</span>
                    </div>
                  </div>
                  <span class="flex-shrink-0 rounded-lg bg-ink/5 px-2.5 py-1 text-xs text-ink-light dark:bg-white/5 dark:text-slate-500">
                    {thread.reply_count} replies
                  </span>
                </a>
              </li>
            {/each}
            {#if threads.length === 0}
              <li class="py-10 text-center text-ink-light dark:text-slate-500">
                <MessageCircle size={32} class="mx-auto mb-3 opacity-30" />
                <p>No threads yet. Be the first to start a discussion!</p>
              </li>
            {/if}
          </ul>
        </div>
      </div>
    </section>
  </div>
{:else}
  <div class="flex min-h-screen items-center justify-center">
    <p class="text-ink-light dark:text-slate-400">Category not found.</p>
  </div>
{/if}