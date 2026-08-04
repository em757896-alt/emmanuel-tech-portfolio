package com.trackspend.app.data.models

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class Category(
    val id: String = "",
    @SerialName("user_id") val userId: String? = null,
    val name: String = "",
    val color: String = "#6366F1",
    val icon: String = "tag",
    @SerialName("is_system") val isSystem: Boolean = false
)
