package com.trackspend.app.ui.transactions

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.compose.ui.window.Dialog
import com.trackspend.app.data.models.*
import com.trackspend.app.data.remote.TransactionRepository
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AddTransactionDialog(onDismiss: () -> Unit) {
    val repo = remember { TransactionRepository() }
    val scope = rememberCoroutineScope()
    var type by remember { mutableStateOf(TransactionType.EXPENSE) }
    var amount by remember { mutableStateOf("") }
    var category by remember { mutableStateOf("") }
    var description by remember { mutableStateOf("") }
    var paymentMethod by remember { mutableStateOf(PaymentMethod.CASH) }
    var incomeSource by remember { mutableStateOf(IncomeSource.SALARY) }
    var loanType by remember { mutableStateOf(LoanType.BANK_LOAN) }
    var lender by remember { mutableStateOf("") }
    var location by remember { mutableStateOf("") }
    var note by remember { mutableStateOf("") }
    var error by remember { mutableStateOf<String?>(null) }

    val categories = listOf("Food", "Transport", "Bills", "Rent", "Entertainment", "Shopping", "Health", "Education", "Other")

    Dialog(onDismissRequest = onDismiss) {
        Card(
            modifier = Modifier
                .fillMaxWidth()
                .heightIn(max = 600.dp)
                .verticalScroll(rememberScrollState())
        ) {
            Column(modifier = Modifier.padding(16.dp)) {
                Text("Add Transaction", style = MaterialTheme.typography.titleLarge)

                Spacer(Modifier.height(12.dp))

                // Type selector
                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    TransactionType.entries.forEach { t ->
                        FilterChip(
                            selected = type == t,
                            onClick = { type = t },
                            label = { Text(t.name.lowercase().replaceFirstChar { it.uppercase() }) }
                        )
                    }
                }

                Spacer(Modifier.height(12.dp))

                OutlinedTextField(
                    value = amount,
                    onValueChange = { amount = it; error = null },
                    label = { Text("Amount") },
                    singleLine = true,
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
                    modifier = Modifier.fillMaxWidth()
                )

                Spacer(Modifier.height(8.dp))

                OutlinedTextField(
                    value = description,
                    onValueChange = { description = it },
                    label = { Text("Description") },
                    singleLine = true,
                    modifier = Modifier.fillMaxWidth()
                )

                Spacer(Modifier.height(8.dp))

                // Category only for expense
                if (type == TransactionType.EXPENSE) {
                    var expanded by remember { mutableStateOf(false) }
                    ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                        OutlinedTextField(
                            value = category,
                            onValueChange = {},
                            readOnly = true,
                            label = { Text("Category") },
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded) },
                            modifier = Modifier.fillMaxWidth().menuAnchor()
                        )
                        ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                            categories.forEach { c ->
                                DropdownMenuItem(text = { Text(c) }, onClick = { category = c; expanded = false })
                            }
                        }
                    }
                    Spacer(Modifier.height(8.dp))
                }

                // Income source
                if (type == TransactionType.INCOME) {
                    var expanded by remember { mutableStateOf(false) }
                    ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                        OutlinedTextField(
                            value = incomeSource.name.lowercase().replaceFirstChar { it.uppercase() },
                            onValueChange = {},
                            readOnly = true,
                            label = { Text("From whom") },
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded) },
                            modifier = Modifier.fillMaxWidth().menuAnchor()
                        )
                        ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                            IncomeSource.entries.forEach { s ->
                                DropdownMenuItem(
                                    text = { Text(s.name.lowercase().replaceFirstChar { it.uppercase() }) },
                                    onClick = { incomeSource = s; expanded = false }
                                )
                            }
                        }
                    }
                    Spacer(Modifier.height(8.dp))
                }

                // Loan fields
                if (type == TransactionType.LOAN) {
                    var expanded by remember { mutableStateOf(false) }
                    ExposedDropdownMenuBox(expanded = expanded, onExpandedChange = { expanded = !expanded }) {
                        OutlinedTextField(
                            value = loanType.name.lowercase().replace("_", " ").replaceFirstChar { it.uppercase() },
                            onValueChange = {},
                            readOnly = true,
                            label = { Text("Loan Type") },
                            trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded) },
                            modifier = Modifier.fillMaxWidth().menuAnchor()
                        )
                        ExposedDropdownMenu(expanded = expanded, onDismissRequest = { expanded = false }) {
                            LoanType.entries.forEach { l ->
                                DropdownMenuItem(
                                    text = { Text(l.name.lowercase().replace("_", " ").replaceFirstChar { it.uppercase() }) },
                                    onClick = { loanType = l; expanded = false }
                                )
                            }
                        }
                    }
                    Spacer(Modifier.height(8.dp))
                    OutlinedTextField(
                        value = lender,
                        onValueChange = { lender = it },
                        label = { Text("Lender") },
                        singleLine = true,
                        modifier = Modifier.fillMaxWidth()
                    )
                    Spacer(Modifier.height(8.dp))
                }

                OutlinedTextField(
                    value = location, onValueChange = { location = it },
                    label = { Text("Location (optional)") },
                    singleLine = true, modifier = Modifier.fillMaxWidth()
                )
                Spacer(Modifier.height(8.dp))

                OutlinedTextField(
                    value = note, onValueChange = { note = it },
                    label = { Text("Note (optional)") },
                    modifier = Modifier.fillMaxWidth()
                )

                Spacer(Modifier.height(12.dp))

                if (error != null) {
                    Text(error!!, color = MaterialTheme.colorScheme.error)
                    Spacer(Modifier.height(8.dp))
                }

                Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    OutlinedButton(onClick = onDismiss, modifier = Modifier.weight(1f)) {
                        Text("Cancel")
                    }
                    Button(onClick = {
                        val amt = amount.toDoubleOrNull()
                        if (amt == null || amt <= 0) {
                            error = "Enter a valid amount"
                            return@Button
                        }
                        if (description.isBlank()) {
                            error = "Enter a description"
                            return@Button
                        }
                        scope.launch {
                            try {
                                val tx = Transaction(
                                    type = type,
                                    amount = amt,
                                    currency = "KES",
                                    category = category.ifBlank { null },
                                    description = description,
                                    paymentMethod = paymentMethod,
                                    incomeSource = if (type == TransactionType.INCOME) incomeSource else null,
                                    loanType = if (type == TransactionType.LOAN) loanType else null,
                                    lender = if (type == TransactionType.LOAN) lender.ifBlank { null } else null,
                                    location = location.ifBlank { null },
                                    note = note.ifBlank { null },
                                    transactionDate = SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss", Locale.getDefault()).format(Date())
                                )
                                repo.create(tx)
                                onDismiss()
                            } catch (e: Exception) {
                                error = e.message ?: "Failed to save"
                            }
                        }
                    }, modifier = Modifier.weight(1f)) {
                        Text("Save")
                    }
                }
            }
        }
    }
}
