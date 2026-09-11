<script lang="ts">
  import { MessageCircle, Users, ArrowRight, Pin, Plus } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import Icon from '$lib/components/ui/Icon.svelte';
  import { forumCategories, getForumThreads } from '$lib/data/forum';
  import { timeAgo } from '$lib/utils';

  const threads = getForumThreads();
</script>

<svelte:head>
  <title>Community Forum — Elevate Media Productions</title>
  <meta name="description" content="Join the Elevate Media developer community — share work, ask questions and build together." />
</svelte:head>

<div class="pt-28 md:pt-36">
  <section class="section-padding relative overflow-hidden">
    <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
    <div class="container-x relative">
      <SectionHeading
        badge="Community"
        title="Forum"
        subtitle="Developers sharing work, asking hard questions and helping each other build better products."
      />

      <div class="grid gap-7 lg:grid-cols-[1fr_1.5fr]">
        <Reveal>
          <div class="glass rounded-2xl p-6">
            <div class="mb-5 flex items-center gap-3">
              <Users size={18} class="text-primary-500" />
              <h3 class="font-display text-lg font-bold text-ink dark:text-white">Categories</h3>
            </div>
            <ul class="space-y-1.5">
              {#each forumCategories as category (category.id)}
                <li>
                  <a
                    href="/forum/{category.slug}"
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-colors hover:bg-ink/5 dark:hover:bg-white/5"
                  >
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg text-white" style="background: {category.color}20; color: {category.color}">
                      <Icon name={category.icon} size={16} />
                    </span>
                    <div class="flex-1 min-w-0">
                      <span class="block text-sm font-semibold text-ink dark:text-white truncate">{category.name}</span>
                      <span class="block text-xs text-ink-light dark:text-slate-500">{category.thread_count} threads · {category.post_count} posts</span>
                    </div>
                    <ArrowRight size={14} class="opacity-0 group-hover:opacity-100 transition-all dark:text-slate-500" />
                  </a>
                </li>
              {/each}
            </ul>
          </div>
        </Reveal>

        <Reveal>
          <div class="glass rounded-2xl p-6">
            <div class="mb-5 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <MessageCircle size={18} class="text-secondary-500" />
                <h3 class="font-display text-lg font-bold text-ink dark:text-white">Recent discussions</h3>
              </div>
              <a href="/forum/new" class="btn-gradient !px-4 !py-2 !text-xs">
                <Plus size={14} /> New thread
              </a>
            </div>
            <ul class="space-y-2">
              {#each threads as thread (thread.id)}
                <li>
                  <a
                    href="/forum/thread/{thread.id}"
                    class="group flex items-start gap-3 rounded-xl px-4 py-3 transition-colors hover:bg-ink/5 dark:hover:bg-white/5"
                  >
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
                      <div class="mt-1 flex items-center gap-2 text-xs text-ink-light dark:text-slate-500 flex-wrap">
                        <span>{thread.author_name}</span>
                        <span>·</span>
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-medium" style="background: {forumCategories.find(c => c.id === thread.category_id)?.color ?? '#6366f1'}15; color: {forumCategories.find(c => c.id === thread.category_id)?.color ?? '#6366f1'}">
                          {thread.category_name}
                        </span>
                        <span>·</span>
                        <span>{timeAgo(thread.last_reply_at ?? thread.created_at)}</span>
                      </div>
                    </div>
                    <span class="flex-shrink-0 rounded-lg bg-ink/5 px-2.5 py-1 text-xs text-ink-light dark:bg-white/5 dark:text-slate-500">
                      {thread.reply_count} {thread.reply_count === 1 ? 'reply' : 'replies'}
                    </span>
                  </a>
                </li>
              {/each}
            </ul>
          </div>
        </Reveal>
      </div>
    </div>
  </section>
</div>