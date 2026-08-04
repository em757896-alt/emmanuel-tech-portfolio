package com.trackspend.app

import android.app.Application
import com.trackspend.app.data.remote.SupabaseClient

class TrackSpendApp : Application() {
    lateinit var supabaseClient: SupabaseClient

    override fun onCreate() {
        super.onCreate()
        instance = this
        supabaseClient = SupabaseClient()
    }

    companion object {
        lateinit var instance: TrackSpendApp
    }
}
