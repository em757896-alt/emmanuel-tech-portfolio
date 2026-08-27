-- TrackSpend v2.1.1 - Missing columns migration + test account profile
-- Run this in Supabase Dashboard -> SQL Editor (project: ajrqpyutbkpbnggowwod)
-- Idempotent: safe to re-run.

-- 1. Ensure all profile columns exist (older SQL never added these)
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS email TEXT;
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS full_name TEXT;
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS country TEXT DEFAULT '';
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS currency TEXT DEFAULT 'USD';
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS occupation TEXT DEFAULT '';
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS premium_tier INTEGER DEFAULT 0;
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS onboarding_done BOOLEAN DEFAULT FALSE;
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS created_at TIMESTAMPTZ DEFAULT NOW();
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS updated_at TIMESTAMPTZ DEFAULT NOW();

-- 2. Test account profile (email: lajolir210@ebflyai.com, username: lajo12)
INSERT INTO public.profiles (id, email, full_name, username, country, currency, occupation, premium_tier, onboarding_done)
VALUES (
  '2ae7d69e-be3c-4754-882c-b04a9b22b93e',
  'lajolir210@ebflyai.com',
  'Lajo Test',
  'lajo12',
  'India',
  'INR',
  '',
  0,
  true
)
ON CONFLICT (id) DO UPDATE SET
  email = EXCLUDED.email,
  full_name = EXCLUDED.full_name,
  username = EXCLUDED.username,
  country = EXCLUDED.country,
  currency = EXCLUDED.currency,
  occupation = EXCLUDED.occupation,
  premium_tier = EXCLUDED.premium_tier,
  onboarding_done = EXCLUDED.onboarding_done,
  updated_at = NOW();

-- 3. Make sure the auto-create trigger still works for future signups
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER SET search_path = public
AS $$
BEGIN
  BEGIN
    INSERT INTO public.profiles (id, email, full_name, username, country, currency, occupation, premium_tier, onboarding_done)
    VALUES (
      new.id,
      new.email,
      COALESCE(new.raw_user_meta_data ->> 'full_name', ''),
      COALESCE(new.raw_user_meta_data ->> 'username', ''),
      COALESCE(new.raw_user_meta_data ->> 'country', 'Kenya'),
      COALESCE(new.raw_user_meta_data ->> 'currency', 'KES'),
      COALESCE(new.raw_user_meta_data ->> 'occupation', ''),
      0,
      false
    )
    ON CONFLICT (id) DO NOTHING;
  EXCEPTION WHEN OTHERS THEN
    RAISE WARNING 'handle_new_user: profile insert failed for %: %', new.id, SQLERRM;
  END;
  RETURN new;
END;
$$;

DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();