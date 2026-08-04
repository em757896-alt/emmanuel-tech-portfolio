package com.trackspend.app.data.remote

import com.trackspend.app.TrackSpendApp
import com.trackspend.app.data.models.Profile
import io.github.jan.supabase.gotrue.providers.builtin.Email
import io.github.jan.supabase.gotrue.user.UserInfo
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import kotlinx.serialization.json.Json
import kotlinx.serialization.json.buildJsonObject
import kotlinx.serialization.json.decodeFromJsonElement
import kotlinx.serialization.json.jsonObject
import kotlinx.serialization.json.JsonPrimitive

class AuthRepository {
    private val client get() = TrackSpendApp.instance.supabaseClient
    private val json = Json { ignoreUnknownKeys = true }

    suspend fun isLoggedIn(): Boolean = withContext(Dispatchers.IO) {
        try {
            client.auth.currentUserOrNull() != null
        } catch (e: Exception) { false }
    }

    suspend fun hasOnboarded(): Boolean = withContext(Dispatchers.IO) {
        try {
            val profile = getProfile()
            profile?.onboardingDone ?: true
        } catch (e: Exception) { true }
    }

    suspend fun setOnboardingDone() = withContext(Dispatchers.IO) {
        val userId = client.auth.currentUserOrNull()?.id ?: return@withContext
        client.postgrest["profiles"].update(
            """{"onboarding_done": true}""",
            { this.filter { eq("id", userId) } }
        )
    }

    suspend fun getProfile(): Profile? = withContext(Dispatchers.IO) {
        try {
            val userId = client.auth.currentUserOrNull()?.id ?: return@withContext null
            val response = client.postgrest["profiles"].select {
                this.filter { eq("id", userId) }
                this.limit(1)
            }
            response.decodeList<Profile>().firstOrNull()
        } catch (e: Exception) { null }
    }

    suspend fun signUp(email: String, password: String, profile: Profile) = withContext(Dispatchers.IO) {
        client.auth.signUpWith(Email) {
            this.email = email
            this.password = password
            data = buildJsonObject {
                put("full_name", JsonPrimitive(profile.fullName))
                put("country", JsonPrimitive(profile.country))
                put("currency", JsonPrimitive(profile.currency))
                put("occupation", JsonPrimitive(profile.occupation))
            }
        }
    }

    suspend fun signIn(email: String, password: String) = withContext(Dispatchers.IO) {
        client.auth.signInWith(Email) {
            this.email = email
            this.password = password
        }
    }

    suspend fun logout() = withContext(Dispatchers.IO) {
        client.auth.signOut()
    }
}
