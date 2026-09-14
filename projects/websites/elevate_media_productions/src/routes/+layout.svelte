<script lang="ts">
  import '../app.css';
  import { onMount } from 'svelte';
  import Navbar from '$lib/components/layout/Navbar.svelte';
  import Footer from '$lib/components/layout/Footer.svelte';
  import { theme } from '$lib/stores/theme';
  import { getInitialTheme, applyTheme } from '$lib/theme';
  import { syncAuthState } from '$lib/supabase';

  let { children } = $props();

  onMount(() => {
    const initial = getInitialTheme();
    applyTheme(initial);
    theme.set(initial);
    return syncAuthState();
  });

  $effect(() => {
    if (typeof window !== 'undefined') {
      applyTheme($theme);
    }
  });
</script>

<div class="relative min-h-screen">
  <div class="bg-mesh pointer-events-none fixed inset-0 -z-10"></div>
  <Navbar />
  <main>
    {@render children()}
  </main>
  <Footer />
</div>