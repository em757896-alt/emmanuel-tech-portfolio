package com.trackspend.app.data.remote

import com.trackspend.app.BuildConfig
import io.github.jan.supabase.SupabaseClient as Supabase
import io.github.jan.supabase.createSupabaseClient
import io.github.jan.supabase.gotrue.FlowType
import io.github.jan.supabase.gotrue.Auth
import io.github.jan.supabase.gotrue.auth
import io.github.jan.supabase.postgrest.Postgrest
import io.github.jan.supabase.postgrest.postgrest

/**
 * Supabase client built from BuildConfig values injected at compile time from
 * `supabase.url` / `supabase.anonKey` (gradle property, environment, or local.properties).
 *
 * Only the *anon* key belongs in a client. It is designed to be readable from the APK and is
 * safe solely because row-level security restricts what it can access. A service-role key must
 * never be shipped to a device.
 */
class SupabaseClient {
    val client: Supabase = createSupabaseClient(
        supabaseUrl = BuildConfig.SUPABASE_URL,
        supabaseKey = BuildConfig.SUPABASE_ANON_KEY
    ) {
        install(Postgrest)
        install(Auth) {
            flowType = FlowType.IMPLICIT
        }
    }

    val auth get() = client.auth
    val postgrest get() = client.postgrest
}
