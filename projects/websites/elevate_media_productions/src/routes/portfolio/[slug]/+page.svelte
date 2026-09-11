<script lang="ts">
  import { page } from '$app/state';
  import { ExternalLink, Github, ArrowLeft } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import { getProjectBySlug } from '$lib/data/projects';

  const slug = $derived(page.params.slug ?? '');
  const project = $derived(getProjectBySlug(slug));
</script>

<svelte:head>
  <title>{project?.title ?? 'Project'} — Elevate Media Productions</title>
  <meta name="description" content={project?.description} />
</svelte:head>

{#if project}
  <div class="pt-28 md:pt-36">
    <section class="section-padding relative overflow-hidden">
      <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
      <div class="container-x relative">
        <Reveal>
          <a href="/portfolio" class="group mb-10 inline-flex items-center gap-2 text-sm text-ink-light transition-colors hover:text-ink dark:text-slate-400 dark:hover:text-white">
            <ArrowLeft size={16} class="transition-transform group-hover:-translate-x-1" />
            Back to portfolio
          </a>
        </Reveal>

        <div class="mx-auto max-w-4xl">
          <Reveal>
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <Badge>{project.category}</Badge>
                <h1 class="mt-3 font-display text-3xl font-bold text-ink dark:text-white sm:text-4xl md:text-5xl">
                  {project.title}
                </h1>
              </div>
              <div class="flex gap-3">
                {#if project.demo_url}
                  <a href={project.demo_url} target="_blank" rel="noopener noreferrer" class="btn-gradient !px-5 !py-2.5 !text-sm">
                    <ExternalLink size={15} /> Live demo
                  </a>
                {/if}
                {#if project.github_url}
                  <a href={project.github_url} target="_blank" rel="noopener noreferrer" class="btn-outline !px-5 !py-2.5 !text-sm !text-ink dark:!text-slate-200">
                    <Github size={15} /> Source code
                  </a>
                {/if}
              </div>
            </div>
          </Reveal>

          <Reveal>
            <div class="glass overflow-hidden rounded-2xl bg-gradient-to-br from-primary-500/20 via-purple-500/10 to-secondary-500/20">
              <div class="flex h-64 items-center justify-center text-5xl font-bold font-display text-primary-500/15 dark:text-primary-500/10 sm:h-80">
                {project.title.slice(0, 2).toUpperCase()}
              </div>
            </div>
          </Reveal>

          <Reveal>
            <div class="glass mt-8 rounded-2xl p-8 md:p-10">
              <h2 class="mb-4 font-display text-xl font-bold text-ink dark:text-white">About this project</h2>
              <p class="text-ink-light dark:text-slate-300 leading-relaxed">
                {project.long_description ?? project.description}
              </p>
              <div class="mt-6 flex flex-wrap gap-2">
                {#each project.tech_tags as tag}
                  <span class="rounded-lg bg-ink/5 px-3 py-1.5 text-sm font-medium text-ink-light dark:bg-white/5 dark:text-slate-400">
                    {tag}
                  </span>
                {/each}
              </div>
            </div>
          </Reveal>
        </div>
      </div>
    </section>
  </div>
{:else}
  <div class="flex min-h-screen items-center justify-center">
    <p class="text-ink-light dark:text-slate-400">Project not found.</p>
  </div>
{/if}