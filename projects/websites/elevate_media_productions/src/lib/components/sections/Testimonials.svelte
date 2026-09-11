<script lang="ts">
  import { Star } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import { testimonials } from '$lib/data/testimonials';
  import { avatarColor } from '$lib/utils';

  const featured = testimonials.filter((t) => t.featured);
</script>

<section class="section-padding relative overflow-hidden">
  <div class="absolute -left-32 top-1/2 h-[360px] w-[360px] -translate-y-1/2 rounded-full bg-secondary-500/10 blur-[130px] pointer-events-none"></div>
  <div class="container-x relative">
    <SectionHeading
      badge="Testimonials"
      title="What people say"
      subtitle="Real words from real people. Unedited, unscripted."
    />

    <div class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
      {#each featured as testimonial, i (testimonial.id)}
        <Reveal>
          <div class="glass card-hover flex flex-col gap-4 rounded-2xl p-7">
            <div class="flex gap-1 text-amber-400">
              {#each { length: testimonial.rating } as _}
                <Star size={14} fill="currentColor" />
              {/each}
            </div>
            <p class="flex-1 text-sm leading-relaxed text-ink dark:text-slate-300">
              &ldquo;{testimonial.content}&rdquo;
            </p>
            <div class="mt-2 flex items-center gap-3 border-t border-slate-200/50 pt-4 dark:border-white/5">
              <span
                class="flex h-10 w-10 items-center justify-center rounded-full text-xs font-bold text-white"
                style="background: {avatarColor(testimonial.name)}"
              >
                {testimonial.name.split(' ').map((n) => n[0]).join('').slice(0, 2)}
              </span>
              <div>
                <p class="text-sm font-semibold text-ink dark:text-white">{testimonial.name}</p>
                <p class="text-xs text-ink-light dark:text-slate-500">
                  {testimonial.role} · {testimonial.company}
                </p>
              </div>
            </div>
          </div>
        </Reveal>
      {/each}
    </div>
  </div>
</section>