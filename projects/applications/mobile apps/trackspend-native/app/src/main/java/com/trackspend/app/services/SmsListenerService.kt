package com.trackspend.app.services

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.provider.Telephony
import com.trackspend.app.util.AppLog

class SmsListenerService : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action == Telephony.Sms.Intents.SMS_RECEIVED_ACTION) {
            AppLog.i("SMS received broadcast")
            val messages = Telephony.Sms.Intents.getMessagesFromIntent(intent)
            for (sms in messages) {
                val body = sms.messageBody ?: continue
                val sender = sms.originatingAddress ?: "Unknown"

                val result = SmsParser.parse(body, sender)
                if (result.transaction != null) {
                    AppLog.i("Parsed SMS from $sender -> amount=${result.transaction.amount}, desc=${result.transaction.description}")
                    PendingSmsStore.add(result)
                } else {
                    AppLog.w("SMS from $sender not recognized as a transaction")
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
