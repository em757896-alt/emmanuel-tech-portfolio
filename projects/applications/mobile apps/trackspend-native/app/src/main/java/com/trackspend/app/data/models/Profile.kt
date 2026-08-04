package com.trackspend.app.data.models

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class Profile(
    val id: String = "",
    @SerialName("full_name") val fullName: String = "",
    val email: String = "",
    val country: String = "Kenya",
    val currency: String = "KES",
    val occupation: String = "",
    @SerialName("onboarding_done") val onboardingDone: Boolean = false
)
