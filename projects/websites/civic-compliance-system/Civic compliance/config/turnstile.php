<?php
/**
 * Cloudflare Turnstile verification helper.
 *
 * Degrades safely: if no secret key is configured, verification is skipped so
 * the login/register flow still works until the secret is added.
 */
function turnstile_configured(): bool {
    return trim((string)(TURNSTILE_SECRET_KEY ?? '')) !== '';
}

function turnstile_verify(?string $token): bool {
    $secret = trim((string)(TURNSTILE_SECRET_KEY ?? ''));

    // Not configured -> allow (same optional-degrading behaviour as the brand site).
    if ($secret === '') {
        return true;
    }

    $token = trim((string)$token);
    if ($token === '') {
        return false;
    }

    $remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret'   => $secret,
            'response' => $token,
            'remoteip' => $remote_ip,
        ]),
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return false;
    }

    $data = json_decode((string)$response, true);

    return is_array($data) && ($data['success'] ?? false) === true;
}
