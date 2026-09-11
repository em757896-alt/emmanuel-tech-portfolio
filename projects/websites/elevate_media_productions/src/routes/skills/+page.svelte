<script lang="ts">
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import Icon from '$lib/components/ui/Icon.svelte';
  import { skills } from '$lib/config';
</script>

<svelte:head>
  <title>Skills & Stack — Elevate Media Productions</title>
  <meta name="description" content="The technologies, frameworks and disciplines that power every Elevate Media product." />
</svelte:head>

<div class="pt-28 md:pt-36">
  <section class="section-padding relative overflow-hidden">
    <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
    <div class="container-x relative">
      <SectionHeading
        badge="Stack"
        title="What we know"
        subtitle="Technologies we trust, frameworks we love and the skills that make our products reliable."
      />

      <div class="grid gap-7 sm:grid-cols-2">
        {#each skills as group (group.category)}
          <Reveal>
            <div class="glass rounded-2xl p-7 cursor-default">
              <div class="mb-6 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white">
                  <Icon name={group.icon} size={18} />
                </span>
                <h3 class="font-display text-xl font-bold text-ink dark:text-white">{group.category}</h3>
              </div>
              <ul class="space-y-5">
                {#each group.items as skill}
                  <li>
                    <div class="mb-1.5 flex items-center justify-between text-sm">
                      <span class="font-medium text-ink dark:text-slate-200">{skill.name}</span>
                      <span class="text-xs text-ink-light dark:text-slate-500">{skill.level}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-ink/5 dark:bg-white/5">
                      <div
                        class="h-full rounded-full bg-gradient-to-r from-primary-500 to-secondary-500 transition-all duration-1000 ease-out"
                        style="width: {skill.level}%"
                      ></div>
                    </div>
                  </li>
                {/each}
              </ul>
            </div>
          </Reveal>
        {/each}
      </div>
    </div>
  </section>
</div>