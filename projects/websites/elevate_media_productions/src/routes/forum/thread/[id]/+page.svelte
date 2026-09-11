<script lang="ts">
  import { page } from '$app/state';
  import { ArrowLeft, ThumbsUp, MessageCircle, Clock, Lock, Pin } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import { getThreadById, forumCategories, forumReplies } from '$lib/data/forum';
  import { timeAgo, avatarColor } from '$lib/utils';

  const threadId = $derived(page.params.id ?? '');
  const thread = $derived(getThreadById(threadId));
  const category = $derived(
    thread ? forumCategories.find((c) => c.id === thread.category_id) : null
  );
  const replies = $derived(forumReplies.filter((r) => r.thread_id === threadId));
</script>

<svelte:head>
  <title>{thread?.title ?? 'Thread'} — Elevate Media Forum</title>
</svelte:head>

{#if thread}
  <div class="pt-28 md:pt-36">
    <section class="section-padding relative overflow-hidden">
      <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
      <div class="container-x relative mx-auto max-w-3xl">
        <Reveal>
          <a
            href="/forum/{category?.slug ?? ''}"
            class="group mb-10 inline-flex items-center gap-2 text-sm text-ink-light transition-colors hover:text-ink dark:text-slate-400 dark:hover:text-white"
          >
            <ArrowLeft size={16} class="transition-transform group-hover:-translate-x-1" />
            Back to {category?.name ?? 'forum'}
          </a>
        </Reveal>

        <Reveal>
          <div class="flex flex-wrap items-center gap-3 mb-4">
            {#if category}
              <a
                href="/forum/{category.slug}"
                class="rounded-lg px-2.5 py-1 text-xs font-medium text-white"
                style="background: {category.color}"
              >
                {category.name}
              </a>
            {/if}
            {#if thread.pinned}
              <Badge variant="secondary">
                <Pin size={11} /> Pinned
              </Badge>
            {/if}
            {#if thread.locked}
              <Badge variant="ghost">
                <Lock size={11} /> Locked
              </Badge>
            {/if}
          </div>
        </Reveal>

        <Reveal>
          <h1 class="font-display text-2xl font-bold text-ink dark:text-white sm:text-3xl">{thread.title}</h1>
          <div class="mt-3 flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold text-white" style="background: {avatarColor(thread.author_name)}">
              {thread.author_name.split(' ').map((n) => n[0]).join('')}
            </div>
            <div class="text-sm">
              <span class="font-medium text-ink dark:text-white">{thread.author_name}</span>
              <span class="mx-2 text-ink-light dark:text-slate-500">·</span>
              <span class="flex items-center gap-1 text-ink-light dark:text-slate-500">
                <Clock size={12} /> {timeAgo(thread.created_at)}
              </span>
            </div>
          </div>
        </Reveal>

        <Reveal>
          <div class="glass mt-8 rounded-2xl p-8 md:p-10">
            <div class="prose-custom">
              {#each thread.content.split('\n\n') as paragraph}
                <p class="text-ink-light dark:text-slate-300 leading-relaxed">{paragraph}</p>
              {/each}
            </div>

            <div class="mt-8 flex items-center gap-6 border-t border-slate-200/50 pt-6 dark:border-white/5">
              <button class="inline-flex items-center gap-2 rounded-lg bg-ink/5 px-3 py-1.5 text-sm text-ink-light transition-colors hover:bg-ink/10 dark:bg-white/5 dark:text-slate-400 dark:hover:bg-white/10">
                <ThumbsUp size={15} /> {thread.upvotes}
              </button>
              <span class="flex items-center gap-2 text-sm text-ink-light dark:text-slate-500">
                <MessageCircle size={15} /> {thread.reply_count} {thread.reply_count === 1 ? 'reply' : 'replies'}
              </span>
            </div>
          </div>
        </Reveal>

        {#if replies.length > 0}
          <Reveal>
            <div class="mt-8 glass rounded-2xl p-6">
              <h3 class="mb-5 font-display text-lg font-bold text-ink dark:text-white">Replies</h3>
              <ul class="space-y-4">
                {#each replies as reply (reply.id)}
                  <li class="flex gap-3 rounded-xl p-4 transition-colors hover:bg-ink/5 dark:hover:bg-white/5">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white" style="background: {avatarColor(reply.author_name)}">
                      {reply.author_name.split(' ').map((n) => n[0]).join('')}
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2 text-sm">
                        <span class="font-semibold text-ink dark:text-white">{reply.author_name}</span>
                        <span class="text-xs text-ink-light dark:text-slate-500">{timeAgo(reply.created_at)}</span>
                      </div>
                      <p class="mt-1 text-sm text-ink-light dark:text-slate-300">{reply.content}</p>
                    </div>
                  </li>
                {/each}
              </ul>
            </div>
          </Reveal>
        {/if}
      </div>
    </section>
  </div>
{:else}
  <div class="flex min-h-screen items-center justify-center">
    <p class="text-ink-light dark:text-slate-400">Thread not found.</p>
  </div>
{/if}