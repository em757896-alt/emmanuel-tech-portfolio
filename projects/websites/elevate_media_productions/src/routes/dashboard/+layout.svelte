<script lang="ts">
  import { page } from '$app/state';
  import { LayoutDashboard, FolderOpen, FileText, MessageCircle, Users, Settings, LogOut, ChevronLeft, Rocket } from 'lucide-svelte';
  import { goto } from '$app/navigation';
  import { session } from '$lib/stores/session';
  import { signOut, supabaseConfigured } from '$lib/supabase';

  let { children } = $props();

  let sidebarOpen = $state(true);

  const links = [
    { href: '/dashboard', icon: LayoutDashboard, label: 'Overview', exact: true },
    { href: '/dashboard/projects', icon: FolderOpen, label: 'Projects' },
    { href: '/dashboard/blog', icon: FileText, label: 'Blog' },
    { href: '/dashboard/forum', icon: MessageCircle, label: 'Forum' },
    { href: '/dashboard/users', icon: Users, label: 'Users' }
  ];

  function isActive(href: string, exact = false) {
    return exact ? page.url.pathname === href : page.url.pathname.startsWith(href);
  }

  $effect(() => {
    if (typeof window === 'undefined') return;
    if (!supabaseConfigured) {
      goto('/auth/login');
      return;
    }
    if ($session.loading) return;
    if (!$session.user) {
      goto('/auth/login');
    }
  });

  async function onSignOut() {
    await signOut();
    goto('/');
  }
</script>

<div class="flex min-h-screen">
  <aside
    class="fixed inset-y-0 left-0 z-40 flex flex-col border-r border-slate-200/60 bg-fog-light transition-all duration-300 dark:border-white/10 dark:bg-night-lighter"
    class:w-64={sidebarOpen}
    class:w-[68px]={!sidebarOpen}
  >
    <div class="flex h-16 items-center gap-3 border-b border-slate-200/60 px-4 dark:border-white/10">
      {#if sidebarOpen}
        <a href="/dashboard" class="flex items-center gap-2.5">
          <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white">
            <Rocket size={17} />
          </span>
          <span class="font-display text-base font-bold text-ink dark:text-white truncate">Dashboard</span>
        </a>
      {/if}
      <button class="ml-auto flex h-8 w-8 items-center justify-center rounded-lg text-ink-light transition-colors hover:bg-ink/5 dark:text-slate-400 dark:hover:bg-white/5"
        onclick={() => sidebarOpen = !sidebarOpen}>
        <ChevronLeft
          size={16}
          class="transition-transform {!sidebarOpen ? 'rotate-180' : ''}"
        />
      </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
      <ul class="space-y-1">
        {#each links as link (link.href)}
          <li>
            <a
              href={link.href}
              class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                {isActive(link.href, link.exact)
                  ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400'
                  : 'text-ink-light hover:text-ink dark:text-slate-400 dark:hover:text-white hover:bg-ink/5 dark:hover:bg-white/5'}"
            >
              <link.icon size={18} />
              {#if sidebarOpen}
                <span>{link.label}</span>
              {/if}
            </a>
          </li>
        {/each}
      </ul>
    </nav>

    <div class="border-t border-slate-200/60 px-3 py-3 dark:border-white/10">
      {#if $session.user}
        <div class="mb-2 flex items-center gap-3 rounded-xl px-3 py-2">
          <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-secondary-500 text-xs font-bold text-white">
            {($session.user.full_name ?? 'U').slice(0, 1)}
          </span>
          {#if sidebarOpen}
            <span class="min-w-0 truncate text-sm font-medium text-ink dark:text-white">
              {$session.user.full_name ?? 'Member'}
            </span>
          {/if}
        </div>
      {/if}
      <a href="/" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-light transition-colors hover:text-ink dark:text-slate-400 dark:hover:text-white hover:bg-ink/5 dark:hover:bg-white/5">
        <LogOut size={18} />
        {#if sidebarOpen}
          <span>Back to site</span>
        {/if}
      </a>
      <button onclick={onSignOut} class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-ink-light transition-colors hover:text-secondary-500 dark:text-slate-400 dark:hover:text-secondary-400 hover:bg-secondary-500/10">
        <Settings size={18} />
        {#if sidebarOpen}
          <span>Sign out</span>
        {/if}
      </button>
    </div>
  </aside>

  <main class="flex-1 transition-all duration-300" class:ml-64={sidebarOpen} class:ml-[68px]={!sidebarOpen}>
    <div class="p-6 lg:p-8">
      {@render children()}
    </div>
  </main>
</div>