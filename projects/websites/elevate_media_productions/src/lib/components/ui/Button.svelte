<script lang="ts">
  export let variant: 'primary' | 'secondary' | 'outline' | 'ghost' = 'primary';
  export let size: 'sm' | 'md' | 'lg' = 'md';
  export let href: string | undefined = undefined;
  export let fullWidth = false;
  export let className: string = '';
  export let disabled = false;

  const sizeClasses = {
    sm: 'px-4 py-2 text-xs gap-1.5 rounded-lg',
    md: 'px-6 py-3 text-sm gap-2 rounded-xl',
    lg: 'px-8 py-4 text-base gap-2.5 rounded-xl'
  };

  const variants = {
    primary: 'btn-gradient',
    secondary: 'btn-gradient',
    outline: 'btn-outline !text-ink dark:!text-slate-200',
    ghost: 'text-ink-light hover:text-ink dark:text-slate-400 dark:hover:text-white hover:bg-ink/5 dark:hover:bg-white/5 px-4 py-2 text-sm'
  };
</script>

{#if href}
  <a
    {href}
    class="inline-flex items-center justify-center font-medium transition-all duration-300 {sizeClasses[size]} {variants[variant]} {fullWidth ? 'w-full' : ''} {disabled ? 'pointer-events-none opacity-50' : ''} {className}"
    aria-disabled={disabled}
    {...$$restProps}
  >
    <slot />
  </a>
{:else}
  <button
    type="submit"
    {disabled}
    class="inline-flex items-center justify-center font-medium transition-all duration-300 {sizeClasses[size]} {variants[variant]} {fullWidth ? 'w-full' : ''} {disabled ? 'pointer-events-none opacity-50' : ''} {className}"
    {...$$restProps}
  >
    <slot />
  </button>
{/if}