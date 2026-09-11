<script lang="ts">
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import { site, stats } from '$lib/config';
</script>

<section class="relative overflow-hidden pb-28 pt-36 md:pt-40 lg:pb-32">
  <div class="absolute inset-0 -z-10 bg-grid opacity-50"></div>

  <div class="absolute -top-24 left-1/2 -translate-x-1/2 h-[600px] w-[800px] rounded-full bg-primary-500/20 blur-[140px] pointer-events-none"></div>
  <div class="absolute -bottom-16 right-1/4 h-[420px] w-[420px] rounded-full bg-secondary-500/20 blur-[120px] pointer-events-none"></div>

  <div class="container-x relative">
    <div class="flex flex-col items-center text-center">
      <Reveal>
        <div class="inline-flex items-center gap-2 rounded-full border border-primary-500/25 bg-primary-500/10 px-5 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 mb-7">
          <span class="animate-pulse-slow inline-block h-2 w-2 rounded-full bg-primary-500"></span>
          Launching our community platform
        </div>
      </Reveal>

      <Reveal>
        <h1 class="font-display text-4xl font-bold leading-[1.08] tracking-tight text-ink dark:text-white sm:text-5xl md:text-6xl lg:text-7xl">
          We craft digital<br class="hidden sm:block" />
          experiences that<br />
          <span class="text-gradient font-bold italic pr-1">elevate</span>
          brands.
        </h1>
      </Reveal>

      <Reveal>
        <p class="mt-6 max-w-2xl text-lg text-ink-light dark:text-slate-400 md:text-xl leading-relaxed">
          Elevate Media Productions builds web applications, mobile apps and community platforms that
          help organizations <span class="text-ink font-medium dark:text-white">launch, grow and engage their audiences</span>.
        </p>
      </Reveal>

      <Reveal>
        <div class="mt-9 flex flex-col items-center gap-4 sm:flex-row">
          <a href="/portfolio" class="btn-gradient text-base px-8 py-3.5">
            See our work
          </a>
          <a href="/about" class="btn-outline text-base px-8 py-3.5 !text-ink dark:!text-slate-200">
            Learn more
          </a>
        </div>
      </Reveal>
    </div>

    <Reveal>
      <div class="mx-auto mt-24 grid max-w-4xl grid-cols-2 gap-6 sm:grid-cols-4">
        {#each stats as stat, i}
          <div
            class="glass group relative overflow-hidden rounded-2xl p-5 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary-500/10 cursor-default"
          >
            <span
              class="block font-display text-2xl font-bold tracking-tight text-ink dark:text-white sm:text-3xl"
            >
              {stat.value}
            </span>
            <span class="mt-1 block text-sm text-ink-light dark:text-slate-400">
              {stat.label}
            </span>
          </div>
        {/each}
      </div>
    </Reveal>
  </div>
</section>