<h1 align="center">TrackSpend Native</h1>

<p align="center">
  <strong>A native Android expense tracker that reads your SMS payment notifications and files them for you.</strong>
  <br />
  Kotlin · Jetpack Compose · Supabase · biometric lock
</p>

<p align="center">
  <a href="https://github.com/em757896-alt/emmanuel-tech-portfolio/stargazers"><img src="https://img.shields.io/github/stars/em757896-alt/emmanuel-tech-portfolio?style=for-the-badge&logo=github&color=ffd166" alt="Stars" /></a>
  <a href="https://github.com/em757896-alt/emmanuel-tech-portfolio/releases"><img src="https://img.shields.io/github/v/release/em757896-alt/emmanuel-tech-portfolio?style=for-the-badge&color=06d6a0" alt="Release" /></a>
  <a href="../../../LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue?style=for-the-badge&color=118ab2" alt="License" /></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Android-3DDC84?style=for-the-badge&logo=android&logoColor=white" alt="Android" />
  <img src="https://img.shields.io/badge/Kotlin-7F52FF?style=for-the-badge&logo=kotlin&logoColor=white" alt="Kotlin" />
  <img src="https://img.shields.io/badge/Jetpack%20Compose-4285F4?style=for-the-badge" alt="Jetpack Compose" />
  <img src="https://img.shields.io/badge/Supabase-3ECF8E?style=for-the-badge&logo=supabase" alt="Supabase" />
</p>

---

## What it does

Most expense trackers ask you to type every transaction in. TrackSpend reads the SMS
notification your bank or M-Pesa already sends you, parses the amount and the merchant, and
files the expense automatically.

| Capability | Details |
|------------|---------|
| **SMS parsing** | `BroadcastReceiver` intercepts payment SMS and extracts amount, merchant and reference |
| **Auto-categorisation** | Merchant patterns map to categories (transport, food, utilities, transfers) |
| **Sync** | Writes to the same Supabase backend as the TrackSpend PWA |
| **Offline-ready** | Local persistence first, sync when connectivity returns |
| **App lock** | Fingerprint / PIN / biometric authentication on launch |
| **Migrations** | `migration-v2.1.1.sql` keeps the schema versioned and reproducible |

## Permissions

The app declares an SMS `BroadcastReceiver`, which requires the **SMS permission** to read
payment notifications. Read [Android's SMS permission guidance](https://developer.android.com/permissions-and-groups/permissions)
for why this is a high-sensitivity permission and how to handle it responsibly.

## Build

Requires the Android SDK and a JDK 17.

```bash
git clone https://github.com/em757896-alt/emmanuel-tech-portfolio.git
cd "emmanuel-tech-portfolio/projects/applications/mobile apps/trackspend-native"

# point the SDK location at your machine (git-ignored)
# local.properties -> sdk.dir=/path/to/Android/sdk

./gradlew assembleDebug
# APK: app/build/outputs/apk/debug/app-debug.apk
```

The Supabase URL and anon key are public by design and access is enforced with **RLS**. The
service-role key is server-only and is never bundled into the app.

## Status

Experimental / in progress. The PWA counterpart is the more complete surface today.

## License

MIT — see [LICENSE](../../../LICENSE).
