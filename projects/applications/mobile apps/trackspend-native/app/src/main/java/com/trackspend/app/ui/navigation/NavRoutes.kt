package com.trackspend.app.ui.navigation

object NavRoutes {
    const val AUTH = "auth"
    const val LOGIN = "auth/login"
    const val SIGNUP = "auth/signup"
    const val FORGOT_PASSWORD = "auth/forgot"
    const val DASHBOARD = "dashboard"
    const val TRANSACTIONS = "transactions"
    const val ADD_TRANSACTION = "add_transaction"
    const val TRANSACTION_DETAIL = "transaction_detail/{id}"
    const val REPORTS = "reports"
    const val BUDGET = "budget"
    const val CATEGORIES = "categories"
    const val SMS_DETECTION = "sms_detection"
    const val PROFILE = "profile"
    const val SETTINGS = "settings"
    const val ONBOARDING = "onboarding"

    fun transactionDetail(id: String) = "transaction_detail/$id"
}
