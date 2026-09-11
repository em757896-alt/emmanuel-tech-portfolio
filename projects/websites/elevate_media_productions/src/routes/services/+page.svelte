<script lang="ts">
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import Icon from '$lib/components/ui/Icon.svelte';
  import { services, workflow } from '$lib/config';
  import { CheckCircle2, MessageCircle } from 'lucide-svelte';
</script>

<svelte:head>
  <title>Services — Elevate Media Productions</title>
  <meta name="description" content="Web applications, mobile apps, brand platforms and performance engineering — end-to-end product services." />
</svelte:head>

<div class="pt-28 md:pt-36">
  <section class="section-padding relative overflow-hidden">
    <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
    <div class="container-x relative">
      <SectionHeading
        badge="Services"
        title="What we do best"
        subtitle="End-to-end engineering — from discovery to launch and every iteration after."
      />

      <div class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
        {#each services as service (service.title)}
          <Reveal>
            <div class="glass card-hover flex flex-col gap-5 rounded-2xl p-7">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white shadow-glow">
                <Icon name={service.icon} size={22} />
              </div>
              <h3 class="font-display text-xl font-bold text-ink dark:text-white">{service.title}</h3>
              <p class="text-sm leading-relaxed text-ink-light dark:text-slate-400">{service.description}</p>
              <ul class="mt-auto flex flex-col gap-2.5 pt-4 border-t border-slate-200/50 dark:border-white/5">
                {#each service.features as feature}
                  <li class="flex items-center gap-2 text-sm text-ink dark:text-slate-300">
                    <CheckCircle2 size={16} class="flex-shrink-0 text-accent-500" />
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

  <section class="section-padding relative">
    <div class="container-x">
      <SectionHeading badge="Process" title="Our process" subtitle="Four deliberate steps, every engagement." />
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {#each workflow as step}
          <Reveal>
            <div class="glass relative overflow-hidden rounded-2xl p-7 group cursor-default">
              <span class="absolute -right-4 -top-6 font-display text-[5.5rem] font-bold leading-none text-ink/[0.03] dark:text-white/[0.03]">
                {step.step}
              </span>
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-secondary-500 text-sm font-bold text-white">
                {step.step}
              </div>
              <h3 class="mt-5 font-display text-xl font-bold text-ink dark:text-white">{step.title}</h3>
              <p class="mt-2 text-sm text-ink-light dark:text-slate-400 leading-relaxed">{step.description}</p>
            </div>
          </Reveal>
        {/each}
      </div>
    </div>
  </section>

  <section class="section-padding relative">
    <div class="container-x">
      <div class="glass rounded-3xl p-10 md:p-14 text-center relative overflow-hidden">
        <div class="absolute -right-20 -top-20 h-48 w-48 rounded-full bg-primary-500/15 blur-3xl pointer-events-none"></div>
        <div class="relative">
          <h2 class="font-display text-2xl font-bold text-ink dark:text-white sm:text-3xl">Have a project in mind?</h2>
          <p class="mt-3 text-ink-light dark:text-slate-400">We typically reply within 48 hours with an honest assessment.</p>
          <a href="/contact" class="btn-gradient mt-6 inline-flex text-base px-8 py-3.5">
            <MessageCircle size={18} /> Start a conversation
          </a>
        </div>
      </div>
    </div>
  </section>
</div>