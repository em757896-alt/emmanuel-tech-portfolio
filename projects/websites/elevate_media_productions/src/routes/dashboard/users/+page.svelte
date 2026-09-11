<script lang="ts">
  import { Users, Shield, UserCog, Mail, Calendar } from 'lucide-svelte';
  import { avatarColor, formatDate } from '$lib/utils';

  const users = [
    { id: 'admin-1', name: 'Emmanuel K.', email: 'emmanuel@elevatemedia.io', role: 'admin', joined: '2025-01-15' },
    { id: '2', name: 'Nyambura W.', email: 'nyambura@example.com', role: 'member', joined: '2025-03-22' },
    { id: '3', name: 'Carlos M.', email: 'carlos@example.com', role: 'member', joined: '2025-04-10' },
    { id: '4', name: 'Linda A.', email: 'linda@example.com', role: 'moderator', joined: '2025-05-01' },
    { id: '5', name: 'Kamau T.', email: 'kamau@example.com', role: 'member', joined: '2025-06-15' },
    { id: '6', name: 'Wanjiru K.', email: 'wanjiru@example.com', role: 'member', joined: '2025-07-20' }
  ];

  const roleStyles: Record<string, string> = {
    admin: 'bg-primary-500/10 text-primary-600 dark:text-primary-400',
    moderator: 'bg-secondary-500/10 text-secondary-600 dark:text-secondary-400',
    member: 'bg-ink/5 text-ink-light dark:bg-white/5 dark:text-slate-400'
  };

  let search = $state('');
  let filtered = $derived(
    search.trim()
      ? users.filter((u) => u.name.toLowerCase().includes(search.toLowerCase()) || u.email.toLowerCase().includes(search.toLowerCase()))
      : users
  );
</script>

<svelte:head>
  <title>Users — Dashboard</title>
</svelte:head>

<div class="space-y-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-ink dark:text-white">Users</h1>
    <p class="mt-1 text-sm text-ink-light dark:text-slate-400">Manage user accounts and roles.</p>
  </div>

  <div class="grid grid-cols-3 gap-4">
    <div class="glass rounded-xl p-4 text-center">
      <p class="font-display text-2xl font-bold text-ink dark:text-white">{users.length}</p>
      <p class="text-xs text-ink-light dark:text-slate-500">Total users</p>
    </div>
    <div class="glass rounded-xl p-4 text-center">
      <p class="font-display text-2xl font-bold text-primary-500">{users.filter(u => u.role === 'admin').length}</p>
      <p class="text-xs text-ink-light dark:text-slate-500">Admins</p>
    </div>
    <div class="glass rounded-xl p-4 text-center">
      <p class="font-display text-2xl font-bold text-secondary-500">{users.filter(u => u.role === 'moderator').length}</p>
      <p class="text-xs text-ink-light dark:text-slate-500">Moderators</p>
    </div>
  </div>

  <div class="glass rounded-2xl p-5">
    <input type="text" bind:value={search} placeholder="Search users..." class="w-full rounded-xl border border-slate-200/80 bg-fog-light px-4 py-3 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white mb-5" />

    <div class="space-y-2">
      {#each filtered as user (user.id)}
        <div class="flex items-center gap-4 rounded-xl px-4 py-3.5 transition-colors hover:bg-ink/5 dark:hover:bg-white/5">
          <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
            style="background: {avatarColor(user.name)}">
            {user.name.split(' ').map((n) => n[0]).join('').slice(0, 2)}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-ink dark:text-white">{user.name}</p>
            <p class="text-xs text-ink-light dark:text-slate-500">{user.email}</p>
          </div>
          <span class="rounded-lg px-2.5 py-1 text-xs font-medium {roleStyles[user.role]}">
            {user.role}
          </span>
          <span class="text-xs text-ink-light dark:text-slate-500">{formatDate(user.joined)}</span>
          <button class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-light transition-colors hover:bg-ink/5 dark:text-slate-400 dark:hover:bg-white/5">
            <UserCog size={15} />
          </button>
        </div>
      {/each}
    </div>
  </div>
</div>