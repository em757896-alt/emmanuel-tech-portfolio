param(
    [Parameter(Mandatory = $true)][string]$Passphrase,
    [Parameter(Mandatory = $true)][string]$EncryptedValue
)

$ErrorActionPreference = "Stop"
$blob = [Convert]::FromBase64String($EncryptedValue)
if ($blob.Length -lt 48) { throw "Invalid encrypted value" }

$salt = $blob[0..15]
$iv = $blob[16..31]
$cipher = $blob[32..($blob.Length - 1)]

$derived = New-Object System.Security.Cryptography.Rfc2898DeriveBytes($Passphrase, $salt, 100000, [System.Security.Cryptography.HashAlgorithmName]::SHA256)
$key = $derived.GetBytes(32)
$aes = [System.Security.Cryptography.Aes]::Create()
$aes.Key = $key
$aes.IV = $iv
$aes.Mode = [System.Security.Cryptography.CipherMode]::CBC
$aes.Padding = [System.Security.Cryptography.PaddingMode]::PKCS7
$dec = $aes.CreateDecryptor()
$plain = $dec.TransformFinalBlock($cipher, 0, $cipher.Length)
[System.Text.Encoding]::UTF8.GetString($plain)
