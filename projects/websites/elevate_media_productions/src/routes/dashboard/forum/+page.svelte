<script lang="ts">
  import { MessageCircle, Pin, Lock, Trash2, Eye, AlertTriangle } from 'lucide-svelte';
  import { forumThreads, forumCategories } from '$lib/data/forum';
  import { timeAgo } from '$lib/utils';

  let search = $state('');
  let filtered = $derived(
    search.trim()
      ? forumThreads.filter((t) => t.title.toLowerCase().includes(search.toLowerCase()))
      : forumThreads
  );
</script>

<svelte:head>
  <title>Forum Moderation — Dashboard</title>
</svelte:head>

<div class="space-y-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-ink dark:text-white">Forum Moderation</h1>
    <p class="mt-1 text-sm text-ink-light dark:text-slate-400">Review and moderate forum threads and discussions.</p>
  </div>

  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    {#each forumCategories.slice(0, 4) as cat (cat.id)}
      <div class="glass rounded-xl p-4">
        <div class="flex items-center gap-2 mb-2">
          <span class="h-2 w-2 rounded-full" style="background: {cat.color}"></span>
          <span class="text-xs font-medium text-ink-light dark:text-slate-500">{cat.name}</span>
        </div>
        <p class="font-display text-xl font-bold text-ink dark:text-white">{cat.thread_count}</p>
        <p class="text-xs text-ink-light dark:text-slate-500">threads</p>
      </div>
    {/each}
  </div>

  <div class="glass rounded-2xl p-5">
    <input type="text" bind:value={search} placeholder="Search threads..." class="w-full rounded-xl border border-slate-200/80 bg-fog-light px-4 py-3 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white mb-5" />

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-200/60 dark:border-white/10">
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Thread</th>
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Author</th>
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Category</th>
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Status</th>
            <th class="pb-3 font-medium text-ink-light dark:text-slate-500">Actions</th>
          </tr>
        </thead>
        <tbody>
          {#each filtered as thread (thread.id)}
            <tr class="border-b border-slate-200/30 dark:border-white/5">
              <td class="py-3.5 pr-4">
                <a href="/forum/thread/{thread.id}" class="font-medium text-ink dark:text-white hover:text-primary-500">{thread.title}</a>
                <p class="text-xs text-ink-light dark:text-slate-500">{thread.reply_count} replies · {timeAgo(thread.last_reply_at ?? thread.created_at)}</p>
              </td>
              <td class="py-3.5 pr-4 text-xs text-ink-light dark:text-slate-500">{thread.author_name}</td>
              <td class="py-3.5 pr-4">
                <span class="rounded-lg px-2 py-0.5 text-xs font-medium" style="background: {forumCategories.find(c => c.id === thread.category_id)?.color ?? '#6366f1'}15; color: {forumCategories.find(c => c.id === thread.category_id)?.color ?? '#6366f1'}">
                  {thread.category_name}
                </span>
              </td>
              <td class="py-3.5 pr-4">
                <div class="flex gap-1.5">
                  {#if thread.pinned}
                    <span class="rounded bg-secondary-500/10 px-1.5 py-0.5 text-[10px] font-bold text-secondary-500">PINNED</span>
                  {/if}
                  {#if thread.locked}
                    <span class="rounded bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-bold text-amber-500">LOCKED</span>
                  {/if}
                </div>
              </td>
              <td class="py-3.5">
                <div class="flex gap-1.5">
                  <button title="Pin" class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-light transition-colors hover:bg-secondary-500/10 hover:text-secondary-500 dark:text-slate-400">
                    <Pin size={15} />
                  </button>
                  <button title="Lock" class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-light transition-colors hover:bg-amber-500/10 hover:text-amber-500 dark:text-slate-400">
                    <Lock size={15} />
                  </button>
                  <button title="Delete" class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-light transition-colors hover:bg-secondary-500/10 hover:text-secondary-500 dark:text-slate-400">
                    <Trash2 size={15} />
                  </button>
                </div>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  </div>
</div>