<script lang="ts">
  import { ArrowRight, MessageCircle, Users, Pin } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import Icon from '$lib/components/ui/Icon.svelte';
  import { forumCategories, getForumThreads } from '$lib/data/forum';
  import { timeAgo } from '$lib/utils';

  const threads = getForumThreads().slice(0, 4);
  const totalThreads = forumCategories.reduce((acc, c) => acc + c.thread_count, 0);
  const totalPosts = forumCategories.reduce((acc, c) => acc + c.post_count, 0);
</script>

<section class="section-padding relative overflow-hidden">
  <div class="absolute -right-40 top-1/2 h-[400px] w-[400px] -translate-y-1/2 rounded-full bg-accent-500/10 blur-[130px] pointer-events-none"></div>
  <div class="container-x relative">
    <SectionHeading
      badge="Community"
      title="Join the conversation"
      subtitle="Developers sharing work, asking hard questions and helping each other build better products."
    />

    <div class="grid gap-8 lg:grid-cols-[1fr_1.3fr]">
      <Reveal>
        <div class="glass rounded-2xl p-7">
          <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <Users size={18} class="text-primary-500" />
              <h3 class="font-display text-lg font-bold text-ink dark:text-white">Categories</h3>
            </div>
            <div class="flex gap-4 text-xs text-ink-light dark:text-slate-500">
              <span class="flex items-center gap-1">
                <MessageCircle size={12} /> {totalThreads} threads
              </span>
              <span>{totalPosts} posts</span>
            </div>
          </div>
          <ul class="space-y-2">
            {#each forumCategories as category (category.id)}
              <li>
                <a
                  href="/forum/{category.slug}"
                  class="group flex items-center gap-4 rounded-xl px-4 py-3 transition-colors hover:bg-ink/5 dark:hover:bg-white/5"
                >
                  <span
                    class="flex h-10 w-10 items-center justify-center rounded-lg text-white"
                    style="background: {category.color}20; color: {category.color}"
                  >
                    <Icon name={category.icon} size={18} />
                  </span>
                  <div class="flex-1 min-w-0">
                    <span class="block text-sm font-semibold text-ink dark:text-white truncate">
                      {category.name}
                    </span>
                    <span class="block text-xs text-ink-light dark:text-slate-500 truncate">
                      {category.description}
                    </span>
                  </div>
                  <ArrowRight
                    size={14}
                    class="text-ink-light opacity-0 transition-all group-hover:translate-x-0.5 group-hover:opacity-100 dark:text-slate-500"
                  />
                </a>
              </li>
            {/each}
          </ul>
        </div>
      </Reveal>

      <Reveal>
        <div class="glass rounded-2xl p-7">
          <div class="mb-6 flex items-center gap-3">
            <Pin size={18} class="text-secondary-500" />
            <h3 class="font-display text-lg font-bold text-ink dark:text-white">Recent threads</h3>
          </div>
          <ul class="space-y-3">
            {#each threads as thread (thread.id)}
              <li>
                <a
                  href="/forum/thread/{thread.id}"
                  class="group flex items-start gap-4 rounded-xl px-4 py-3 transition-colors hover:bg-ink/5 dark:hover:bg-white/5"
                >
                  <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-xs font-bold text-white">
                    {thread.author_name.split(' ').map((n) => n[0]).join('')}
                  </div>
                  <div class="flex-1 min-w-0">
                    <span class="flex items-center gap-2">
                      {#if thread.pinned}
                        <span class="rounded bg-secondary-500/10 px-1.5 py-0.5 text-[10px] font-bold uppercase text-secondary-500">pinned</span>
                      {/if}
                      <span class="block truncate text-sm font-semibold text-ink dark:text-white">
                        {thread.title}
                      </span>
                    </span>
                    <div class="mt-1 flex items-center gap-3 text-xs text-ink-light dark:text-slate-500">
                      <span>{thread.author_name}</span>
                      <span>·</span>
                      <span>{thread.category_name}</span>
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
          </ul>
          <a
            href="/forum"
            class="mt-6 flex items-center justify-center gap-2 rounded-xl bg-ink/5 py-3 text-sm font-medium text-ink-light transition-colors hover:bg-ink/10 dark:bg-white/5 dark:text-slate-400 dark:hover:bg-white/10"
          >
            View all discussions
            <ArrowRight size={14} />
          </a>
        </div>
      </Reveal>
    </div>
  </div>
</section>