<?php
declare(strict_types=1);
// New application entry point. The former student system remains in the repository as legacy source.
header('Location: pos/index.php?page=dashboard', true, 302);
exit;
