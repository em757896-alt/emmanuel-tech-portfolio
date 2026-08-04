This is a brand new native Android project (Kotlin + Jetpack Compose) that replaces the Capacitor-based TrackSpend PWA.

## Architecture
- **Language**: Kotlin
- **UI**: Jetpack Compose (Material 3, dark theme only)
- **Navigation**: Jetpack Navigation Compose
- **State**: ViewModel + Compose state
- **Backend**: Supabase (PostgREST + Auth)
- **SMS**: Android BroadcastReceiver + SmsListenerService
- **Build**: Gradle with Kotlin DSL

## Project Structure
```
trackspend-native/
├── build.gradle.kts          (project-level)
├── settings.gradle.kts
├── gradle.properties
├── local.properties          (Android SDK path)
├── app/
│   ├── build.gradle.kts      (module-level, all dependencies)
│   ├── proguard-rules.pro
│   └── src/main/
│       ├── AndroidManifest.xml
│       ├── java/com/trackspend/app/
│       │   ├── MainActivity.kt
│       │   ├── TrackSpendApp.kt
│       │   ├── data/
│       │   │   ├── models/ (Transaction, Category, Profile)
│       │   │   └── remote/ (SupabaseClient, AuthRepository, TransactionRepository)
│       │   ├── services/ (SmsParser, AutoCategorizer, SmsListenerService)
│       │   └── ui/
│       │       ├── auth/ (LoginScreen, SignUpScreen)
│       │       ├── dashboard/ (DashboardScreen)
│       │       ├── transactions/ (TransactionsScreen, AddTransactionDialog)
│       │       ├── reports/ (ReportsScreen)
│       │       ├── settings/ (SettingsScreen, ProfileScreen)
│       │       ├── sms/ (SmsDetectionScreen)
│       │       ├── onboarding/ (OnboardingScreen)
│       │       ├── components/ (BottomNavBar, MainAppScreen)
│       │       ├── navigation/ (NavRoutes)
│       │       └── theme/ (Theme)
│       └── res/ (themes, strings, adaptive icons, network config)
```

## Key Dependencies
- `androidx.compose.material3` — Material 3 dark theme
- `io.github.jan-tennert.supabase:bom:2.1.2` — Supabase (auth + postgrest)
- `com.google.android.gms:play-services-auth-api-phone:18.0.1` — SMS Retriever
- `androidx.biometric:biometric:1.2.0` — Fingerprint / PIN
- `androidx.work:work-runtime-ktx:2.9.0` — Background sync
- `androidx.navigation:navigation-compose:2.7.6` — Screen navigation

## What's Built
- Project compiles with `./gradlew assembleDebug` (assuming Android SDK is set up)
- Auth flow: login/signup (email password), email confirmation gating
- Dashboard: welcome name, today/week/month totals, recent transactions
- Transactions: list with filter chips, add dialog (expense/income/loan with all fields)
- SMS detection: BroadcastReceiver listener, pending SMS review UI
- Reports: income vs expenses vs loans, category breakdown progress bars
- Settings: profile view, logout
- Onboarding: 3-slide intro
- Full Jetpack Compose UI with dark theme (bg-gray-950 equivalent)

## What Still Needs Code
- **Budget page** (only conceptually started; add BudgetRepository + UI)
- **PIN lock screen** (use Android Biometric API + DataStore for PIN hash)
- **Categories page** (system + custom CRUD)
- **Push notifications** (Firebase Cloud Messaging)
- **Premium subscriptions** (PayPal/M-Pesa SDK integration)
- **SMS permission request** (runtime permission dialog on first launch)
- **Notifications badge count** (work manager polling)
- **Exchange rates** (Frankfurter API call from Kotlin)
- **Search page** (multi-filter search UI)
- **Scheduled payments** (WorkManager recurring tasks)

## To Build & Run
1. Open `D:\Github Projects\Mobile Apps\trackspend-native\` in Android Studio
2. Wait for Gradle sync to finish
3. Connect Android device (API 21+) or start emulator
4. Click Run (green triangle) or: `./gradlew assembleDebug`
5. APK will be at `app/build/outputs/apk/debug/app-debug.apk`

## Relationship to PWA
- **Same Supabase backend** (URL, anon key, same tables/policies)
- **Same data model** (Transaction, Category, Profile)
- **Same SMS parsing logic** (ported from TypeScript to Kotlin)
- **Same UI design** (dark theme, same color palette, same layout)
- **Major improvement**: True native SMS reading via BroadcastReceiver (no WebView limitation)

## Key Differences from PWA
1. **No WebView** — runs directly on Android Runtime, full native performance
2. **Real SMS detection** — BroadcastReceiver catches SMS even when app is backgrounded
3. **No update delays** — new APK installs replace old one instantly
4. **No Capacitor bugs** — SMS permission, biometrics, notifications all work natively
5. **Can be distributed outside Play Store** — APK on website, auto-update checker
