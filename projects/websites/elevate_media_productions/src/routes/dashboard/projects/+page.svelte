<script lang="ts">
  import { FolderOpen, ExternalLink, Github, Plus, Pencil, Trash2 } from 'lucide-svelte';
  import { projects } from '$lib/data/projects';

  let search = $state('');
  let filtered = $derived(
    search.trim()
      ? projects.filter((p) => p.title.toLowerCase().includes(search.toLowerCase()))
      : projects
  );
</script>

<svelte:head>
  <title>Manage Projects — Dashboard</title>
</svelte:head>

<div class="space-y-6">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="font-display text-2xl font-bold text-ink dark:text-white">Projects</h1>
      <p class="mt-1 text-sm text-ink-light dark:text-slate-400">Manage your portfolio projects.</p>
    </div>
    <button class="btn-gradient !px-5 !py-2.5 !text-sm">
      <Plus size={16} /> New project
    </button>
  </div>

  <div class="glass rounded-2xl p-5">
    <input type="text" bind:value={search} placeholder="Search projects..." class="w-full rounded-xl border border-slate-200/80 bg-fog-light px-4 py-3 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white mb-5" />

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-200/60 dark:border-white/10">
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Project</th>
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Category</th>
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Featured</th>
            <th class="pb-3 pr-4 font-medium text-ink-light dark:text-slate-500">Links</th>
            <th class="pb-3 font-medium text-ink-light dark:text-slate-500">Actions</th>
          </tr>
        </thead>
        <tbody>
          {#each filtered as project (project.id)}
            <tr class="border-b border-slate-200/30 dark:border-white/5">
              <td class="py-3.5 pr-4">
                <p class="font-medium text-ink dark:text-white">{project.title}</p>
                <p class="text-xs text-ink-light dark:text-slate-500 line-clamp-1">{project.description}</p>
              </td>
              <td class="py-3.5 pr-4">
                <span class="rounded-lg bg-primary-500/10 px-2.5 py-1 text-xs font-medium text-primary-600 dark:text-primary-400">{project.category}</span>
              </td>
              <td class="py-3.5 pr-4">
                {#if project.featured}
                  <span class="rounded-lg bg-accent-500/10 px-2.5 py-1 text-xs font-medium text-accent-500">Yes</span>
                {:else}
                  <span class="text-xs text-ink-light dark:text-slate-500">No</span>
                {/if}
              </td>
              <td class="py-3.5 pr-4">
                <div class="flex gap-2">
                  {#if project.demo_url}
                    <a href={project.demo_url} target="_blank" rel="noopener noreferrer" class="text-ink-light hover:text-primary-500 dark:text-slate-400">
                      <ExternalLink size={15} />
                    </a>
                  {/if}
                  {#if project.github_url}
                    <a href={project.github_url} target="_blank" rel="noopener noreferrer" class="text-ink-light hover:text-primary-500 dark:text-slate-400">
                      <Github size={15} />
                    </a>
                  {/if}
                </div>
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