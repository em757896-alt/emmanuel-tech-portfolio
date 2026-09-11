<script lang="ts">
  import { LayoutDashboard, FolderOpen, FileText, MessageCircle, Users, TrendingUp, BarChart3, Activity } from 'lucide-svelte';
  import { projects } from '$lib/data/projects';
  import { blogPosts } from '$lib/data/blog';
  import { forumThreads } from '$lib/data/forum';

  const cards = [
    { label: 'Total Projects', value: projects.length, icon: FolderOpen, color: 'from-primary-500 to-purple-500' },
    { label: 'Published Posts', value: blogPosts.length, icon: FileText, color: 'from-secondary-500 to-pink-500' },
    { label: 'Forum Threads', value: forumThreads.length, icon: MessageCircle, color: 'from-accent-500 to-teal-500' },
    { label: 'Active Users', value: 12, icon: Users, color: 'from-amber-500 to-orange-500' }
  ];
</script>

<svelte:head>
  <title>Dashboard — Elevate Media</title>
</svelte:head>

<div class="space-y-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-ink dark:text-white">Dashboard</h1>
    <p class="mt-1 text-sm text-ink-light dark:text-slate-400">Overview of your Elevate Media platform.</p>
  </div>

  <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
    {#each cards as card (card.label)}
      <div class="glass group cursor-default rounded-2xl p-5 transition-all hover:-translate-y-1 hover:shadow-lg hover:shadow-primary-500/10">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-ink-light dark:text-slate-500">{card.label}</p>
            <p class="mt-1 font-display text-3xl font-bold text-ink dark:text-white">{card.value}</p>
          </div>
          <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br text-white {card.color}">
            <card.icon size={20} />
          </div>
        </div>
      </div>
    {/each}
  </div>

  <div class="grid gap-6 lg:grid-cols-2">
    <div class="glass rounded-2xl p-6">
      <div class="mb-5 flex items-center gap-3">
        <BarChart3 size={18} class="text-primary-500" />
        <h3 class="font-display text-lg font-bold text-ink dark:text-white">Recent projects</h3>
      </div>
      <ul class="space-y-3">
        {#each projects.slice(0, 5) as project}
          <li class="flex items-center justify-between rounded-xl px-4 py-3 transition-colors hover:bg-ink/5 dark:hover:bg-white/5">
            <div class="flex items-center gap-3 min-w-0">
              <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-primary-500/10 text-xs font-bold text-primary-500">
                {project.title.slice(0, 2)}
              </div>
              <div class="min-w-0">
                <p class="text-sm font-medium text-ink dark:text-white truncate">{project.title}</p>
                <p class="text-xs text-ink-light dark:text-slate-500">{project.category}</p>
              </div>
            </div>
            <a href="/portfolio/{project.slug}" class="flex-shrink-0 text-xs font-medium text-primary-500 hover:underline">View</a>
          </li>
        {/each}
      </ul>
    </div>

    <div class="glass rounded-2xl p-6">
      <div class="mb-5 flex items-center gap-3">
        <Activity size={18} class="text-secondary-500" />
        <h3 class="font-display text-lg font-bold text-ink dark:text-white">Recent threads</h3>
      </div>
      <ul class="space-y-3">
        {#each forumThreads.slice(0, 5) as thread}
          <li class="flex items-center justify-between rounded-xl px-4 py-3 transition-colors hover:bg-ink/5 dark:hover:bg-white/5">
            <div class="min-w-0">
              <p class="text-sm font-medium text-ink dark:text-white truncate">{thread.title}</p>
              <p class="text-xs text-ink-light dark:text-slate-500">{thread.author_name} · {thread.reply_count} replies</p>
            </div>
            <a href="/forum/thread/{thread.id}" class="flex-shrink-0 text-xs font-medium text-secondary-500 hover:underline">View</a>
          </li>
        {/each}
      </ul>
    </div>
  </div>
</div>