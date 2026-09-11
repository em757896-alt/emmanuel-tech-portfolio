<script lang="ts">
  import { Mail, Lock, Github, Eye, EyeOff } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import Logo from '$lib/components/ui/Logo.svelte';

  let email = $state('');
  let password = $state('');
  let showPw = $state(false);
  let loading = $state(false);
  let error = $state('');

  async function onSubmit(e: Event) {
    e.preventDefault();
    loading = true;
    error = '';
    try {
      // Supabase auth will be connected when keys are configured
      await new Promise((r) => setTimeout(r, 1000));
      error = 'Supabase is not configured yet. Add your keys to .env to enable authentication.';
    } catch {
      error = 'An error occurred. Please try again.';
    } finally {
      loading = false;
    }
  }
</script>

<svelte:head>
  <title>Sign in — Elevate Media Productions</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center px-6">
  <div class="absolute inset-0 -z-10 bg-grid opacity-30"></div>
  <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>

  <Reveal>
    <div class="glass w-full max-w-md rounded-2xl p-8">
      <div class="flex flex-col items-center mb-7">
        <Logo size="md" />
        <h1 class="mt-5 font-display text-2xl font-bold text-ink dark:text-white">Welcome back</h1>
        <p class="mt-1 text-sm text-ink-light dark:text-slate-400">Sign in to your account</p>
      </div>

      {#if error}
        <div class="mb-5 rounded-xl bg-secondary-500/10 px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
          {error}
        </div>
      {/if}

      <form onsubmit={onSubmit} class="space-y-4">
        <div>
          <label for="email" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">Email</label>
          <div class="relative">
            <Mail size={16} class="absolute left-4 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500" />
            <input id="email" type="email" bind:value={email} required placeholder="you@example.com"
              class="w-full rounded-xl border border-slate-200/80 bg-fog-light py-3 pl-11 pr-4 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
          </div>
        </div>
        <div>
          <label for="password" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">Password</label>
          <div class="relative">
            <Lock size={16} class="absolute left-4 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500" />
            <input id="password" type={showPw ? 'text' : 'password'} bind:value={password} required placeholder="Enter your password"
              class="w-full rounded-xl border border-slate-200/80 bg-fog-light py-3 pl-11 pr-11 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500"
              onclick={() => showPw = !showPw} aria-label="Toggle password visibility">
              {#if showPw}
                <EyeOff size={16} />
              {:else}
                <Eye size={16} />
              {/if}
            </button>
          </div>
          <a href="/auth/forgot" class="mt-2 block text-xs text-primary-500 hover:underline">Forgot password?</a>
        </div>
        <button type="submit" disabled={loading} class="btn-gradient w-full !py-3">
          {loading ? 'Signing in...' : 'Sign in'}
        </button>
      </form>

      <div class="my-5 flex items-center gap-4">
        <span class="h-px flex-1 bg-slate-200/60 dark:bg-white/10"></span>
        <span class="text-xs text-ink-light dark:text-slate-500">or continue with</span>
        <span class="h-px flex-1 bg-slate-200/60 dark:bg-white/10"></span>
      </div>

      <button type="button" class="btn-outline w-full !py-3 !text-ink dark:!text-slate-200">
        <Github size={17} /> GitHub
      </button>

      <p class="mt-6 text-center text-sm text-ink-light dark:text-slate-400">
        Don't have an account?
        <a href="/auth/signup" class="font-medium text-primary-500 hover:underline"> Create one free</a>
      </p>
    </div>
  </Reveal>
</div>