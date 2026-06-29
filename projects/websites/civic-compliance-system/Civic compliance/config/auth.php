<?php
/**
 * Authentication & Authorization Handler
 * PBO Compliance Platform - CRECO Kenya
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

class Auth {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Register a new user
     */
    public function register(array $data): array {
        // Validate required fields
        $required = ['full_name', 'email', 'password', 'confirm_password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field '$field' is required"];
            }
        }
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }
        
        // Validate password match
        if ($data['password'] !== $data['confirm_password']) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }
        
        // Password strength
        if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters'];
        }
        
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $data['password'])) {
            return ['success' => false, 'message' => 'Password must contain uppercase, lowercase, number, and special character'];
        }
        
        // Check if email exists
        $existing = $this->db->fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $data['email']]);
        if ($existing) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Consent check
        if (empty($data['consent'])) {
            return ['success' => false, 'message' => 'You must agree to the terms and privacy policy'];
        }
        
        try {
            $userId = $this->db->insert('users', [
                'uuid'              => generateUUID(),
                'full_name'         => sanitize($data['full_name']),
                'email'             => strtolower(trim($data['email'])),
                'phone'             => sanitize($data['phone'] ?? ''),
                'password_hash'     => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
                'role'              => $data['role'] ?? 'pbo_user',
                'organization_name' => sanitize($data['organization_name'] ?? ''),
                'county'            => sanitize($data['county'] ?? ''),
                'consent_given'     => 1,
                'consent_date'      => date('Y-m-d H:i:s'),
            ]);
            
            // Auto-login after registration
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
            if ($user) {
                $this->logAudit(null, 'USER_REGISTERED', 'users', $userId);
                $_SESSION['welcome_new'] = true;
                return $this->createSession($user, false);
            }
            
            return [
                'success'  => true,
                'message'  => 'Registration successful. Please log in.',
                'user_id'  => $userId
            ];
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Login user
     */
    public function login(string $email, string $password, bool $remember = false): array {
        $email = strtolower(trim($email));
        
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE email = :email AND is_active = 1",
            ['email' => $email]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $minutes = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Account locked. Try again in $minutes minutes."];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $attempts = $user['login_attempts'] + 1;
            $lockUntil = $attempts >= MAX_LOGIN_ATTEMPTS ? 
                date('Y-m-d H:i:s', time() + LOCKOUT_DURATION) : null;
            
            $this->db->update('users',
                ['login_attempts' => $attempts, 'locked_until' => $lockUntil],
                'id = :id',
                ['id' => $user['id']]
            );
            
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Check MFA
        if ($user['mfa_enabled']) {
            $_SESSION['mfa_pending_user'] = $user['id'];
            return ['success' => true, 'mfa_required' => true, 'message' => 'MFA verification required'];
        }
        
        return $this->createSession($user, $remember);
    }
    
    /**
     * Create user session
     */
    private function createSession(array $user, bool $remember = false): array {
        // Reset login attempts
        $this->db->update('users',
            ['login_attempts' => 0, 'locked_until' => null, 'last_login' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $user['id']]
        );
        
        // Create session token
        $token = bin2hex(random_bytes(32));
        $expiry = $remember ? date('Y-m-d H:i:s', time() + 604800) : date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        $this->db->insert('sessions', [
            'user_id'       => $user['id'],
            'session_token' => $token,
            'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'expires_at'    => $expiry,
        ]);
        
        // Set session vars
        session_regenerate_id(true);
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['user_uuid']   = $user['uuid'];
        $_SESSION['user_role']   = $user['role'];
        $_SESSION['user_name']   = $user['full_name'];
        $_SESSION['user_email']  = $user['email'];
        $_SESSION['auth_token']  = $token;
        $_SESSION['logged_in']   = true;
        $_SESSION['welcome_back'] = true;
        
        if ($remember) {
            setcookie('auth_token', $token, time() + 604800, '/', '', true, true);
        }
        
        $this->logAudit($user['id'], 'USER_LOGIN', 'users', $user['id']);
        
        $redirectUrl = match($user['role']) {
            'super_admin', 'admin', 'moderator' => '/admin/',
            default => '/dashboard.php'
        };
        
        return [
            'success'      => true,
            'message'      => 'Login successful',
            'role'         => $user['role'],
            'redirect_url' => $redirectUrl
        ];
    }
    
    /**
     * Logout user
     */
    public function logout(): void {
        if (isset($_SESSION['auth_token'])) {
            $this->db->query(
                "UPDATE sessions SET is_active = 0 WHERE session_token = :token",
                ['token' => $_SESSION['auth_token']]
            );
        }
        
        $this->logAudit($_SESSION['user_id'] ?? null, 'USER_LOGOUT', 'users', $_SESSION['user_id'] ?? null);
        
        session_destroy();
        setcookie('auth_token', '', time() - 3600, '/', '', true, true);
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Check user has required role
     */
    public function hasRole(string|array $roles): bool {
        if (!$this->isAuthenticated()) return false;
        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($_SESSION['user_role'], $roles);
    }
    
    /**
     * Require authentication
     */
    public function requireAuth(string $redirect = '/auth/login.php'): void {
        if (!$this->isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header("Location: $redirect");
            exit();
        }
    }
    
    /**
     * Require specific role
     */
    public function requireRole(string|array $roles, string $redirect = '/auth/login.php'): void {
        $this->requireAuth($redirect);
        if (!$this->hasRole($roles)) {
            http_response_code(403);
            include ROOT_PATH . '/errors/403.php';
            exit();
        }
    }

    /**
     * Require admin access
     */
    public function requireAdmin(): void {
        $this->requireAuth();
        if (!$this->hasRole(['super_admin', 'admin'])) {
            http_response_code(403);
            include ROOT_PATH . '/errors/403.php';
            exit();
        }
    }
    
    /**
     * Get current user
     */
    public function currentUser(): ?array {
        if (!$this->isAuthenticated()) return null;
        return $this->db->fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $_SESSION['user_id']]);
    }
    
    /**
     * Log audit trail
     */
    public function logAudit(?int $userId, string $action, string $module = '', ?int $recordId = null): void {
        try {
            $this->db->insert('audit_logs', [
                'user_id'    => $userId,
                'action'     => $action,
                'module'     => $module,
                'record_id'  => $recordId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ]);
        } catch (Exception $e) {
            error_log("Audit log error: " . $e->getMessage());
        }
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRF(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCSRF(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Global helper functions
function requireAdmin(): void {
    $auth = new Auth();
    $auth->requireAdmin();
}