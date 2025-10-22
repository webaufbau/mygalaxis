<?php
// Bestimme Farbe basierend auf der Rolle des eingeloggten Users
$userRole = null;
$isLoggedIn = false;

try {
    if (function_exists('auth') && auth()->loggedIn()) {
        $isLoggedIn = true;
        $user = auth()->user();
        if ($user && $user->inGroup('admin')) {
            $userRole = 'admin';
        } elseif ($user && $user->inGroup('user')) {
            $userRole = 'user';
        }
    }
} catch (\Exception $e) {
    // Fehler beim Prüfen der Authentifizierung ignorieren
}

// Admin: #FF6B6B (rot), User/Firma: #4A90E2 (blau), Default: #667eea (violett)
if ($userRole === 'admin') {
    $primaryColor = '#FF6B6B';
    $secondaryColor = '#E85A5A';
} elseif ($userRole === 'user') {
    $primaryColor = '#4A90E2';
    $secondaryColor = '#3A7BC8';
} else {
    $primaryColor = '#667eea';
    $secondaryColor = '#764ba2';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Seite nicht gefunden</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 60px 40px;
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-icon {
            font-size: 120px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .error-code {
            font-size: 80px;
            font-weight: 700;
            background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        h1 {
            font-size: 32px;
            color: #2d3748;
            margin-bottom: 15px;
            font-weight: 600;
        }

        p {
            font-size: 16px;
            color: #718096;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .error-message {
            background: #f7fafc;
            border-left: 4px solid <?= $primaryColor ?>;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            border-radius: 4px;
        }

        .error-message code {
            background: #edf2f7;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: "Courier New", monospace;
            font-size: 14px;
            color: #e53e3e;
        }

        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $secondaryColor ?> 100%);
            color: white;
            padding: 14px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(<?= hexdec(substr($primaryColor, 1, 2)) ?>, <?= hexdec(substr($primaryColor, 3, 2)) ?>, <?= hexdec(substr($primaryColor, 5, 2)) ?>, 0.4);
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(<?= hexdec(substr($primaryColor, 1, 2)) ?>, <?= hexdec(substr($primaryColor, 3, 2)) ?>, <?= hexdec(substr($primaryColor, 5, 2)) ?>, 0.6);
        }

        .suggestions {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }

        .suggestions h3 {
            font-size: 18px;
            color: #2d3748;
            margin-bottom: 15px;
        }

        .suggestions ul {
            list-style: none;
            padding: 0;
        }

        .suggestions li {
            margin: 10px 0;
        }

        .suggestions a {
            color: <?= $primaryColor ?>;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .suggestions a:hover {
            color: <?= $secondaryColor ?>;
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .error-container {
                padding: 40px 25px;
            }

            .error-code {
                font-size: 60px;
            }

            h1 {
                font-size: 24px;
            }

            .error-icon {
                font-size: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🔍</div>
        <div class="error-code">404</div>
        <h1>Seite nicht gefunden</h1>
        <p>Die gesuchte Seite konnte leider nicht gefunden werden. Möglicherweise wurde sie verschoben oder existiert nicht mehr.</p>

        <?php if (ENVIRONMENT !== 'production' && !empty($message)): ?>
            <div class="error-message">
                <strong>Entwickler-Info:</strong><br>
                <code><?= esc($message) ?></code>
            </div>
        <?php endif; ?>

        <a href="/" class="btn-home">Zur Startseite</a>

        <div class="suggestions">
            <h3>Vielleicht suchen Sie nach:</h3>
            <ul>
                <?php if ($userRole === 'admin'): ?>
                    <li><a href="/admin/dashboard">🔧 Admin Dashboard</a></li>
                    <li><a href="/admin/offers">📋 Offerten verwalten</a></li>
                    <li><a href="/admin/user">👥 Benutzer verwalten</a></li>
                <?php elseif ($userRole === 'user'): ?>
                    <li><a href="/dashboard">📊 Dashboard</a></li>
                    <li><a href="/offers">📋 Meine Offerten</a></li>
                    <li><a href="/profile">👤 Mein Profil</a></li>
                <?php else: ?>
                    <li><a href="/">🏠 Startseite</a></li>
                    <li><a href="/login">🔑 Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
