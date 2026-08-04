package com.trackspend.app.services

object AutoCategorizer {
    private val keywordMap = mapOf(
        "food" to listOf("restaurant", "cafe", "groceries", "supermarket", "kFC", "mcdonald", "pizza", "burger", "lunch", "dinner", "food", "uber eats", "glovo", "jumbo", "naivas", "carrefour", "quickmart"),
        "transport" to listOf("uber", "bolt", "taxi", "bus", "train", "fuel", "petrol", "diesel", "parking", "toll", "fare", "matatu", "airtime", "data", "safaricom", "airtel"),
        "bills" to listOf("electricity", "water", "gas", "kplc", "nairobi water", "sewer", "internet", "wifi", "subscription", "netflix", "spotify", "dstv", "gotv", "zuku"),
        "rent" to listOf("rent", "lease", "deposit", "landlord", "tenancy", "apartment", "house"),
        "entertainment" to listOf("movie", "cinema", "game", "concert", "club", "bar", "drinks", "betting", "sportpesa", "betika", "shabiki", "game", "steam", "playstation"),
        "shopping" to listOf("shop", "store", "clothes", "shoes", "electronics", "phone", "amazon", "jiji", "kilimall", "mall", "supermarket"),
        "health" to listOf("hospital", "clinic", "doctor", "pharmacy", "medicine", "drug", "insurance", "nhif", "dentist", "optics"),
        "education" to listOf("school", "college", "university", "tuition", "fee", "course", "training", "books", "exams", "helb", "bursary"),
        "salary" to listOf("salary", "wage", "pay", "income", "earnings", "remuneration", "stipend"),
        "gifts" to listOf("gift", "donation", "present", "birthday", "thanks"),
        "business" to listOf("business", "profit", "sale", "revenue", "client", "invoice", "consultation", "contract"),
        "investment" to listOf("investment", "dividend", "interest", "stock", "bond", "crypto", "bitcoin", "forex", "mature", "sacco"),
        "bank_loan" to listOf("bank loan", "personal loan", "loan disbursed", "loan approved", "kcb", "equity", "cooperative bank", "stanbic", "absa"),
        "asset_loan" to listOf("asset", "car loan", "mortgage", "home loan"),
        "from_someone" to listOf("from", "sent you")
    )

    fun categorize(description: String): String {
        val lower = description.lowercase()
        for ((category, keywords) in keywordMap) {
            if (keywords.any { lower.contains(it) }) return category
        }
        return "Other"
    }
}
