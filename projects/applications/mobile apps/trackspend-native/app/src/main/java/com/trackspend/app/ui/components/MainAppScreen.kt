package com.trackspend.app.ui.components

import androidx.compose.runtime.*
import com.trackspend.app.ui.dashboard.DashboardScreen
import com.trackspend.app.ui.reports.ReportsScreen
import com.trackspend.app.ui.settings.SettingsScreen
import com.trackspend.app.ui.sms.SmsDetectionScreen
import com.trackspend.app.ui.transactions.TransactionsScreen

@Composable
fun MainAppScreen(onLogout: () -> Unit) {
    var selectedTab by remember { mutableIntStateOf(0) }

    when (selectedTab) {
        0 -> DashboardScreen(
            onLogout = onLogout,
            onNavigate = { tab -> selectedTab = tab }
        )
        1 -> TransactionsScreen()
        2 -> SmsDetectionScreen()
        3 -> ReportsScreen()
        4 -> SettingsScreen(onLogout = onLogout)
    }
}
