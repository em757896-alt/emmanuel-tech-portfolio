<script lang="ts">
  let { class: className = '', children } = $props();
</script>

<div
  class="group relative overflow-hidden rounded-2xl border border-slate-200/60 bg-fog-light/60 p-6 backdrop-blur-xl transition-all duration-500 hover:-translate-y-1.5 hover:shadow-lg dark:border-white/10 dark:bg-night-lighter/60 sm:p-7 {className}"
>
  {@render children?.()}
</div>