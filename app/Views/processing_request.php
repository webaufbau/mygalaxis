<!DOCTYPE html>
<html>
<head>
    <title>Anfrage wird verarbeitet</title>
</head>
<body>
<p>Ihre Anfrage wird verarbeitet... Bitte einen Moment Geduld.</p>
<div class="loader"></div>

<style>
    .loader {
        border: 8px solid #f3f3f3;
        border-top: 8px solid #007bff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>


<script>
    async function checkSession() {
        try {
            const response = await fetch('verification/check-session');
            const data = await response.json();

            if (data.status === 'ok') {
                window.location.href = '/verification';
            } else {
                setTimeout(checkSession, 2000);
            }
        } catch (e) {
            setTimeout(checkSession, 5000);
        }
    }

    checkSession();
</script>
</body>
</html>
