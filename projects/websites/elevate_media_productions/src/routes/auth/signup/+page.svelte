<script lang="ts">
  import { Mail, Lock, User, Eye, EyeOff, Github, ArrowLeft } from 'lucide-svelte';
  import Reveal from '$lib/components/ui/Reveal.svelte';
  import Logo from '$lib/components/ui/Logo.svelte';
  import { goto } from '$app/navigation';
  import { supabase, supabaseConfigured } from '$lib/supabase';

  let name = $state('');
  let email = $state('');
  let password = $state('');
  let showPw = $state(false);
  let loading = $state(false);
  let error = $state('');
  let sent = $state(false);

  async function onSubmit(e: Event) {
    e.preventDefault();
    loading = true;
    error = '';
    try {
      if (!supabaseConfigured || !supabase) {
        error = 'Authentication is not configured yet. Add your Supabase keys to .env and redeploy.';
        return;
      }
      const { data, error: authError } = await supabase.auth.signUp({
        email,
        password,
        options: {
          data: { full_name: name, username: email.split('@')[0] },
          emailRedirectTo: `${window.location.origin}/auth/login`
        }
      });
      if (authError) {
        error = authError.message;
        return;
      }
      if (data.session) {
        goto('/dashboard');
      } else {
        sent = true;
      }
    } catch {
      error = 'An error occurred. Please try again.';
    } finally {
      loading = false;
    }
  }
</script>

<svelte:head>
  <title>Create account — Elevate Media Productions</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center px-6">
  <div class="absolute inset-0 -z-10 bg-grid opacity-30"></div>
  <div class="absolute -top-32 left-1/2 h-[480px] w-[680px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-[160px] pointer-events-none"></div>

  <Reveal>
    <div class="glass w-full max-w-md rounded-2xl p-8">
      <div class="flex flex-col items-center mb-7">
        <Logo size="md" />
        <h1 class="mt-5 font-display text-2xl font-bold text-ink dark:text-white">Join the community</h1>
        <p class="mt-1 text-sm text-ink-light dark:text-slate-400">Create your Elevate Media account</p>
      </div>

      {#if error}
        <div class="mb-5 rounded-xl bg-secondary-500/10 px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
          {error}
        </div>
      {/if}

      {#if sent}
        <div class="rounded-xl bg-accent-500/10 px-4 py-6 text-center">
          <p class="text-sm font-medium text-accent-500">Check your inbox!</p>
          <p class="mt-1 text-sm text-ink-light dark:text-slate-400">We sent a confirmation link to <span class="font-medium text-ink dark:text-white">{email}</span>. Click it to activate your account, then sign in.</p>
        </div>
        <a href="/auth/login" class="btn-outline mt-6 w-full !py-3 !text-ink dark:!text-slate-200">
          <ArrowLeft size={16} /> Go to sign in
        </a>
      {:else}
        <form onsubmit={onSubmit} class="space-y-4">
        <div>
          <label for="name" class="mb-1.5 block text-sm font-medium text-ink dark:text-slate-200">Full name</label>
          <div class="relative">
            <User size={16} class="absolute left-4 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500" />
            <input id="name" type="text" bind:value={name} required placeholder="Your name"
              class="w-full rounded-xl border border-slate-200/80 bg-fog-light py-3 pl-11 pr-4 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
          </div>
        </div>
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
            <input id="password" type={showPw ? 'text' : 'password'} bind:value={password} required placeholder="Min 8 characters" minlength="8"
              class="w-full rounded-xl border border-slate-200/80 bg-fog-light py-3 pl-11 pr-11 text-sm text-ink outline-none focus:border-primary-500 dark:border-white/10 dark:bg-night-lighter dark:text-white" />
            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-light dark:text-slate-500"
              onclick={() => showPw = !showPw} aria-label="Toggle password">
              {#if showPw}<EyeOff size={16} />{:else}<Eye size={16} />{/if}
            </button>
          </div>
        </div>
        <button type="submit" disabled={loading} class="btn-gradient w-full !py-3">
          {loading ? 'Creating account...' : 'Create account'}
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
        Already have an account?
        <a href="/auth/login" class="font-medium text-primary-500 hover:underline"> Sign in</a>
      </p>
      {/if}
    </div>
  </Reveal>
</div>