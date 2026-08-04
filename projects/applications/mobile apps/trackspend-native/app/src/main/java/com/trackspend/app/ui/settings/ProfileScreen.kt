package com.trackspend.app.ui.settings

import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.trackspend.app.data.models.Profile
import com.trackspend.app.data.remote.AuthRepository

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ProfileScreen() {
    val authRepo = remember { AuthRepository() }
    var profile by remember { mutableStateOf<Profile?>(null) }
    var isEditing by remember { mutableStateOf(false) }

    LaunchedEffect(Unit) {
        profile = authRepo.getProfile()
    }

    Column(modifier = Modifier.fillMaxSize().padding(16.dp)) {
        Text(
            "Profile",
            fontSize = 24.sp,
            fontWeight = FontWeight.Bold,
            color = MaterialTheme.colorScheme.onBackground
        )
        Spacer(Modifier.height(24.dp))

        profile?.let { p ->
            Card(
                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                modifier = Modifier.fillMaxWidth()
            ) {
                Column(modifier = Modifier.padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    ProfileField("Full Name", p.fullName)
                    ProfileField("Email", p.email)
                    ProfileField("Country", p.country)
                    ProfileField("Currency", p.currency)
                    ProfileField("Occupation", p.occupation)
                }
            }

            Spacer(Modifier.height(16.dp))

            if (!isEditing) {
                Button(onClick = { isEditing = true }) {
                    Text("Change Profile")
                }
            }
        } ?: run {
            CircularProgressIndicator()
        }
    }
}

@Composable
private fun ProfileField(label: String, value: String) {
    Column {
        Text(label, fontSize = 12.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
        Text(value, fontSize = 16.sp, color = MaterialTheme.colorScheme.onSurface)
    }
}
