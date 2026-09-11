<script lang="ts">
  import {
    Rocket,
    Megaphone,
    Code,
    Smartphone,
    Database,
    Trophy,
    Globe,
    Users,
    PenTool,
    Landmark,
    Zap,
    Layout,
    Palette,
    Circle
  } from 'lucide-svelte';

  const iconMap: Record<string, typeof Globe> = {
    rocket: Rocket,
    megaphone: Megaphone,
    code: Code,
    smartphone: Smartphone,
    database: Database,
    trophy: Trophy,
    globe: Globe,
    users: Users,
    'pen-tool': PenTool,
    landmark: Landmark,
    zap: Zap,
    layout: Layout,
    palette: Palette,
    default: Circle
  };

  let { name = 'default', size = 24, strokeWidth = 2, className = '', color = undefined, fill = undefined } = $props();

  const Icon = $derived(iconMap[name] ?? iconMap.default);
</script>

<Icon {size} {strokeWidth} {color} {fill} class={className} />