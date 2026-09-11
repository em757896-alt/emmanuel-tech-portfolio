<script lang="ts">
  import Hero from '$lib/components/sections/Hero.svelte';
  import FeaturedProjects from '$lib/components/sections/FeaturedProjects.svelte';
  import Services from '$lib/components/sections/Services.svelte';
  import Testimonials from '$lib/components/sections/Testimonials.svelte';
  import Workflow from '$lib/components/sections/Workflow.svelte';
  import ForumPreview from '$lib/components/sections/ForumPreview.svelte';
  import BlogPreview from '$lib/components/sections/BlogPreview.svelte';
  import CTA from '$lib/components/sections/CTA.svelte';
</script>

<svelte:head>
  <title>Elevate Media Productions — We craft digital experiences that elevate brands.</title>
  <meta
    name="description"
    content="Elevate Media Productions builds web applications, mobile apps and community platforms that help organizations launch, grow and engage their audiences."
  />
  <meta property="og:title" content="Elevate Media Productions" />
  <meta property="og:description" content="Web applications, mobile apps and brand platforms — crafted to elevate." />
</svelte:head>

<Hero />
<FeaturedProjects />
<Services />
<Workflow />
<ForumPreview />
<Testimonials />
<BlogPreview />
<CTA />