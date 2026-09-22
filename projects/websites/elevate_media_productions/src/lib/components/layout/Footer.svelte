<script lang="ts">
  import { Github, Linkedin, Phone, Heart, ArrowUpRight } from 'lucide-svelte';
  import WhatsApp from '$lib/components/ui/WhatsApp.svelte';
  import Logo from '$lib/components/ui/Logo.svelte';
  import { nav, site } from '$lib/config';

  const socials = [
    { label: 'GitHub', href: site.github, Icon: Github },
    { label: 'LinkedIn', href: site.linkedin, Icon: Linkedin },
    { label: 'WhatsApp', href: site.whatsapp, Icon: WhatsApp },
    { label: 'Call', href: site.phone, Icon: Phone }
  ];

  const year = new Date().getFullYear();
</script>

<footer class="relative border-t border-slate-200/60 bg-fog-light dark:border-white/10 dark:bg-night">
  <div class="bg-mesh pointer-events-none absolute inset-0"></div>
  <div class="container-x relative py-16">
    <div class="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
      <div>
        <Logo size="lg" />
        <p class="mt-5 max-w-xs text-sm leading-relaxed text-ink-light dark:text-slate-400">
          {site.description}
        </p>
        <div class="mt-6 flex gap-3">
          {#each socials as { label, href, Icon } (label)}
            <a
              href={href}
              target="_blank"
              rel="noopener noreferrer"
              aria-label={label}
              class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 text-ink-light transition-all duration-300 hover:-translate-y-0.5 hover:border-primary-500/50 hover:text-primary-500 dark:border-white/10 dark:text-slate-400 dark:hover:text-primary-400"
            >
              <Icon size={17} />
            </a>
          {/each}
        </div>
      </div>

      <div>
        <h3 class="font-display text-sm font-semibold uppercase tracking-widest text-ink dark:text-white">
          Navigate
        </h3>
        <ul class="mt-5 space-y-3">
          {#each nav.footer as link (link.href)}
            <li>
              <a
                href={link.href}
                class="group inline-flex items-center gap-1 text-sm text-ink-light transition-colors hover:text-primary-500 dark:text-slate-400 dark:hover:text-primary-400"
              >
                {link.label}
                <ArrowUpRight
                  size={13}
                  class="opacity-0 transition-all duration-200 group-hover:translate-x-0.5 group-hover:opacity-100"
                />
              </a>
            </li>
          {/each}
        </ul>
      </div>

      <div>
        <h3 class="font-display text-sm font-semibold uppercase tracking-widest text-ink dark:text-white">
          Community
        </h3>
        <ul class="mt-5 space-y-3">
          <li><a href="/forum" class="text-sm text-ink-light transition-colors hover:text-primary-500 dark:text-slate-400">Discussions</a></li>
          <li><a href="/blog" class="text-sm text-ink-light transition-colors hover:text-primary-500 dark:text-slate-400">Blog</a></li>
          <li><a href="/portfolio" class="text-sm text-ink-light transition-colors hover:text-primary-500 dark:text-slate-400">Open projects</a></li>
          <li><a href="/about" class="text-sm text-ink-light transition-colors hover:text-primary-500 dark:text-slate-400">About us</a></li>
        </ul>
      </div>

      <div>
        <h3 class="font-display text-sm font-semibold uppercase tracking-widest text-ink dark:text-white">
          Newsletter
        </h3>
        <p class="mt-5 text-sm text-ink-light dark:text-slate-400">
          Monthly builds, launch notes and honest engineering lessons. No spam.
        </p>
        <form
          class="mt-4 flex gap-2"
          action="/?subscribe=1"
          method="get"
        >
          <input
            type="email"
            required
            name="email"
            placeholder="you@example.com"
            class="w-full rounded-xl border border-slate-200/80 bg-fog-light px-4 py-2.5 text-sm text-ink outline-none transition-colors focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white"
          />
          <button type="submit" class="btn-gradient !px-4 !py-2.5 !text-sm">
            Join
          </button>
        </form>
      </div>
    </div>

    <div
      class="mt-14 flex flex-col items-center justify-between gap-4 border-t border-slate-200/60 pt-8 text-sm text-ink-light dark:border-white/10 dark:text-slate-400 md:flex-row"
    >
      <p>© {year} {site.name}. All rights reserved.</p>
      <p class="inline-flex items-center gap-1.5">
        Crafted with <Heart size={14} class="text-secondary-500" fill="currentColor" />
      </p>
    </div>
  </div>
</footer>