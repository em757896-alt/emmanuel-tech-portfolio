package com.trackspend.app.data.remote

import io.github.jan.supabase.SupabaseClient as Supabase
import io.github.jan.supabase.createSupabaseClient
import io.github.jan.supabase.gotrue.FlowType
import io.github.jan.supabase.gotrue.Auth
import io.github.jan.supabase.gotrue.auth
import io.github.jan.supabase.postgrest.Postgrest
import io.github.jan.supabase.postgrest.postgrest

class SupabaseClient {
    val client: Supabase = createSupabaseClient(
        supabaseUrl = "https://ajrqpyutbkpbnggowwod.supabase.co",
        supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFqcnFweXV0YmtwYm5nZ293d29kIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODI3NzQ5NDUsImV4cCI6MjA5ODM1MDk0NX0.deWwwk7YiK75a5p6E3IWQ5FT_QNGMaFUE3ol3InUT48"
    ) {
        install(Postgrest)
        install(Auth) {
            flowType = FlowType.IMPLICIT
        }
    }

    val auth get() = client.auth
    val postgrest get() = client.postgrest
}
