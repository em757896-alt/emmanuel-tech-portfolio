<?php
$pageTitle = 'Page Not Found - PBO Kenya';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .error-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; }
        .error-card { text-align: center; max-width: 500px; padding: 3rem; }
        .error-code { font-size: 8rem; font-weight: 800; background: linear-gradient(135deg,#3b82f6,#6366f1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; }
        .error-title { font-size: 1.75rem; font-weight: 700; margin: 1rem 0 .5rem; }
        .error-desc { color: #6b7280; margin-bottom: 2rem; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-card">
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-desc">The page you're looking for doesn't exist or has been moved. Try searching or navigating from the homepage.</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="/" class="btn btn-primary"><i class="fas fa-home me-2"></i>Go Home</a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Go Back</a>
            </div>
        </div>
    </div>
</body>
</html>
