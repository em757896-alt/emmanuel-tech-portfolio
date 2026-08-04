<?php
// logout.php
session_start();
session_unset();
session_destroy();
header("Location: home.php?msg=Logged+out");
exit;