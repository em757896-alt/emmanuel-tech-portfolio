<script lang="ts">
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import { workflow } from '$lib/config';
</script>

<section class="section-padding relative">
  <div class="container-x">
    <SectionHeading
      badge="Process"
      title="How we work"
      subtitle="Four deliberate phases, each designed to reduce risk and increase clarity."
    />

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      {#each workflow as step, i (step.step)}
        <Reveal>
          <div class="glass relative overflow-hidden rounded-2xl p-7 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary-500/10 group cursor-default">
            <span
              class="absolute -right-4 -top-6 font-display text-[6rem] font-bold leading-none text-ink/[0.03] dark:text-white/[0.03] transition-transform duration-300 group-hover:scale-110"
            >
              {step.step}
            </span>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-secondary-500 text-sm font-bold text-white">
              {step.step}
            </div>
            <h3 class="mt-5 font-display text-xl font-bold text-ink dark:text-white">
              {step.title}
            </h3>
            <p class="mt-2 text-sm text-ink-light dark:text-slate-400 leading-relaxed">
              {step.description}
            </p>
          </div>
        </Reveal>
      {/each}
    </div>
  </div>
</section>