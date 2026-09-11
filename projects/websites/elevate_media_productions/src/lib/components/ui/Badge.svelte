<script lang="ts">
  export let variant: 'primary' | 'secondary' | 'accent' | 'ghost' = 'primary';
  export let className: string = '';

  const variants = {
    primary: 'bg-primary-500/10 text-primary-600 dark:text-primary-400',
    secondary: 'bg-secondary-500/10 text-secondary-600 dark:text-secondary-400',
    accent: 'bg-accent-500/10 text-accent-600 dark:text-accent-400',
    ghost: 'bg-ink/5 text-ink-light dark:bg-white/5 dark:text-slate-400'
  };
</script>

<span
  class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-medium {variants[variant]} {className}"
>
  <slot />
</span>