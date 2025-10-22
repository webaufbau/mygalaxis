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
    <meta name="robots" content="noindex">
    <title>500 - Serverfehler</title>

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
            animation: shake 3s ease-in-out infinite;
        }

        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
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
            margin: 5px;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(<?= hexdec(substr($primaryColor, 1, 2)) ?>, <?= hexdec(substr($primaryColor, 3, 2)) ?>, <?= hexdec(substr($primaryColor, 5, 2)) ?>, 0.6);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background: #cbd5e0;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
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
            text-align: left;
        }

        .suggestions li {
            margin: 10px 0;
            padding-left: 10px;
        }

        .suggestions li:before {
            content: "→";
            color: <?= $primaryColor ?>;
            font-weight: bold;
            margin-right: 10px;
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
        <div class="error-icon">⚠️</div>
        <div class="error-code">500</div>
        <h1>Serverfehler</h1>
        <p>Es ist ein unerwarteter Fehler aufgetreten. Wir wurden automatisch benachrichtigt und kümmern uns darum.</p>

        <a href="/" class="btn-home">Zur Startseite</a>
        <?php if ($isLoggedIn): ?>
            <a href="javascript:history.back()" class="btn-home btn-secondary">Zurück</a>
        <?php endif; ?>

        <div class="suggestions">
            <h3>Was Sie tun können:</h3>
            <ul>
                <li>Laden Sie die Seite neu (F5 oder Ctrl+R)</li>
                <li>Versuchen Sie es in einigen Minuten erneut</li>
                <li>Leeren Sie Ihren Browser-Cache</li>
                <?php if ($userRole === 'admin'): ?>
                    <li>Prüfen Sie die Error-Logs im Server</li>
                    <li>Kontaktieren Sie den technischen Support</li>
                <?php elseif ($userRole === 'user'): ?>
                    <li>Kontaktieren Sie unseren Support, falls das Problem bestehen bleibt</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
