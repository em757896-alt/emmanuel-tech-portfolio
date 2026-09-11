<script lang="ts">
  import Badge from './Badge.svelte';
  import Reveal from './Reveal.svelte';

  export let badge: string = 'Explore';
  export let title: string;
  export let subtitle: string = '';
  export let center = true;
</script>

<Reveal class="mb-14 md:mb-20">
  <div class="flex {center ? 'flex-col items-center text-center' : ''} gap-5">
    <Badge>{badge}</Badge>
    <h2 class="font-display text-3xl font-bold tracking-tight text-ink dark:text-white sm:text-4xl md:text-5xl">
      {title}
    </h2>
    {#if subtitle}
      <p class="max-w-2xl text-lg text-ink-light dark:text-slate-400">
        {subtitle}
      </p>
    {/if}
  </div>
</Reveal>