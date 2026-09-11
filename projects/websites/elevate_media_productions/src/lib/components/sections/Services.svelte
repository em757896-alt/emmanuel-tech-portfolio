<script lang="ts">
  import Icon from '$lib/components/ui/Icon.svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import { services } from '$lib/config';
</script>

<section class="section-padding relative overflow-hidden">
  <div class="absolute -right-32 top-1/2 h-[460px] w-[460px] -translate-y-1/2 rounded-full bg-primary-500/10 blur-[140px] pointer-events-none"></div>
  <div class="container-x relative">
    <SectionHeading
      badge="Services"
      title="What we build"
      subtitle="End-to-end product engineering — from discovery to launch and the loops that make products better."
    />

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      {#each services as service (service.title)}
        <Reveal>
          <div class="glass card-hover flex flex-col gap-5 rounded-2xl p-7 cursor-default">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white shadow-glow">
              <Icon name={service.icon} size={22} />
            </div>
            <h3 class="font-display text-xl font-bold text-ink dark:text-white">
              {service.title}
            </h3>
            <p class="text-sm leading-relaxed text-ink-light dark:text-slate-400">
              {service.description}
            </p>
            <ul class="mt-auto grid grid-cols-2 gap-x-4 gap-y-2 pt-4 border-t border-slate-200/50 dark:border-white/5">
              {#each service.features as feature}
                <li class="flex items-center gap-2 text-xs text-ink-light dark:text-slate-500">
                  <span class="h-1 w-1 flex-shrink-0 rounded-full bg-primary-500"></span>
                  {feature}
                </li>
              {/each}
            </ul>
          </div>
        </Reveal>
      {/each}
    </div>
  </div>
</section>