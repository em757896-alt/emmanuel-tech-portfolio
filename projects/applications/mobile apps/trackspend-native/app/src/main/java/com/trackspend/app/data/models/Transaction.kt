package com.trackspend.app.data.models

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

enum class TransactionType {
    @SerialName("expense") EXPENSE,
    @SerialName("income") INCOME,
    @SerialName("loan") LOAN
}

enum class PaymentMethod {
    @SerialName("cash") CASH,
    @SerialName("mpesa") M_PESA,
    @SerialName("bank") BANK,
    @SerialName("paypal") PAYPAL,
    @SerialName("stripe") STRIPE,
    @SerialName("airtel") AIRTEL_MONEY,
    @SerialName("other") OTHER
}

enum class IncomeSource {
    @SerialName("salary") SALARY,
    @SerialName("gifts") GIFTS,
    @SerialName("business") BUSINESS,
    @SerialName("investment") INVESTMENT,
    @SerialName("other") OTHER
}

enum class LoanType {
    @SerialName("bank_loan") BANK_LOAN,
    @SerialName("asset_loan") ASSET_LOAN,
    @SerialName("from_someone") FROM_SOMEONE,
    @SerialName("other") OTHER
}

@Serializable
data class Transaction(
    val id: String = "",
    @SerialName("user_id") val userId: String = "",
    val type: TransactionType = TransactionType.EXPENSE,
    val amount: Double = 0.0,
    val currency: String = "KES",
    val category: String? = null,
    @SerialName("payment_method") val paymentMethod: PaymentMethod = PaymentMethod.CASH,
    val description: String = "",
    val location: String? = null,
    val note: String? = null,
    @SerialName("income_source") val incomeSource: IncomeSource? = null,
    @SerialName("loan_type") val loanType: LoanType? = null,
    val lender: String? = null,
    @SerialName("transaction_date") val transactionDate: String = "",
    @SerialName("created_at") val createdAt: String = ""
)
