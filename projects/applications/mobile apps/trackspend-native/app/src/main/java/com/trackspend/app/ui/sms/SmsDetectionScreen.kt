package com.trackspend.app.ui.sms

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.Close
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.trackspend.app.data.remote.TransactionRepository
import com.trackspend.app.services.PendingSmsStore
import com.trackspend.app.ui.components.BottomNavBar
import kotlinx.coroutines.launch

@Composable
fun SmsDetectionScreen() {
    val repo = remember { TransactionRepository() }
    val scope = rememberCoroutineScope()
    var items by remember { mutableStateOf(PendingSmsStore.getAll()) }
    var error by remember { mutableStateOf<String?>(null) }

    Scaffold(
        bottomBar = { BottomNavBar(selectedTab = 2) }
    ) { padding ->
        Column(modifier = Modifier.fillMaxSize().padding(padding)) {
            Text(
                "SMS Detection",
                fontSize = 20.sp,
                fontWeight = FontWeight.Bold,
                modifier = Modifier.padding(16.dp),
                color = MaterialTheme.colorScheme.onBackground
            )

            if (items.isEmpty()) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text(
                            "No pending SMS",
                            fontSize = 16.sp,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                        Spacer(Modifier.height(8.dp))
                        Text(
                            "New SMS from M-Pesa, bank, or payment services will appear here",
                            fontSize = 14.sp,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }
            } else {
                if (error != null) {
                    Text(error!!, color = MaterialTheme.colorScheme.error, modifier = Modifier.padding(horizontal = 16.dp))
                }
                LazyColumn(modifier = Modifier.padding(horizontal = 16.dp)) {
                    itemsIndexed(items) { index, item ->
                        Card(
                            colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                            modifier = Modifier.fillMaxWidth()
                        ) {
                            Column(modifier = Modifier.padding(12.dp)) {
                                Text(
                                    item.transaction?.description ?: "Unknown",
                                    fontWeight = FontWeight.Medium,
                                    color = MaterialTheme.colorScheme.onSurface
                                )
                                Text(
                                    item.sender,
                                    fontSize = 12.sp,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant
                                )
                                if (item.transaction != null) {
                                    Text(
                                        "Amount: ${item.transaction.amount}",
                                        color = MaterialTheme.colorScheme.primary,
                                        fontWeight = FontWeight.SemiBold
                                    )
                                }

                                Spacer(Modifier.height(8.dp))
                                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                                    IconButton(
                                        onClick = {
                                            scope.launch {
                                                try {
                                                    item.transaction?.let { repo.create(it) }
                                                    PendingSmsStore.remove(index)
                                                    items = PendingSmsStore.getAll()
                                                } catch (e: Exception) {
                                                    error = "Failed to save: ${e.message}"
                                                }
                                            }
                                        }
                                    ) {
                                        Icon(Icons.Default.Check, contentDescription = "Save", tint = MaterialTheme.colorScheme.primary)
                                    }
                                    IconButton(
                                        onClick = {
                                            PendingSmsStore.remove(index)
                                            items = PendingSmsStore.getAll()
                                        }
                                    ) {
                                        Icon(Icons.Default.Close, contentDescription = "Ignore", tint = MaterialTheme.colorScheme.error)
                                    }
                                }
                            }
                        }
                        Spacer(Modifier.height(8.dp))
                    }
                }
            }
        }
    }
}
