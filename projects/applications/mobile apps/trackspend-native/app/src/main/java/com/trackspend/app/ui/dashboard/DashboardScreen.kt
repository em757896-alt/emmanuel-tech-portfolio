package com.trackspend.app.ui.dashboard

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.trackspend.app.data.models.Profile
import com.trackspend.app.data.models.Transaction
import com.trackspend.app.data.models.TransactionType
import com.trackspend.app.data.remote.AuthRepository
import com.trackspend.app.data.remote.TransactionRepository
import com.trackspend.app.ui.components.BottomNavBar
import kotlinx.coroutines.launch
import java.text.NumberFormat
import java.util.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DashboardScreen(onLogout: () -> Unit, onNavigate: (Int) -> Unit = {}) {
    val scope = rememberCoroutineScope()
    val authRepo = remember { AuthRepository() }
    val txRepo = remember { TransactionRepository() }
    var profile by remember { mutableStateOf<Profile?>(null) }
    var recentTransactions by remember { mutableStateOf<List<Transaction>>(emptyList()) }
    var todayTotal by remember { mutableStateOf(0.0) }
    var weekTotal by remember { mutableStateOf(0.0) }
    var monthTotal by remember { mutableStateOf(0.0) }
    var isLoading by remember { mutableStateOf(true) }

    LaunchedEffect(Unit) {
        profile = authRepo.getProfile()
        recentTransactions = txRepo.getRecent(5)

        val today = java.text.SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).format(Date())
        val calendar = Calendar.getInstance()

        calendar.add(Calendar.DAY_OF_YEAR, -1)
        val yesterday = java.text.SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).format(calendar.time)

        calendar.add(Calendar.DAY_OF_YEAR, -7)
        val weekAgo = java.text.SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).format(calendar.time)

        calendar.add(Calendar.MONTH, -1)
        val monthAgo = java.text.SimpleDateFormat("yyyy-MM-dd", Locale.getDefault()).format(calendar.time)

        todayTotal = txRepo.getTotalForPeriod(yesterday, today)
        weekTotal = txRepo.getTotalForPeriod(weekAgo, today)
        monthTotal = txRepo.getTotalForPeriod(monthAgo, today)

        isLoading = false
    }

    val currency = profile?.currency ?: "KES"
    val name = profile?.fullName?.split(" ")?.firstOrNull() ?: "User"
    val fmt: (Double) -> String = { amt ->
        NumberFormat.getCurrencyInstance(Locale.US).format(amt).replace("USD", currency)
            .replace("$", if (currency == "KES") "KSh " else if (currency == "USD") "$ " else "$ ")
    }

    Scaffold(
        bottomBar = { BottomNavBar(selectedTab = 0, onTabSelected = onNavigate) },
        topBar = {
            TopAppBar(
                title = { Text("TrackSpend") },
                actions = {
                    TextButton(onClick = onLogout) { Text("Logout") }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.background,
                    titleContentColor = MaterialTheme.colorScheme.onBackground
                )
            )
        }
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(16.dp)
                .verticalScroll(rememberScrollState())
        ) {
            Text(
                text = "Welcome back, $name!",
                fontSize = 24.sp,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.onBackground
            )
            Text(
                text = "TrackSpend — Smart Personal Finance Assistant",
                fontSize = 14.sp,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                modifier = Modifier.padding(bottom = 24.dp)
            )

            if (!isLoading) {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceEvenly
                ) {
                    StatCard("Today", fmt(todayTotal), MaterialTheme.colorScheme.primary)
                    StatCard("This Week", fmt(weekTotal), MaterialTheme.colorScheme.secondary)
                    StatCard("This Month", fmt(monthTotal), MaterialTheme.colorScheme.tertiary)
                }

                Spacer(Modifier.height(24.dp))

                Text(
                    "Recent Transactions",
                    fontSize = 18.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = MaterialTheme.colorScheme.onBackground
                )
                Spacer(Modifier.height(8.dp))

                if (recentTransactions.isEmpty()) {
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
                    ) {
                        Text(
                            "No transactions yet. Add your first one!",
                            modifier = Modifier.padding(16.dp),
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                } else {
                    recentTransactions.forEach { tx ->
                        TransactionRow(tx, currency)
                        Spacer(Modifier.height(4.dp))
                    }
                }
            }
        }
    }
}

@Composable
private fun StatCard(label: String, value: String, color: androidx.compose.ui.graphics.Color) {
    Card(
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        modifier = Modifier.width(110.dp)
    ) {
        Column(modifier = Modifier.padding(12.dp), horizontalAlignment = Alignment.CenterHorizontally) {
            Text(label, fontSize = 12.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
            Spacer(Modifier.height(4.dp))
            Text(value, fontSize = 16.sp, fontWeight = FontWeight.Bold, color = color)
        }
    }
}

@Composable
private fun TransactionRow(tx: Transaction, currency: String) {
    val fmt = NumberFormat.getCurrencyInstance(Locale.US).format(tx.amount)
        .replace("USD", currency)
    val typeColor = when (tx.type) {
        TransactionType.INCOME -> MaterialTheme.colorScheme.primary
        TransactionType.EXPENSE -> MaterialTheme.colorScheme.error
        TransactionType.LOAN -> MaterialTheme.colorScheme.tertiary
    }
    val typeIcon = when (tx.type) {
        TransactionType.INCOME -> "↓"
        TransactionType.EXPENSE -> "↑"
        TransactionType.LOAN -> "↔"
    }

    Card(
        modifier = Modifier.fillMaxWidth(),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface)
    ) {
        Row(
            modifier = Modifier.padding(12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = typeIcon,
                style = MaterialTheme.typography.titleLarge,
                color = typeColor
            )
            Spacer(Modifier.width(12.dp))
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    tx.description.ifEmpty { tx.type.name.lowercase().replaceFirstChar { it.uppercase() } },
                    fontWeight = FontWeight.Medium,
                    color = MaterialTheme.colorScheme.onSurface
                )
                if (tx.category != null) {
                    Text(
                        tx.category!!,
                        fontSize = 12.sp,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            }
            Text(fmt, color = typeColor, fontWeight = FontWeight.SemiBold)
        }
    }
}
