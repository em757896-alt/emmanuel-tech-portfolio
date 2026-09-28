package com.trackspend.app

import android.app.Application
import com.trackspend.app.data.remote.SupabaseClient
import com.trackspend.app.util.AppLog

class TrackSpendApp : Application() {
    lateinit var supabaseClient: SupabaseClient

    override fun onCreate() {
        super.onCreate()
        instance = this
        try {
            supabaseClient = SupabaseClient()
            AppLog.i("TrackSpendApp started, Supabase client configured")
        } catch (e: Exception) {
            AppLog.e("Failed to init Supabase", e)
        }
    }

    companion object {
        lateinit var instance: TrackSpendApp
    }
}
