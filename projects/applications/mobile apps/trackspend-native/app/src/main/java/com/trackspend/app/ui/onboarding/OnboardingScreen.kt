package com.trackspend.app.ui.onboarding

import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp

@Composable
fun OnboardingScreen(onComplete: () -> Unit) {
    var page by remember { mutableIntStateOf(0) }
    val slides = listOf(
        Triple("Welcome to TrackSpend", "Your smart personal finance assistant. Track expenses, manage budgets, and take control of your money.", "💰"),
        Triple("Smart SMS Detection", "TrackSpend automatically detects and categorises transactions from M-Pesa, bank, and payment SMS.", "📱"),
        Triple("Your Data, Your Control", "All your financial data is encrypted and stored securely. You are in full control.", "🔒")
    )

    Column(
        modifier = Modifier.fillMaxSize().padding(32.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(slides[page].third, fontSize = 64.sp)
        Spacer(Modifier.height(24.dp))
        Text(
            slides[page].first,
            fontSize = 24.sp,
            fontWeight = FontWeight.Bold,
            color = MaterialTheme.colorScheme.onBackground,
            textAlign = TextAlign.Center
        )
        Spacer(Modifier.height(12.dp))
        Text(
            slides[page].second,
            fontSize = 14.sp,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            textAlign = TextAlign.Center
        )

        Spacer(Modifier.height(48.dp))

        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            slides.indices.forEach { i ->
                Box(
                    modifier = Modifier
                        .size(if (i == page) 24.dp else 8.dp, 8.dp)
                        .then(
                            if (i == page) Modifier else Modifier
                        )
                ) {
                    Surface(
                        modifier = Modifier.fillMaxSize(),
                        shape = MaterialTheme.shapes.small,
                        color = if (i == page) MaterialTheme.colorScheme.primary
                                else MaterialTheme.colorScheme.outline.copy(alpha = 0.3f)
                    ) {}
                }
            }
        }

        Spacer(Modifier.height(32.dp))

        Button(
            onClick = {
                if (page < slides.size - 1) page++
                else onComplete()
            },
            modifier = Modifier.fillMaxWidth().height(48.dp)
        ) {
            Text(if (page < slides.size - 1) "Next" else "Get Started")
        }

        if (page < slides.size - 1) {
            Spacer(Modifier.height(8.dp))
            TextButton(onClick = onComplete) {
                Text("Skip")
            }
        }
    }
}
