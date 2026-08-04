package com.trackspend.app

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.Surface
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import com.trackspend.app.data.remote.AuthRepository
import com.trackspend.app.ui.auth.LoginScreen
import com.trackspend.app.ui.auth.SignUpScreen
import com.trackspend.app.ui.components.MainAppScreen
import com.trackspend.app.ui.navigation.NavRoutes
import com.trackspend.app.ui.onboarding.OnboardingScreen
import com.trackspend.app.ui.theme.TrackSpendTheme
import kotlinx.coroutines.launch

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val authRepo = AuthRepository()
        setContent {
            TrackSpendTheme {
                Surface(modifier = Modifier.fillMaxSize()) {
                    val navController = rememberNavController()
                    var isLoggedIn by remember { mutableStateOf<Boolean?>(null) }
                    var hasOnboarded by remember { mutableStateOf(true) }
                    val scope = rememberCoroutineScope()

                    LaunchedEffect(Unit) {
                        try {
                            isLoggedIn = authRepo.isLoggedIn()
                            if (isLoggedIn == true) {
                                hasOnboarded = authRepo.hasOnboarded()
                            }
                        } catch (_: Exception) {
                            isLoggedIn = false
                        }
                    }

                    when (isLoggedIn) {
                        null -> { /* loading */ }
                        false -> {
                            NavHost(navController, NavRoutes.LOGIN) {
                                composable(NavRoutes.LOGIN) {
                                    LoginScreen(
                                        onLoginSuccess = { isLoggedIn = true },
                                        onNavigateToSignUp = { navController.navigate(NavRoutes.SIGNUP) }
                                    )
                                }
                                composable(NavRoutes.SIGNUP) {
                                    SignUpScreen(
                                        onSignUpSuccess = { navController.popBackStack() }
                                    )
                                }
                            }
                        }
                        true -> {
                            if (!hasOnboarded) {
                                OnboardingScreen(
                                    onComplete = {
                                        scope.launch { authRepo.setOnboardingDone() }
                                        hasOnboarded = true
                                    }
                                )
                            } else {
                                MainAppScreen(
                                    onLogout = {
                                        scope.launch { authRepo.logout() }
                                        isLoggedIn = false
                                    }
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}
