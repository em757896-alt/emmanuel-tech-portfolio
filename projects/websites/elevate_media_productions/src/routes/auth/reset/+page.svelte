<script lang="ts">
  import { Lock, Eye, EyeOff, ArrowLeft, CheckCircle2 } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import Logo from '$lib/components/ui/Logo.svelte';
  import { goto } from '$app/navigation';
  import { supabase, supabaseConfigured } from '$lib/supabase';

  let password = $state('');
  let confirm = $state('');
  let showPw = $state(false);
  let loading = $state(false);
  let error = $state('');
  let done = $state(false);

  async function onSubmit(e: Event) {
    e.preventDefault();
    loading = true;
    error = '';
    try {
      if (password.length < 8) {
        error = 'Password must be at least 8 characters.';
        return;
      }
      if (password !== confirm) {
        error = 'Passwords do not match.';
        return;
      }
      if (!supabaseConfigured || !supabase) {
        error = 'Authentication is not configured yet.';
        return;
      }
      const { error: authError } = await supabase.auth.updateUser({ password });
      if (authError) {
        error = authError.message;
        return;
      }
      done = true;
      setTimeout(() => goto('/auth/login'), 1500);
    } catch {
      error = 'An error occurred. Please try again.';
    } finally {
      loading = false;
    }
  }
</script>

<svelte:head>
  <title>Reset password — Elevate Media Productions</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center px-6">
  <div class="absolute inset-0 -z-10 bg-grid opacity-30"></div>
  <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>

  <Reveal>
    <div class="glass w-full max-w-md rounded-2xl p-8">
      <div class="flex flex-col items-center mb-7">
        <Logo size="md" />
        <h1 class="mt-5 font-display text-2xl font-bold text-ink dark:text-white">Set a new password</h1>
        <p class="mt-1 text-sm text-ink-light dark:text-slate-400">Choose a new password for your account.</p>
      </div>

      {#if error}
        <div class="mb-5 rounded-xl bg-secondary-500/10 px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">{error}</div>
      {/if}

      {#if done}
        <div class="rounded-xl bg-accent-500/10 px-4 py-6 text-center">
          <CheckCircle2 size={28} class="mx-auto text-accent-500" />
          <p class="mt-2 text-sm font-medium text-accent-500">Password updated!</p>
          <p class="mt-1 text-sm text-ink-light dark:text-slate-400">Redirecting you to sign in…</p>
        </div>
      {:else}
        <form onsubmit={onSubmit} class="space-y-4">
          <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">New password</label>
            <div class="relative">
              <Lock size={16} class="absolute left-4 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500" />
              <input id="password" type={showPw ? 'text' : 'password'} bind:value={password} required minlength="8" placeholder="Min 8 characters"
                class="w-full rounded-xl border border-slate-200/80 bg-fog-light py-3 pl-11 pr-11 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
              <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500"
                onclick={() => showPw = !showPw} aria-label="Toggle password visibility">
                {#if showPw}<EyeOff size={16} />{:else}<Eye size={16} />{/if}
              </button>
            </div>
          </div>
          <div>
            <label for="confirm" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">Confirm password</label>
            <div class="relative">
              <Lock size={16} class="absolute left-4 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500" />
              <input id="confirm" type={showPw ? 'text' : 'password'} bind:value={confirm} required minlength="8" placeholder="Repeat your password"
                class="w-full rounded-xl border border-slate-200/80 bg-fog-light py-3 pl-11 pr-4 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
            </div>
          </div>
          <button type="submit" disabled={loading} class="btn-gradient w-full !py-3">
            {loading ? 'Updating...' : 'Update password'}
          </button>
        </form>
        <a href="/auth/login" class="mt-5 flex items-center justify-center gap-1.5 text-sm text-ink-light transition-colors hover:text-ink dark:text-slate-400 dark:hover:text-white">
          <ArrowLeft size={14} /> Back to sign in
        </a>
      {/if}
    </div>
  </Reveal>
</div>