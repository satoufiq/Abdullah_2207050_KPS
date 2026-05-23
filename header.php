<?php
/**
 * KUET Photography Society - Header Template
 */
$body_class = isset($body_class) ? trim($body_class) : 'luxury-site';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KUET Photography Society home page.">
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?>KUET Photography Society</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script defer src="script.js"></script>
</head>
<body class="<?php echo htmlspecialchars($body_class); ?>">
