<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

requireAdmin();

$pageTitle   = 'Settings - Admin';
$currentPage = 'settings';

$db = Database::getInstance()->getConnection();

$actionMsg  = '';
$actionType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $actionMsg  = 'Invalid security token.';
        $actionType = 'error';
    } else {
        $action = sanitizeInput($_POST['action'] ?? '');

        if ($action === 'save_settings') {
            $settings = $_POST['setting'] ?? [];
            foreach ($settings as $key => $value) {
                $key = sanitizeInput($key);
                $value = sanitizeInput($value);
                $stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = :k");
                $stmt->execute([':k'=>$key]);
                if ($stmt->fetchColumn() > 0) {
                    $db->prepare("UPDATE settings SET setting_value = :v, updated_at = NOW() WHERE setting_key = :k")
                       ->execute([':v'=>$value, ':k'=>$key]);
                } else {
                    $db->prepare("INSERT INTO settings (setting_key, setting_value, created_at) VALUES (:k, :v, NOW())")
                       ->execute([':k'=>$key, ':v'=>$value]);
                }
            }
            $actionMsg  = 'Settings saved successfully.';
            $actionType = 'success';
        } elseif ($action === 'save_email_template') {
            $template = sanitizeInput($_POST['template_name'] ?? '');
            $subject  = sanitizeInput($_POST['subject'] ?? '');
            $body     = sanitizeInput($_POST['body'] ?? '');
            if ($template && $body) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM email_templates WHERE template_name = :t");
                $stmt->execute([':t'=>$template]);
                if ($stmt->fetchColumn() > 0) {
                    $db->prepare("UPDATE email_templates SET subject=:s, body=:b, updated_at=NOW() WHERE template_name=:t")
                       ->execute([':s'=>$subject, ':b'=>$body, ':t'=>$template]);
                } else {
                    $db->prepare("INSERT INTO email_templates (template_name, subject, body, created_at) VALUES (:t,:s,:b,NOW())")
                       ->execute([':t'=>$template, ':s'=>$subject, ':b'=>$body]);
                }
                $actionMsg  = 'Email template saved.';
                $actionType = 'success';
            }
        } elseif ($action === 'test_email') {
            $testEmail = filter_var($_POST['test_email'] ?? APP_EMAIL, FILTER_SANITIZE_EMAIL);
            $subject   = 'PBO Hub - Test Email from Admin Settings';
            $body      = "This is a test email from the PBO Kenya Platform admin panel.\n\nIf you received this, SMTP is configured correctly.";
            $sent = function_exists('mail') ? @mail($testEmail, $subject, $body, 'From: ' . (SMTP_USER ?? APP_EMAIL)) : false;
            $actionMsg  = $sent ? 'Test email sent to ' . $testEmail : 'Failed to send test email. Check SMTP settings.';
            $actionType = $sent ? 'success' : 'error';
        }
    }
}

$settingsList = [];
try {
    $stmt = $db->query("SELECT * FROM settings ORDER BY setting_key");
    $settingsList = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {}

$templates = [];
try {
    $stmt = $db->query("SELECT * FROM email_templates ORDER BY template_name");
    $templates = $stmt->fetchAll();
} catch (Exception $e) {}

$currentTab = sanitizeInput($_GET['tab'] ?? 'general');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<?php include '../includes/admin-sidebar.php'; ?>

<main class="admin-main" id="adminMain">

    <header class="admin-topbar">
        <div class="topbar-left">
            <button class="topbar-menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <div class="topbar-breadcrumb">
                <a href="../dashboard.php" class="bc-item"><i class="fas fa-home"></i></a>
                <span class="bc-sep">/</span>
                <span class="bc-item active">Settings</span>
            </div>
        </div>
    </header>

    <div class="dashboard-content">

        <?php if ($actionMsg): ?>
        <div class="action-message action-<?php echo $actionType; ?>">
            <i class="fas fa-<?php echo $actionType==='success'?'check-circle':'times-circle'; ?>"></i>
            <?php echo htmlspecialchars($actionMsg); ?>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h1>System Settings</h1>
                <p>Configure platform settings, email templates and system preferences.</p>
            </div>
        </div>

        <div class="tabs-nav" style="display:flex;gap:4px;margin-bottom:24px;background:#fff;padding:6px;border-radius:10px;border:1px solid #e5e7eb">
            <?php $tabs = ['general'=>'General','email'=>'Email','features'=>'Features','security'=>'Security']; ?>
            <?php foreach($tabs as $tab=>$label): ?>
            <a href="?tab=<?php echo $tab; ?>" class="tab-btn <?php echo $currentTab===$tab?'tab-active':''; ?>" style="padding:8px 18px;border-radius:7px;font-size:0.83rem;font-weight:500;color:<?php echo $currentTab===$tab?'#fff':'#6b7280'; ?>;background:<?php echo $currentTab===$tab?'#1a3c5e':'transparent'; ?>;text-decoration:none;transition:all 0.2s"><?php echo $label; ?></a>
            <?php endforeach; ?>
        </div>

        <form method="POST">
            <?php echo generateCSRFField(); ?>
            <input type="hidden" name="action" value="save_settings">

            <?php if ($currentTab === 'general'): ?>
            <div class="table-card" style="padding:24px">
                <h3 style="margin-bottom:16px;font-size:1rem;color:#1a3c5e">General Settings</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Application Name</label>
                        <input type="text" name="setting[app_name]" value="<?php echo htmlspecialchars($settingsList['app_name']??'PBO Kenya Platform'); ?>" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Admin Email</label>
                        <input type="email" name="setting[admin_email]" value="<?php echo htmlspecialchars($settingsList['admin_email']??APP_EMAIL); ?>" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Application URL</label>
                        <input type="url" name="setting[app_url]" value="<?php echo htmlspecialchars($settingsList['app_url']??APP_URL); ?>" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Items Per Page</label>
                        <input type="number" name="setting[per_page]" value="<?php echo intval($settingsList['per_page']??20); ?>" style="width:100px;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                </div>
            </div>

            <?php elseif ($currentTab === 'email'): ?>
            <div class="table-card" style="padding:24px;margin-bottom:20px">
                <h3 style="margin-bottom:16px;font-size:1rem;color:#1a3c5e">SMTP Configuration</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">SMTP Host</label>
                        <input type="text" name="setting[smtp_host]" value="<?php echo htmlspecialchars($settingsList['smtp_host']??''); ?>" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">SMTP Port</label>
                        <input type="number" name="setting[smtp_port]" value="<?php echo htmlspecialchars($settingsList['smtp_port']??'587'); ?>" style="width:120px;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">SMTP Username</label>
                        <input type="text" name="setting[smtp_user]" value="<?php echo htmlspecialchars($settingsList['smtp_user']??''); ?>" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">SMTP Password</label>
                        <input type="password" name="setting[smtp_pass]" value="<?php echo htmlspecialchars($settingsList['smtp_pass']??''); ?>" style="width:100%;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                </div>
                <div style="margin-top:16px">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Send Test Email To</label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="email" name="test_email" value="<?php echo htmlspecialchars($settingsList['admin_email']??APP_EMAIL); ?>" style="flex:1;max-width:300px;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                        <button type="submit" name="action" value="test_email" class="btn btn-outline"><i class="fas fa-paper-plane"></i> Send Test</button>
                    </div>
                </div>
            </div>

            <div class="table-card" style="padding:24px">
                <h3 style="margin-bottom:16px;font-size:1rem;color:#1a3c5e">Email Templates</h3>
                <p style="font-size:0.82rem;color:#6b7280;margin-bottom:16px">Customize email notifications sent to users.</p>
                <?php $templateNames = ['welcome'=>'Welcome Email','report_confirmation'=>'Report Confirmation','password_reset'=>'Password Reset','incident_alert'=>'Incident Alert']; ?>
                <?php foreach($templateNames as $tpl=>$tplLabel): ?>
                <?php $existing = null;
                foreach($templates as $et) { if($et['template_name']===$tpl) { $existing=$et; break; } } ?>
                <details style="margin-bottom:12px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden">
                    <summary style="padding:12px 16px;cursor:pointer;font-weight:600;font-size:0.87rem;color:#374151;background:#f8fafc"><?php echo $tplLabel; ?></summary>
                    <div style="padding:16px;display:flex;flex-direction:column;gap:12px">
                        <div>
                            <label style="font-size:0.78rem;color:#6b7280;margin-bottom:3px;display:block">Subject</label>
                            <input type="text" name="email_subject[<?php echo $tpl; ?>]" value="<?php echo htmlspecialchars($existing['subject']??''); ?>" style="width:100%;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:7px;font-size:0.85rem">
                        </div>
                        <div>
                            <label style="font-size:0.78rem;color:#6b7280;margin-bottom:3px;display:block">Body</label>
                            <textarea name="email_body[<?php echo $tpl; ?>]" rows="4" style="width:100%;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:7px;font-size:0.85rem;font-family:inherit"><?php echo htmlspecialchars($existing['body']??''); ?></textarea>
                        </div>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>

            <?php elseif ($currentTab === 'features'): ?>
            <div class="table-card" style="padding:24px">
                <h3 style="margin-bottom:16px;font-size:1rem;color:#1a3c5e">Feature Toggles</h3>
                <div style="display:grid;gap:12px">
                    <?php $features = ['enable_chatbot'=>'Enable AI Chatbot','enable_monitoring'=>'Enable Civic Space Monitoring','enable_knowledge_hub'=>'Enable Knowledge Hub','enable_compliance_tools'=>'Enable Compliance Tools','enable_public_dashboard'=>'Enable Public Dashboard','enable_registration'=>'Enable User Registration','enable_mfa'=>'Enable Multi-Factor Authentication']; ?>
                    <?php foreach($features as $key=>$label): ?>
                    <label style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f3f4f6">
                        <input type="hidden" name="setting[<?php echo $key; ?>]" value="0">
                        <input type="checkbox" name="setting[<?php echo $key; ?>]" value="1" <?php echo ($settingsList[$key]??'1')==='1'?'checked':''; ?> style="width:18px;height:18px;accent-color:#1a3c5e">
                        <span style="font-size:0.87rem;color:#374151"><?php echo $label; ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php elseif ($currentTab === 'security'): ?>
            <div class="table-card" style="padding:24px">
                <h3 style="margin-bottom:16px;font-size:1rem;color:#1a3c5e">Security Settings</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Session Lifetime (minutes)</label>
                        <input type="number" name="setting[session_lifetime]" value="<?php echo intval($settingsList['session_lifetime']??120); ?>" style="width:120px;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Rate Limit (requests/minute)</label>
                        <input type="number" name="setting[rate_limit]" value="<?php echo intval($settingsList['rate_limit']??60); ?>" style="width:120px;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Max Login Attempts</label>
                        <input type="number" name="setting[max_login_attempts]" value="<?php echo intval($settingsList['max_login_attempts']??5); ?>" style="width:120px;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:5px">Lockout Duration (minutes)</label>
                        <input type="number" name="setting[lockout_duration]" value="<?php echo intval($settingsList['lockout_duration']??30); ?>" style="width:120px;padding:9px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:0.87rem">
                    </div>
                </div>
                <div style="margin-top:20px">
                    <label style="display:flex;align-items:center;gap:10px">
                        <input type="hidden" name="setting[maintenance_mode]" value="0">
                        <input type="checkbox" name="setting[maintenance_mode]" value="1" <?php echo ($settingsList['maintenance_mode']??'0')==='1'?'checked':''; ?> style="width:18px;height:18px;accent-color:#ef4444">
                        <span style="font-size:0.87rem;color:#374151"><strong style="color:#ef4444">Maintenance Mode</strong> — Only admins can access the site</span>
                    </label>
                </div>
            </div>
            <?php endif; ?>

            <div style="margin-top:20px">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
            </div>
        </form>

    </div>
</main>

<script>
const msg=document.querySelector('.action-message');
if(msg){setTimeout(()=>{msg.style.opacity='0';setTimeout(()=>msg.remove(),400);},4000);}
</script>
<style>
.tab-btn:hover{opacity:0.85}
</style>
</body>
</html>
