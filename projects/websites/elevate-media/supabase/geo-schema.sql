create table if not exists public.geo_countries (
  code text primary key,
  name text not null,
  flag text
);

create table if not exists public.geo_regions (
  id bigint primary key,
  country_code text not null references public.geo_countries(code) on delete cascade,
  name text not null
);

create table if not exists public.geo_cities (
  id bigint primary key,
  region_id bigint references public.geo_regions(id) on delete cascade,
  country_code text not null references public.geo_countries(code) on delete cascade,
  name text not null,
  population int not null default 0
);

create index if not exists geo_regions_country_idx on public.geo_regions(country_code);
create index if not exists geo_cities_region_idx on public.geo_cities(region_id);
create index if not exists geo_cities_country_idx on public.geo_cities(country_code);
