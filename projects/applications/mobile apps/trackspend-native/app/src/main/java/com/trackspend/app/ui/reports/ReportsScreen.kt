package com.trackspend.app.ui.reports

import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.trackspend.app.data.models.Transaction
import com.trackspend.app.data.models.TransactionType
import com.trackspend.app.data.remote.TransactionRepository
import com.trackspend.app.ui.components.BottomNavBar
import java.text.NumberFormat
import java.util.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ReportsScreen() {
    val repo = remember { TransactionRepository() }
    var transactions by remember { mutableStateOf<List<Transaction>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    var selectedPeriod by remember { mutableStateOf("month") }

    val periods = listOf("day", "week", "month", "year")

    LaunchedEffect(Unit) {
        transactions = repo.getAll()
        isLoading = false
    }

    val totalIncome = transactions.filter { it.type == TransactionType.INCOME }.sumOf { it.amount }
    val totalExpenses = transactions.filter { it.type == TransactionType.EXPENSE }.sumOf { it.amount }
    val totalLoans = transactions.filter { it.type == TransactionType.LOAN }.sumOf { it.amount }

    val categoryTotals = transactions
        .filter { it.type == TransactionType.EXPENSE && it.category != null }
        .groupBy { it.category!! }
        .mapValues { it.value.sumOf { tx -> tx.amount } }
        .entries.sortedByDescending { it.value }

    val fmt: (Double) -> String = {
        NumberFormat.getCurrencyInstance(Locale.US).format(it)
    }

    Scaffold(
        bottomBar = { BottomNavBar(selectedTab = 3) },
        topBar = {
            TopAppBar(
                title = { Text("Reports") },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = MaterialTheme.colorScheme.background)
            )
        }
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(16.dp)
        ) {
            Text("Reports", fontSize = 24.sp, fontWeight = FontWeight.Bold, color = MaterialTheme.colorScheme.onBackground)
            Spacer(Modifier.height(16.dp))

            // Period selector
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                periods.forEach { p ->
                    FilterChip(
                        selected = selectedPeriod == p,
                        onClick = { selectedPeriod = p },
                        label = { Text(p.replaceFirstChar { it.uppercase() }) }
                    )
                }
            }

            Spacer(Modifier.height(16.dp))

            // Summary cards
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceEvenly
            ) {
                SummaryCard("Income", fmt(totalIncome), MaterialTheme.colorScheme.primary)
                SummaryCard("Expenses", fmt(totalExpenses), MaterialTheme.colorScheme.error)
                SummaryCard("Loans", fmt(totalLoans), MaterialTheme.colorScheme.tertiary)
            }

            Spacer(Modifier.height(24.dp))

            Text("Category Breakdown", fontSize = 16.sp, fontWeight = FontWeight.SemiBold, color = MaterialTheme.colorScheme.onBackground)
            Spacer(Modifier.height(8.dp))

            if (categoryTotals.isEmpty()) {
                Text("No data yet", color = MaterialTheme.colorScheme.onSurfaceVariant)
            } else {
                categoryTotals.forEach { (cat, total) ->
                    Column(modifier = Modifier.padding(vertical = 4.dp)) {
                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween
                        ) {
                            Text(cat, color = MaterialTheme.colorScheme.onSurface)
                            Text(fmt(total), color = MaterialTheme.colorScheme.onSurfaceVariant)
                        }
                        val fraction = if (totalExpenses > 0) (total / totalExpenses).toFloat() else 0f
                        LinearProgressIndicator(
                            progress = fraction,
                            modifier = Modifier.fillMaxWidth().height(6.dp),
                            color = MaterialTheme.colorScheme.primary,
                            trackColor = MaterialTheme.colorScheme.surfaceVariant,
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun SummaryCard(label: String, value: String, color: androidx.compose.ui.graphics.Color) {
    Card(
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        modifier = Modifier.width(110.dp)
    ) {
        Column(modifier = Modifier.padding(12.dp), horizontalAlignment = Alignment.CenterHorizontally) {
            Text(label, fontSize = 12.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
            Spacer(Modifier.height(4.dp))
            Text(value, fontSize = 13.sp, fontWeight = FontWeight.Bold, color = color)
        }
    }
}
