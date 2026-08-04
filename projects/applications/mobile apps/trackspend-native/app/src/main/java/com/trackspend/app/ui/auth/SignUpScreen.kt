package com.trackspend.app.ui.auth

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.trackspend.app.data.models.Profile
import com.trackspend.app.data.remote.AuthRepository
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SignUpScreen(onSignUpSuccess: () -> Unit) {
    val authRepo = remember { AuthRepository() }
    val scope = rememberCoroutineScope()
    var fullName by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var country by remember { mutableStateOf("Kenya") }
    var currency by remember { mutableStateOf("KES") }
    var occupation by remember { mutableStateOf("") }
    var error by remember { mutableStateOf<String?>(null) }
    var success by remember { mutableStateOf<String?>(null) }
    var isLoading by remember { mutableStateOf(false) }

    val occupations = listOf("Student", "Nurse", "Farmer", "Teacher", "Engineer", "Doctor", "Business Owner", "Software Developer", "Driver", "Other")

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp)
            .verticalScroll(rememberScrollState())
    ) {
        Text(
            "Create Account",
            fontSize = 28.sp,
            fontWeight = FontWeight.Bold,
            color = MaterialTheme.colorScheme.primary
        )
        Text(
            "Join TrackSpend",
            fontSize = 14.sp,
            color = MaterialTheme.colorScheme.onSurfaceVariant,
            modifier = Modifier.padding(bottom = 24.dp)
        )

        OutlinedTextField(
            value = fullName, onValueChange = { fullName = it; error = null },
            label = { Text("Full Name") }, singleLine = true,
            modifier = Modifier.fillMaxWidth()
        )
        Spacer(Modifier.height(12.dp))

        OutlinedTextField(
            value = email, onValueChange = { email = it; error = null },
            label = { Text("Email") }, singleLine = true,
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email),
            modifier = Modifier.fillMaxWidth()
        )
        Spacer(Modifier.height(12.dp))

        OutlinedTextField(
            value = password, onValueChange = { password = it; error = null },
            label = { Text("Password") }, singleLine = true,
            visualTransformation = PasswordVisualTransformation(),
            keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
            modifier = Modifier.fillMaxWidth()
        )
        Spacer(Modifier.height(12.dp))

        OutlinedTextField(
            value = country, onValueChange = { country = it; error = null },
            label = { Text("Country") }, singleLine = true,
            modifier = Modifier.fillMaxWidth()
        )
        Spacer(Modifier.height(12.dp))

        // Currency selector
        var currencyExpanded by remember { mutableStateOf(false) }
        val currencies = listOf("KES", "USD", "EUR", "GBP", "UGX", "TZS", "RWF", "ZAR", "NGN", "GHS")
        ExposedDropdownMenuBox(
            expanded = currencyExpanded,
            onExpandedChange = { currencyExpanded = !currencyExpanded }
        ) {
            OutlinedTextField(
                value = currency,
                onValueChange = {},
                readOnly = true,
                label = { Text("Currency") },
                trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = currencyExpanded) },
                modifier = Modifier.fillMaxWidth().menuAnchor()
            )
            ExposedDropdownMenu(
                expanded = currencyExpanded,
                onDismissRequest = { currencyExpanded = false }
            ) {
                currencies.forEach { c ->
                    DropdownMenuItem(
                        text = { Text(c) },
                        onClick = { currency = c; currencyExpanded = false }
                    )
                }
            }
        }
        Spacer(Modifier.height(12.dp))

        // Occupation dropdown
        var occExpanded by remember { mutableStateOf(false) }
        ExposedDropdownMenuBox(
            expanded = occExpanded,
            onExpandedChange = { occExpanded = !occExpanded }
        ) {
            OutlinedTextField(
                value = occupation,
                onValueChange = {},
                readOnly = true,
                label = { Text("Occupation") },
                trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = occExpanded) },
                modifier = Modifier.fillMaxWidth().menuAnchor()
            )
            ExposedDropdownMenu(
                expanded = occExpanded,
                onDismissRequest = { occExpanded = false }
            ) {
                occupations.forEach { o ->
                    DropdownMenuItem(
                        text = { Text(o) },
                        onClick = { occupation = o; occExpanded = false }
                    )
                }
            }
        }
        Spacer(Modifier.height(16.dp))

        if (error != null) {
            Text(error!!, color = MaterialTheme.colorScheme.error, fontSize = 14.sp)
            Spacer(Modifier.height(8.dp))
        }
        if (success != null) {
            Text(success!!, color = MaterialTheme.colorScheme.primary, fontSize = 14.sp)
            Spacer(Modifier.height(8.dp))
        }

        Button(
            onClick = {
                if (fullName.isBlank() || email.isBlank() || password.isBlank()) {
                    error = "Please fill in all required fields"
                    return@Button
                }
                isLoading = true
                scope.launch {
                    try {
                        val profile = Profile(
                            fullName = fullName,
                            email = email,
                            country = country,
                            currency = currency,
                            occupation = occupation
                        )
                        authRepo.signUp(email, password, profile)
                        success = "Account created! Check your email to confirm."
                        error = null
                    } catch (e: Exception) {
                        error = e.message ?: "Sign up failed"
                    } finally {
                        isLoading = false
                    }
                }
            },
            modifier = Modifier.fillMaxWidth().height(48.dp),
            enabled = !isLoading
        ) {
            if (isLoading) CircularProgressIndicator(modifier = Modifier.size(20.dp))
            else Text("Create Account")
        }
    }
}
