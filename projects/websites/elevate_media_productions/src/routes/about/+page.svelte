<script lang="ts">
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import Badge from '$lib/components/ui/Badge.svelte';
  import Icon from '$lib/components/ui/Icon.svelte';
  import { site, team, workflow, stats } from '$lib/config';
</script>

<svelte:head>
  <title>About — Elevate Media Productions</title>
  <meta name="description" content="Who we are, what we build and why we exist." />
</svelte:head>

<div class="pt-28 md:pt-36">
  <section class="section-padding relative overflow-hidden">
    <div class="absolute -top-24 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
    <div class="container-x relative">
      <SectionHeading
        badge="About"
        title="Who we are"
        subtitle="Elevate Media Productions is a small, focused studio building digital products that do real work for real people."
      />

      <Reveal>
        <div class="glass mx-auto max-w-3xl rounded-2xl p-8 md:p-12 text-center">
          <div class="flex h-14 w-14 items-center justify-center mx-auto rounded-2xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white shadow-glow mb-6">
            <Icon name="rocket" size={26} />
          </div>
          <p class="text-ink-light dark:text-slate-300 text-lg leading-relaxed">
            We believe every organization deserves a digital experience that feels
            <span class="text-ink font-semibold dark:text-white">intentional</span> — not generic.
            From civic platforms to student portals, from mobile apps to community forums: the thread
            is a belief that products should be <span class="text-ink font-semibold dark:text-white">fast, thoughtful and beautiful</span>.
          </p>
        </div>
      </Reveal>

      <Reveal>
        <div class="mx-auto mt-16 max-w-4xl">
          <h3 class="mb-8 font-display text-2xl font-bold text-center text-ink dark:text-white">The Elevate values</h3>
          <div class="grid gap-6 sm:grid-cols-2">
            {#each [{ title: 'Speed with substance', desc: 'Fast shipping is not an excuse for sloppy work. We ship in slices that compound.' }, { title: 'Honest craft', desc: 'We say what we do not know. We say when something is hard. We do not hide behind hand-waving.' }, { title: 'Community first', desc: 'We build products that make people feel included, not like a checkbox on a dashboard.' }, { title: 'Performance is a feature', desc: 'An accessible, fast, responsive experience is a product decision — not a polish pass.' }] as value}
              <div class="glass rounded-2xl p-6 text-center">
                <h4 class="font-display text-lg font-bold text-ink dark:text-white">{value.title}</h4>
                <p class="mt-2 text-sm text-ink-light dark:text-slate-400 leading-relaxed">{value.desc}</p>
              </div>
            {/each}
          </div>
        </div>
      </Reveal>
    </div>
  </section>

  <section class="section-padding relative">
    <div class="container-x">
      <SectionHeading badge="Team" title="Who builds this" />

      <div class="grid max-w-3xl gap-7 mx-auto sm:grid-cols-2">
        {#each team as member, i (member.name)}
          <Reveal>
            <div class="glass card-hover flex flex-col items-center gap-4 rounded-2xl p-8 text-center">
              <div
                class="flex h-20 w-20 items-center justify-center rounded-full text-2xl font-bold font-display text-white shadow-lg"
                style="background: {member.gradient}"
              >
                {member.initials}
              </div>
              <div>
                <h4 class="font-display text-lg font-bold text-ink dark:text-white">{member.name}</h4>
                <p class="mt-0.5 text-sm font-medium text-primary-500">{member.role}</p>
              </div>
              <p class="text-sm text-ink-light dark:text-slate-400">{member.bio}</p>
            </div>
          </Reveal>
        {/each}
      </div>
    </div>
  </section>

  <section class="section-padding relative">
    <div class="container-x">
      <SectionHeading badge="By the numbers" title="What we have shipped" />

      <Reveal>
        <div class="mx-auto grid max-w-4xl grid-cols-2 gap-6 sm:grid-cols-4">
          {#each stats as stat}
            <div class="glass rounded-2xl p-6 text-center hover:-translate-y-1 transition-transform">
              <span class="block font-display text-3xl font-bold text-ink dark:text-white">{stat.value}</span>
              <span class="mt-1 block text-sm text-ink-light dark:text-slate-400">{stat.label}</span>
            </div>
          {/each}
        </div>
      </Reveal>
    </div>
  </section>
</div>