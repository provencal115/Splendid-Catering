<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Connection Required</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container text-center">
    <h1>Database Not Connected</h1>
    <p class="text-muted">Please run the setup to initialize the Splendid Caterery database.</p>
    <a href="<?= defined('BASE_URL') ? BASE_URL . '/setup' : '/setup' ?>" class="btn btn-warning btn-lg">Run Setup</a>
</div>
</body>
</html>
