package com.trackspend.app.services

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.provider.Telephony
import android.util.Log

class SmsListenerService : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action == Telephony.Sms.Intents.SMS_RECEIVED_ACTION) {
            val messages = Telephony.Sms.Intents.getMessagesFromIntent(intent)
            for (sms in messages) {
                val body = sms.messageBody ?: continue
                val sender = sms.originatingAddress ?: "Unknown"

                val result = SmsParser.parse(body, sender)
                if (result.transaction != null) {
                    Log.d("SmsListener", "Parsed: ${result.transaction}")
                    // Store parsed SMS for user review
                    PendingSmsStore.add(result)
                }
            }
        }
    }
}

object PendingSmsStore {
    private val pending = mutableListOf<SmsParseResult>()

    fun add(result: SmsParseResult) {
        pending.add(result)
    }

    fun getAll(): List<SmsParseResult> = pending.toList()

    fun remove(index: Int) {
        if (index in pending.indices) pending.removeAt(index)
    }

    fun clear() {
        pending.clear()
    }
}
