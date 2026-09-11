<script lang="ts">
  import { ArrowRight, ExternalLink } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import { projects, getFeaturedProjects } from '$lib/data/projects';

  const featured = getFeaturedProjects();
</script>

<section class="section-padding">
  <SectionHeading
    badge="Work"
    title="Featured projects"
    subtitle="A curated look at the products we have built, scaled and shipped."
  />

  <div class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
    {#each featured as project (project.id)}
      <Reveal>
        <a
          href="/portfolio/{project.slug}"
          class="group glass card-hover flex flex-col overflow-hidden rounded-2xl"
        >
          <div
            class="relative h-48 w-full overflow-hidden bg-gradient-to-br from-primary-500/20 to-secondary-500/15"
          >
            {#if project.image_url}
              <img src={project.image_url} alt={project.title} class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
            {:else}
              <div class="flex h-full items-center justify-center text-4xl font-bold font-display text-primary-500/20 dark:text-primary-500/15">
                {project.title.slice(0, 2).toUpperCase()}
              </div>
            {/if}
            <Badge className="absolute top-4 left-4">{project.category}</Badge>
          </div>
          <div class="flex flex-1 flex-col p-6">
            <h3 class="font-display text-lg font-bold text-ink transition-colors group-hover:text-primary-500 dark:text-white">
              {project.title}
            </h3>
            <p class="mt-2 line-clamp-2 text-sm text-ink-light dark:text-slate-400">
              {project.description}
            </p>
            <div class="mt-auto flex flex-wrap gap-2 pt-4">
              {#each project.tech_tags.slice(0, 4) as tag}
                <span class="rounded-md bg-ink/5 px-2 py-1 text-xs text-ink-light dark:bg-white/5 dark:text-slate-400">
                  {tag}
                </span>
              {/each}
            </div>
          </div>
        </a>
      </Reveal>
    {/each}
  </div>

  <Reveal>
    <div class="mt-14 text-center">
      <a href="/portfolio" class="group inline-flex items-center gap-2 text-sm font-semibold text-primary-500 transition-colors hover:text-primary-600">
        View all projects
        <ArrowRight size={16} class="transition-transform group-hover:translate-x-0.5" />
      </a>
    </div>
  </Reveal>
</section>