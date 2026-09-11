<script lang="ts">
  import { FileText, Plus, Pencil, Trash2, Eye, EyeOff } from 'lucide-svelte';
  import { blogPosts } from '$lib/data/blog';
  import { formatDate } from '$lib/utils';

  let search = $state('');
  let filtered = $derived(
    search.trim()
      ? blogPosts.filter((p) => p.title.toLowerCase().includes(search.toLowerCase()))
      : blogPosts
  );
</script>

<svelte:head>
  <title>Manage Blog — Dashboard</title>
</svelte:head>

<div class="space-y-6">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="font-display text-2xl font-bold text-ink dark:text-white">Blog Posts</h1>
      <p class="mt-1 text-sm text-ink-light dark:text-slate-400">Create and manage your blog content.</p>
    </div>
    <button class="btn-gradient !px-5 !py-2.5 !text-sm">
      <Plus size={16} /> New post
    </button>
  </div>

  <div class="glass rounded-2xl p-5">
    <input type="text" bind:value={search} placeholder="Search posts..." class="w-full rounded-xl border border-slate-200/80 bg-fog-light px-4 py-3 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white mb-5" />

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-200/60 dark:border-white/10">
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Title</th>
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Category</th>
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Published</th>
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Date</th>
            <th class="pb-3 font-medium text-ink-light dark:text-slate-500">Actions</th>
          </tr>
        </thead>
        <tbody>
          {#each filtered as post (post.id)}
            <tr class="border-b border-slate-200/30 dark:border-white/5">
              <td class="py-3.5 pr-4">
                <p class="font-medium text-ink dark:text-white">{post.title}</p>
                <p class="text-xs text-ink-light dark:text-slate-500">{post.reading_time} min read</p>
              </td>
              <td class="py-3.5 pr-4">
                <span class="rounded-lg bg-secondary-500/10 px-2.5 py-1 text-xs font-medium text-secondary-600 dark:text-secondary-400">{post.category}</span>
              </td>
              <td class="py-3.5 pr-4">
                {#if post.published}
                  <span class="inline-flex items-center gap-1 text-xs text-accent-500"><Eye size={13} /> Live</span>
                {:else}
                  <span class="inline-flex items-center gap-1 text-xs text-ink-light dark:text-slate-500"><EyeOff size={13} /> Draft</span>
                {/if}
              </td>
              <td class="py-3.5 pr-4">
                <span class="text-xs text-ink-light dark:text-slate-500">{formatDate(post.published_at)}</span>
              </td>
              <td class="py-3.5">
                <div class="flex gap-2">
                  <button class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-light transition-colors hover:bg-ink/5 hover:text-primary-500 dark:text-slate-400 dark:hover:bg-white/5">
                    <Pencil size={15} />
                  </button>
                  <button class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-light transition-colors hover:bg-secondary-500/10 hover:text-secondary-500 dark:text-slate-400 dark:hover:bg-white/5">
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