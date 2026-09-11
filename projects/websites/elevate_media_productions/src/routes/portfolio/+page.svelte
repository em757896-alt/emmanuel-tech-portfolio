<script lang="ts">
  import { ExternalLink, Github, ArrowRight } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import { projects, getProjectCategories } from '$lib/data/projects';

  let activeCategory = $state('All');
  const categories = ['All', ...getProjectCategories()];
  let filtered = $derived(
    activeCategory === 'All' ? projects : projects.filter((p) => p.category === activeCategory)
  );
</script>

<svelte:head>
  <title>Our Work — Elevate Media Productions</title>
  <meta name="description" content="A curated showcase of web applications, mobile apps and platforms built by Elevate Media Productions." />
</svelte:head>

<div class="pt-28 md:pt-36">
  <section class="section-padding relative overflow-hidden">
    <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
    <div class="container-x relative">
      <SectionHeading
        badge="Portfolio"
        title="Our work"
        subtitle="A curated look at the products we have built, scaled and shipped."
      />

      <Reveal>
        <div class="mb-10 flex flex-wrap items-center justify-center gap-2">
          {#each categories as category}
            <button
              class="rounded-xl px-4 py-2 text-sm font-medium transition-all duration-300
                {activeCategory === category
                  ? 'bg-gradient-to-r from-primary-500 to-secondary-500 text-white shadow-glow'
                  : 'bg-ink/5 text-ink-light hover:bg-ink/10 dark:bg-white/5 dark:text-slate-400 dark:hover:bg-white/10'}"
              onclick={() => (activeCategory = category)}
            >
              {category}
            </button>
          {/each}
        </div>
      </Reveal>

      <div class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
        {#each filtered as project (project.id)}
          <Reveal>
            <div class="glass card-hover flex flex-col overflow-hidden rounded-2xl">
              <div class="relative h-48 w-full bg-gradient-to-br from-primary-500/20 to-secondary-500/15">
                {#if project.image_url}
                  <img src={project.image_url} alt={project.title} class="h-full w-full object-cover" />
                {:else}
                  <div class="flex h-full items-center justify-center text-4xl font-bold font-display text-primary-500/20 dark:text-primary-500/15">
                    {project.title.slice(0, 2).toUpperCase()}
                  </div>
                {/if}
                <Badge className="absolute top-4 left-4">{project.category}</Badge>
              </div>
              <div class="flex flex-1 flex-col p-6">
                <h3 class="font-display text-lg font-bold text-ink dark:text-white">{project.title}</h3>
                <p class="mt-2 line-clamp-3 text-sm text-ink-light dark:text-slate-400">{project.description}</p>
                <div class="mt-auto flex flex-wrap gap-2 pt-4">
                  {#each project.tech_tags.slice(0, 4) as tag}
                    <span class="rounded-md bg-ink/5 px-2 py-1 text-xs text-ink-light dark:bg-white/5 dark:text-slate-500">{tag}</span>
                  {/each}
                </div>
                <div class="mt-5 flex gap-3 border-t border-slate-200/50 pt-4 dark:border-white/5">
                  {#if project.demo_url}
                    <a href={project.demo_url} target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-500 hover:text-primary-600">
                      <ExternalLink size={14} /> Live
                    </a>
                  {/if}
                  {#if project.github_url}
                    <a href={project.github_url} target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-light dark:text-slate-400 hover:text-ink dark:hover:text-white">
                      <Github size={14} /> Source
                    </a>
                  {/if}
                </div>
              </div>
            </div>
          </Reveal>
        {/each}
      </div>
    </div>
  </section>
</div>