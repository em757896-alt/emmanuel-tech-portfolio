<script lang="ts">
  import { onMount } from 'svelte';

  let node: HTMLDivElement;

  onMount(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          observer.unobserve(entry.target);
        }
      },
      { threshold: 0.12, rootMargin: '0px 0px -50px 0px' }
    );

    observer.observe(node);
    return () => observer.disconnect();
  });
</script>

<div bind:this={node} class="reveal">
  <slot />
</div>