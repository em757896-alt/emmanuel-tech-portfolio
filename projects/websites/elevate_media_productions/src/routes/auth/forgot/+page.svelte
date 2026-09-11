<script lang="ts">
  import { Mail, ArrowLeft } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import Logo from '$lib/components/ui/Logo.svelte';

  let email = $state('');
  let sent = $state(false);
  let loading = $state(false);
  let error = $state('');

  async function onSubmit(e: Event) {
    e.preventDefault();
    loading = true;
    error = '';
    try {
      await new Promise((r) => setTimeout(r, 1000));
      sent = true;
    } catch {
      error = 'An error occurred. Please try again.';
    } finally {
      loading = false;
    }
  }
</script>

<svelte:head>
  <title>Forgot password — Elevate Media</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center px-6">
  <div class="absolute inset-0 -z-10 bg-grid opacity-30"></div>
  <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>

  <Reveal>
    <div class="glass w-full max-w-md rounded-2xl p-8">
      <div class="flex flex-col items-center mb-7">
        <Logo size="md" />
        <h1 class="mt-5 font-display text-2xl font-bold text-ink dark:text-white">Reset password</h1>
        <p class="mt-1 text-sm text-ink-light dark:text-slate-400">We'll email you a reset link.</p>
      </div>

      {#if error}
        <div class="mb-5 rounded-xl bg-secondary-500/10 px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">{error}</div>
      {/if}

      {#if sent}
        <div class="rounded-xl bg-accent-500/10 px-4 py-6 text-center">
          <p class="text-sm font-medium text-accent-500">Check your inbox!</p>
          <p class="mt-1 text-sm text-ink-light dark:text-slate-400">If an account exists for {email}, a reset link is on its way.</p>
        </div>
        <a href="/auth/login" class="btn-outline mt-6 w-full !py-3 !text-ink dark:!text-slate-200">
          <ArrowLeft size={16} /> Back to sign in
        </a>
      {:else}
        <form onsubmit={onSubmit} class="space-y-4">
          <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">Email</label>
            <div class="relative">
              <Mail size={16} class="absolute left-4 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500" />
              <input id="email" type="email" bind:value={email} required placeholder="you@example.com"
                class="w-full rounded-xl border border-slate-200/80 bg-fog-light py-3 pl-11 pr-4 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
            </div>
          </div>
          <button type="submit" disabled={loading} class="btn-gradient w-full !py-3">
            {loading ? 'Sending...' : 'Send reset link'}
          </button>
        </form>
        <a href="/auth/login" class="mt-5 flex items-center justify-center gap-1.5 text-sm text-ink-light transition-colors hover:text-ink dark:text-slate-400 dark:hover:text-white">
          <ArrowLeft size={14} /> Back to sign in
        </a>
      {/if}
    </div>
  </Reveal>
</div>