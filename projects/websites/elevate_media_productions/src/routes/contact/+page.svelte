<script lang="ts">
  import { Mail, Phone, Send, Github, Linkedin, MessageCircle } from 'lucide-svelte';
  import WhatsApp from '$lib/components/ui/WhatsApp.svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import SectionHeading from '$lib/components/ui/SectionHeading.svelte';
  import { site } from '$lib/config';
  import { faqs } from '$lib/config';

  let name = $state('');
  let email = $state('');
  let subject = $state('');
  let message = $state('');
  let submitted = $state(false);
  let submitting = $state(false);
  let errorMsg = $state('');
  let openFaq = $state<number | null>(null);

  async function onSubmit(e: Event) {
    e.preventDefault();
    if (submitting) return;
    submitting = true;
    errorMsg = '';
    try {
      const res = await fetch('/api/contact', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({ name, email, subject, message })
      });
      if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        errorMsg = (data as { error?: string }).error ?? 'Something went wrong. Please try again.';
        return;
      }
      submitted = true;
      setTimeout(() => { name = ''; email = ''; subject = ''; message = ''; }, 3000);
    } catch {
      errorMsg = 'Network error. Please try again.';
    } finally {
      submitting = false;
    }
  }
</script>

<svelte:head>
  <title>Contact — Elevate Media Productions</title>
  <meta name="description" content="Get in touch with Elevate Media Productions — project inquiries, collaboration and community." />
</svelte:head>

<div class="pt-28 md:pt-36">
  <section class="section-padding relative overflow-hidden">
    <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>
    <div class="container-x relative">
      <SectionHeading
        badge="Contact"
        title="Let's talk"
        subtitle="Have a project in mind or want to collaborate? We reply within 48 hours."
      />

      <div class="grid gap-8 lg:grid-cols-[1.2fr_1.5fr]">
        <Reveal>
          <div class="glass rounded-2xl p-7 space-y-6">
            <div class="flex items-center gap-4 rounded-xl p-4 transition-colors hover:bg-ink/5 dark:hover:bg-white/5">
              <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-500/10 text-primary-500">
                <Mail size={20} />
              </div>
              <div>
                <p class="text-xs text-ink-light dark:text-slate-500">Email us</p>
                <a href="mailto:{site.email}" class="text-sm font-medium text-ink dark:text-white hover:text-primary-500">{site.email}</a>
              </div>
            </div>
            <div class="flex items-center gap-4 rounded-xl p-4 transition-colors hover:bg-ink/5 dark:hover:bg-white/5">
              <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-accent-500/10 text-accent-500">
                <WhatsApp size={20} />
              </div>
              <div>
                <p class="text-xs text-ink-light dark:text-slate-500">WhatsApp</p>
                <a href={site.whatsapp} target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-ink dark:text-white hover:text-primary-500">+254 775 333 673</a>
              </div>
            </div>
            <div class="flex items-center gap-4 rounded-xl p-4 transition-colors hover:bg-ink/5 dark:hover:bg-white/5">
              <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-secondary-500/10 text-secondary-500">
                <Phone size={20} />
              </div>
              <div>
                <p class="text-xs text-ink-light dark:text-slate-500">Call us</p>
                <a href={site.phone} class="text-sm font-medium text-ink dark:text-white hover:text-primary-500">+254 111 275 630</a>
              </div>
            </div>
            <div class="border-t border-slate-200/50 pt-5 dark:border-white/5">
              <p class="mb-3 text-xs font-medium text-ink-light dark:text-slate-500">Find us on</p>
              <div class="flex gap-3">
                {#each [{ icon: Github, href: site.github, label: 'GitHub' }, { icon: Linkedin, href: site.linkedin, label: 'LinkedIn' }] as social}
                  <a href={social.href} target="_blank" rel="noopener noreferrer" aria-label={social.label}
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200/70 text-ink-light transition-all hover:-translate-y-0.5 hover:border-primary-500/50 hover:text-primary-500 dark:border-white/10 dark:text-slate-400 dark:hover:text-primary-400">
                    <social.icon size={17} />
                  </a>
                {/each}
              </div>
            </div>
          </div>
        </Reveal>

        <Reveal>
          {#if submitted}
            <div class="glass flex flex-col items-center justify-center rounded-2xl p-12 text-center">
              <div class="flex h-14 w-14 items-center justify-center rounded-full bg-accent-500/10 text-accent-500">
                <MessageCircle size={26} />
              </div>
              <h3 class="mt-5 font-display text-xl font-bold text-ink dark:text-white">Message received!</h3>
              <p class="mt-2 text-sm text-ink-light dark:text-slate-400">Thanks {name.split(' ')[0] || 'there'} — we will get back to you within 48 hours.</p>
              <button class="btn-outline mt-6 !text-ink dark:!text-slate-200" onclick={() => submitted = false}>
                Send another message
              </button>
            </div>
          {:else}
            <form class="glass rounded-2xl p-7" onsubmit={onSubmit}>
              <div class="space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                  <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">Name</label>
                    <input id="name" type="text" bind:value={name} required placeholder="Your name"
                      class="w-full rounded-xl border border-slate-200/80 bg-fog-light px-4 py-3 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
                  </div>
                  <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">Email</label>
                    <input id="email" type="email" bind:value={email} required placeholder="you@example.com"
                      class="w-full rounded-xl border border-slate-200/80 bg-fog-light px-4 py-3 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
                  </div>
                </div>
                <div>
                  <label for="subject" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">Subject</label>
                  <input id="subject" type="text" bind:value={subject} required placeholder="What's this about?"
                    class="w-full rounded-xl border border-slate-200/80 bg-fog-light px-4 py-3 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
                </div>
                <div>
                  <label for="message" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">Message</label>
                  <textarea id="message" bind:value={message} required rows={6} placeholder="Tell us about your project..."
                    class="w-full rounded-xl border border-slate-200/80 bg-fog-light px-4 py-3 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white resize-y"></textarea>
                </div>
              </div>
              <div class="mt-6 flex flex-col items-end gap-3">
                {#if errorMsg}
                  <p class="text-sm text-red-500" role="alert">{errorMsg}</p>
                {/if}
                <button type="submit" class="btn-gradient !px-6 !py-3" disabled={submitting}>
                  {#if submitting}
                    <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white" aria-hidden="true"></span>
                    Sending...
                  {:else}
                    <Send size={15} /> Send message
                  {/if}
                </button>
              </div>
            </form>
          {/if}
        </Reveal>
      </div>
    </div>
  </section>

  <section class="section-padding relative">
    <div class="container-x mx-auto max-w-3xl">
      <Reveal>
        <h2 class="mb-8 text-center font-display text-2xl font-bold text-ink dark:text-white sm:text-3xl">Frequently asked questions</h2>
      </Reveal>
      <div class="space-y-3">
        {#each faqs as faq, i (i)}
          <Reveal>
            <div class="glass rounded-xl overflow-hidden">
              <button class="flex w-full items-center justify-between p-5 text-left" onclick={() => openFaq = openFaq === i ? null : i}>
                <span class="text-sm font-semibold text-ink dark:text-white pr-4">{faq.question}</span>
                <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-ink/5 text-ink-light transition-transform dark:bg-white/5 dark:text-slate-400"
                  class:rotate-45={openFaq === i}>
                  +
                </span>
              </button>
              {#if openFaq === i}
                <div class="px-5 pb-5 text-sm text-ink-light dark:text-slate-400 leading-relaxed">
                  {faq.answer}
                </div>
              {/if}
            </div>
          </Reveal>
        {/each}
      </div>
    </div>
  </section>
</div>