package com.trackspend.app.ui.settings

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.trackspend.app.data.models.Profile
import com.trackspend.app.data.remote.AuthRepository
import com.trackspend.app.ui.components.BottomNavBar
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SettingsScreen(onLogout: () -> Unit) {
    val authRepo = remember { AuthRepository() }
    val scope = rememberCoroutineScope()
    var profile by remember { mutableStateOf<Profile?>(null) }
    LaunchedEffect(Unit) { profile = authRepo.getProfile() }

    Scaffold(
        bottomBar = { BottomNavBar(selectedTab = 4) },
        topBar = {
            TopAppBar(
                title = { Text("Settings") },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.background
                )
            )
        }
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(16.dp)
        ) {
            Text(
                "Settings",
                fontSize = 24.sp,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.onBackground
            )
            Spacer(Modifier.height(24.dp))

            SettingsItem(Icons.Default.Person, "Profile", "Manage your profile details") { }
            Spacer(Modifier.height(8.dp))
            SettingsItem(Icons.Default.Palette, "Theme", "Dark mode (always active)") { }
            Spacer(Modifier.height(8.dp))
            SettingsItem(Icons.Default.Security, "Security", "Set or change PIN") { }
            Spacer(Modifier.height(8.dp))
            SettingsItem(Icons.Default.Notifications, "Notifications", "Manage alerts") { }
            Spacer(Modifier.height(8.dp))
            SettingsItem(Icons.Default.Info, "Updates", "Check for updates") { }
            Spacer(Modifier.height(8.dp))
            SettingsItem(Icons.Default.Phone, "Support", "Contact us") { }
            Spacer(Modifier.height(8.dp))
            SettingsItem(Icons.Default.Star, "Premium", "Upgrade to premium") { }
            Spacer(Modifier.height(24.dp))

            Button(
                onClick = {
                    scope.launch {
                        authRepo.logout()
                        onLogout()
                    }
                },
                modifier = Modifier.fillMaxWidth(),
                colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.error)
            ) {
                Icon(Icons.Default.ExitToApp, contentDescription = null)
                Spacer(Modifier.width(8.dp))
                Text("Logout")
            }

            Spacer(Modifier.weight(1f))
            Spacer(Modifier.height(24.dp))
            Text(
                "Created by Elevate Media Productions",
                fontSize = 13.sp,
                fontWeight = FontWeight.Medium,
                color = MaterialTheme.colorScheme.onBackground
            )
            Spacer(Modifier.height(4.dp))
            Text(
                "WhatsApp: +254 775 333 673",
                fontSize = 12.sp,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Text(
                "Call: +254 111 275 630",
                fontSize = 12.sp,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
            Text(
                "Email: em757896@gmail.com",
                fontSize = 12.sp,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun SettingsItem(icon: androidx.compose.ui.graphics.vector.ImageVector, title: String, subtitle: String, onClick: () -> Unit) {
    Card(
        onClick = onClick,
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
    ) {
        Row(
            modifier = Modifier.padding(16.dp),
            horizontalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            Icon(icon, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
            Column {
                Text(title, fontWeight = FontWeight.Medium, color = MaterialTheme.colorScheme.onSurface)
                Text(subtitle, fontSize = 12.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
            }
        }
    }
}
