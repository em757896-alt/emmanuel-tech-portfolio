<?php
require __DIR__.'/bootstrap.php';
$count=(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if($count>0){http_response_code(403);exit('Setup is already complete. Remove setup.php from the server.');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $name=trim($_POST['name']??''); $username=trim($_POST['username']??''); $password=(string)($_POST['password']??'');
 if($name===''||$username===''||strlen($password)<12){$error='Enter your name, username and a password of at least 12 characters.';}
 else{
  $q=$pdo->prepare("INSERT INTO users(full_name,username,password_hash,role) VALUES(?,?,?,'admin')");
  $q->execute([$name,$username,password_hash($password,PASSWORD_DEFAULT)]);
  audit($pdo,'initial_admin_created','user',(int)$pdo->lastInsertId());
  header('Location: index.php?page=login');exit;
 }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Secure setup · Elevate Retail POS</title><link rel="stylesheet" href="assets/app.css"></head><body class="auth-shell"><main class="auth-card"><div class="brand-mark">ER</div><p class="eyebrow">FIRST-TIME CONFIGURATION</p><h1>Create store administrator</h1><p class="muted">This one-time setup creates your first administrator account.</p><?php if($error):?><div class="alert danger"><?=h($error)?></div><?php endif?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><label>Full name<input name="name" required maxlength="120"></label><label>Username<input name="username" required maxlength="60"></label><label>Password (12+ characters)<input type="password" name="password" minlength="12" required autocomplete="new-password"></label><button class="btn primary full">Create administrator</button></form><p class="tiny">Delete or rename setup.php after successful setup.</p></main></body></html>
