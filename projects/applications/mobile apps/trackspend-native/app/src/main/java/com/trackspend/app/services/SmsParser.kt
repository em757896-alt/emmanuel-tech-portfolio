package com.trackspend.app.services

import com.trackspend.app.data.models.PaymentMethod
import com.trackspend.app.data.models.Transaction
import com.trackspend.app.data.models.TransactionType
import java.text.NumberFormat
import java.util.Locale
import java.util.regex.Pattern

data class SmsParseResult(
    val transaction: Transaction?,
    val rawSms: String,
    val sender: String,
    val confidence: Float
)

object SmsParser {
    private val patterns = listOf(
        // M-Pesa received
        Regex("""(?:You have received|Received| sent you)\s*[A-Z]{0,3}\s*([\d,]+(?:\.\d{1,2})?)\s*(?:KES|USD|EUR)?\s*(?:from|by)\s*(.+?)(?:\s+on\s+\d{1,2}/\d{1,2}|\s*$)""", RegexOption.IGNORE_CASE),
        // M-Pesa sent
        Regex("""(?:You have sent|sent|paid)\s*[A-Z]{0,3}\s*([\d,]+(?:\.\d{1,2})?)\s*(?:KES|USD|EUR)?\s*(?:to|for)\s*(.+?)(?:\s+on\s+\d{1,2}/\d{1,2}|\s*$)""", RegexOption.IGNORE_CASE),
        // M-Pesa withdrawal
        Regex("""(?:Withdraw|Withdrawal)\s*[A-Z]{0,3}\s*([\d,]+(?:\.\d{1,2})?)\s*(?:KES|USD|EUR)?\s*(?:from|at)\s*(.+?)(?:\s+on\s+\d{1,2}/\d{1,2}|\s*$)""", RegexOption.IGNORE_CASE),
        // Bank debit
        Regex("""(?:debited|withdrawn|payment of)\s*[A-Z]{0,3}\s*([\d,]+(?:\.\d{1,2})?)\s*(?:KES|USD|EUR)?\s*(?:from|for|to)\s*(.+?)(?:\s+on\s+\d{1,2}/\d{1,2}|\s*$)""", RegexOption.IGNORE_CASE),
        // Bank credit
        Regex("""(?:credited|deposited|received)\s*[A-Z]{0,3}\s*([\d,]+(?:\.\d{1,2})?)\s*(?:KES|USD|EUR)?\s*(?:to|from|for)\s*(.+?)(?:\s+on\s+\d{1,2}/\d{1,2}|\s*$)""", RegexOption.IGNORE_CASE)
    )

    private val incomeKeywords = listOf("received", "credited", "deposited", "sent you", "salary", "payment from")
    private val expenseKeywords = listOf("sent", "paid", "debited", "withdrawn", "withdrawal", "purchase", "payment to")

    fun parse(smsBody: String, sender: String): SmsParseResult {
        var bestAmount: Double? = null
        var bestDescription = ""
        var bestType: TransactionType = TransactionType.EXPENSE
        var bestMethod = PaymentMethod.OTHER

        for (pattern in patterns) {
            val match = pattern.find(smsBody)
            if (match != null) {
                val amountStr = match.groupValues[1].replace(",", "")
                val description = match.groupValues.getOrElse(2) { "Transaction" }

                val amount = amountStr.toDoubleOrNull()
                if (amount != null && (bestAmount == null || amount > bestAmount)) {
                    bestAmount = amount

                    val lowerBody = smsBody.lowercase()
                    val isIncome = incomeKeywords.any { lowerBody.contains(it) }
                    val isExpense = expenseKeywords.any { lowerBody.contains(it) }

                    bestType = when {
                        isIncome -> TransactionType.INCOME
                        isExpense -> TransactionType.EXPENSE
                        else -> TransactionType.EXPENSE
                    }

                    bestDescription = when {
                        sender.contains("M-PESA", ignoreCase = true) || smsBody.contains("M-PESA", ignoreCase = true) -> {
                            bestMethod = PaymentMethod.M_PESA
                            "M-Pesa: $description"
                        }
                        sender.contains("BANK", ignoreCase = true) || smsBody.any { bank -> description.contains("bank") } -> {
                            bestMethod = PaymentMethod.BANK
                            description
                        }
                        sender.contains("PAYPAL", ignoreCase = true) -> {
                            bestMethod = PaymentMethod.PAYPAL
                            description
                        }
                        sender.contains("STRIPE", ignoreCase = true) -> {
                            bestMethod = PaymentMethod.STRIPE
                            description
                        }
                        sender.contains("AIRTEL", ignoreCase = true) -> {
                            bestMethod = PaymentMethod.AIRTEL_MONEY
                            description
                        }
                        else -> {
                            bestMethod = PaymentMethod.OTHER
                            description
                        }
                    }
                }
            }
        }

        if (bestAmount == null) return SmsParseResult(null, smsBody, sender, 0f)

        val transaction = Transaction(
            amount = bestAmount,
            type = bestType,
            paymentMethod = bestMethod,
            description = bestDescription.trim(),
            transactionDate = java.text.SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss", Locale.getDefault()).format(java.util.Date())
        )

        return SmsParseResult(transaction, smsBody, sender, 0.85f)
    }
}
