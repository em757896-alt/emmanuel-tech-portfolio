package com.trackspend.app.data.remote

import com.trackspend.app.TrackSpendApp
import com.trackspend.app.data.models.Transaction
import com.trackspend.app.data.models.TransactionType
import io.github.jan.supabase.postgrest.query.Order
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import kotlinx.serialization.json.Json
import kotlinx.serialization.encodeToString
import kotlinx.serialization.modules.SerializersModule

class TransactionRepository {
    private val client get() = TrackSpendApp.instance.supabaseClient

    private val json = Json {
        ignoreUnknownKeys = true
        serializersModule = SerializersModule { }
    }

    suspend fun getAll(): List<Transaction> = withContext(Dispatchers.IO) {
        val userId = client.auth.currentUserOrNull()?.id ?: return@withContext emptyList()
        client.postgrest["transactions"]
            .select { this.order("transaction_date", Order.DESCENDING) }
            .decodeList<Transaction>()
    }

    suspend fun getRecent(pageSize: Long = 5): List<Transaction> = withContext(Dispatchers.IO) {
        val userId = client.auth.currentUserOrNull()?.id ?: return@withContext emptyList()
        client.postgrest["transactions"]
            .select {
                this.order("transaction_date", Order.DESCENDING)
                this.limit(pageSize)
            }
            .decodeList<Transaction>()
    }

    suspend fun getByType(type: TransactionType): List<Transaction> = withContext(Dispatchers.IO) {
        val userId = client.auth.currentUserOrNull()?.id ?: return@withContext emptyList()
        client.postgrest["transactions"]
            .select {
                this.order("transaction_date", Order.DESCENDING)
                this.filter { eq("type", type.name.lowercase()) }
            }
            .decodeList<Transaction>()
    }

    suspend fun getTotalForPeriod(startDate: String, endDate: String, type: TransactionType? = null): Double = withContext(Dispatchers.IO) {
        val userId = client.auth.currentUserOrNull()?.id ?: return@withContext 0.0
        val query = client.postgrest["transactions"]
            .select {
                this.filter {
                    gte("transaction_date", startDate)
                    lte("transaction_date", endDate)
                    if (type != null) eq("type", type.name.lowercase())
                }
            }
        val transactions = query.decodeList<Transaction>()
        transactions.sumOf { it.amount }
    }

    suspend fun create(transaction: Transaction): Transaction = withContext(Dispatchers.IO) {
        val userId = client.auth.currentUserOrNull()?.id ?: throw Exception("Not authenticated")
        val body = json.encodeToString(transaction.copy(userId = userId))
        client.postgrest["transactions"].insert(body) { }.decodeSingle<Transaction>()
    }

    suspend fun update(transaction: Transaction) = withContext(Dispatchers.IO) {
        val body = json.encodeToString(transaction)
        client.postgrest["transactions"].update(
            body,
            { this.filter { eq("id", transaction.id) } }
        )
    }

    suspend fun delete(id: String) = withContext(Dispatchers.IO) {
        client.postgrest["transactions"].delete {
            this.filter { eq("id", id) }
        }
    }
}
