package com.trackspend.app.ui.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

private val DarkColors = darkColorScheme(
    primary = Color(0xFF818CF8),
    onPrimary = Color(0xFF030712),
    primaryContainer = Color(0xFF312E81),
    secondary = Color(0xFF64748B),
    onSecondary = Color(0xFFF8FAFC),
    surface = Color(0xFF0F172A),
    onSurface = Color(0xFFF8FAFC),
    surfaceVariant = Color(0xFF1E293B),
    onSurfaceVariant = Color(0xFF94A3B8),
    background = Color(0xFF030712),
    onBackground = Color(0xFFF8FAFC),
    error = Color(0xFFEF4444),
    onError = Color(0xFFF8FAFC),
    outline = Color(0xFF334155)
)

@Composable
fun TrackSpendTheme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = DarkColors,
        content = content
    )
}
