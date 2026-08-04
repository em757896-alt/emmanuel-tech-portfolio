import { createClient } from "@supabase/supabase-js";

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL!;
const supabaseAnonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!;
const supabaseServiceKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

export const supabase = createClient(supabaseUrl, supabaseAnonKey);

export const supabaseAdmin = supabaseServiceKey
  ? createClient(supabaseUrl, supabaseServiceKey, {
      auth: { autoRefreshToken: false, persistSession: false },
    })
  : null;

export const BUCKETS = {
  AVATARS: "avatars",
  POE: "poe-documents",
  RESEARCH: "research-papers",
  SUBMISSIONS: "assignment-submissions",
  RESULTS: "results",
} as const;

export async function uploadFile(
  bucket: string,
  path: string,
  file: File | Buffer,
  contentType?: string
) {
  const client = supabaseAdmin || supabase;
  const { data, error } = await client.storage
    .from(bucket)
    .upload(path, file, {
      contentType,
      upsert: true,
    });

  if (error) throw error;
  return data;
}

export async function getPublicUrl(bucket: string, path: string) {
  const client = supabaseAdmin || supabase;
  const { data } = client.storage.from(bucket).getPublicUrl(path);
  return data.publicUrl;
}

export async function deleteFile(bucket: string, paths: string[]) {
  const client = supabaseAdmin || supabase;
  const { error } = await client.storage.from(bucket).remove(paths);
  if (error) throw error;
}
