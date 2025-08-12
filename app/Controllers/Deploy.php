<?php namespace App\Controllers;

use CodeIgniter\Controller;

class Deploy extends Controller
{
    protected $config;

    public function __construct()
    {
        helper('filesystem');
        $this->config = config('Deploy');
    }

    public function webhook()
    {
        // --- Request Body ---
        $content = file_get_contents('php://input');
        $json    = json_decode($content, true);

        // --- Logging ---
        $logFile = $this->config->logfile;
        $file = fopen($logFile, 'a');
        $time = time();
        date_default_timezone_set('UTC');
        fputs($file, date("d-m-Y (H:i:s)", $time) . " - Webhook triggered\n");

        // --- Set Response Header ---
        $this->response->setContentType('text/plain');

        // --- Check Token / Signature ---
        $token = false;
        $sha = $json['checkout_sha'] ?? null;
        $dir = rtrim($this->config->dir, '/') . '/';

        // Retrieve token from headers or GET
        if (!$token && $this->request->getHeaderLine('X-Hub-Signature')) {
            list($algo, $token) = explode('=', $this->request->getHeaderLine('X-Hub-Signature'), 2) + ['', ''];
        } elseif (!$token && $this->request->getHeaderLine('X-Hub-Signature-256')) {
            list($algo, $token) = explode('=', $this->request->getHeaderLine('X-Hub-Signature-256'), 2) + ['', ''];
        } elseif ($this->request->getHeaderLine('X-Gitlab-Token')) {
            $token = $this->request->getHeaderLine('X-Gitlab-Token');
        } elseif ($this->request->getGet('token')) {
            $token = $this->request->getGet('token');
        }

        // Helper function to log and forbid
        $forbid = function($reason) use ($file) {
            $error = "=== ERROR: $reason ===\n*** ACCESS DENIED ***\n";
            http_response_code(403);
            fputs($file, $error . "\n\n");
            fclose($file);
            exit($error);
        };

        // Check signatures and tokens
        if (!empty($this->config->token) && $this->request->getHeaderLine('X-Hub-Signature')) {
            list($algo, $githubSignature) = explode('=', $this->request->getHeaderLine('X-Hub-Signature'), 2) + ['', ''];
            $expectedSignature = hash_hmac($algo, $content, $this->config->token);

            fputs($file, "DEBUG: algo=$algo\n");
            fputs($file, "DEBUG: githubSignature=$githubSignature\n");
            fputs($file, "DEBUG: expectedSignature=$expectedSignature\n");

            if (!hash_equals($expectedSignature, $githubSignature)) {
                $forbid('X-Hub-Signature does not match TOKEN');
            }
        }

        if (!empty($this->config->token) && $this->request->getHeaderLine('X-Hub-Signature-256')) {
            list($algo, $githubSignature) = explode('=', $this->request->getHeaderLine('X-Hub-Signature-256'), 2) + ['', ''];
            $expectedSignature = hash_hmac('sha256', $content, $this->config->token);

            fputs($file, "DEBUG: algo=sha256\n");
            fputs($file, "DEBUG: githubSignature=$githubSignature\n");
            fputs($file, "DEBUG: expectedSignature=$expectedSignature\n");

            if (!hash_equals($expectedSignature, $githubSignature)) {
                $forbid('X-Hub-Signature-256 does not match TOKEN');
            }
        }

        if (!empty($this->config->token) && $this->request->getHeaderLine('X-Gitlab-Token') && $token !== $this->config->token) {
            $forbid('X-GitLab-Token does not match TOKEN');
        }

        if (!empty($this->config->token) && $this->request->getGet('token') && $token !== $this->config->token) {
            $forbid('$_GET["token"] does not match TOKEN');
        }

        if (!empty($this->config->token) &&
            !$this->request->getHeaderLine('X-Hub-Signature') &&
            !$this->request->getHeaderLine('X-Gitlab-Token') &&
            !$this->request->getGet('token')) {
            $forbid('No token detected');
        }

        // --- Branch check ---
        if (($json['ref'] ?? '') !== $this->config->branch) {
            $error = "=== ERROR: Pushed branch `" . ($json['ref'] ?? '') . "` does not match BRANCH `" . $this->config->branch . "` ===\n";
            http_response_code(400);
            fputs($file, $error);
            fclose($file);
            return $this->response->setBody($error)->setStatusCode(400);
        }

        // --- Repository checks ---
        if (!is_dir($dir) || !file_exists($dir . '.git')) {
            $error = "=== ERROR: DIR `" . $dir . "` is not a git repository ===\n";
            if (!is_dir($dir)) $error = "=== ERROR: DIR `" . $dir . "` is not a directory ===\n";
            if (!file_exists($dir)) $error = "=== ERROR: DIR `" . $dir . "` does not exist ===\n";

            http_response_code(400);
            fputs($file, $error);
            fclose($file);
            return $this->response->setBody($error)->setStatusCode(400);
        }

        // --- Execute deploy commands ---
        chdir($dir);
        fputs($file, "*** AUTO PULL INITIATED ***\n");

        // Reset HEAD if requested
        if ($this->request->getGet('reset') === 'true') {
            fputs($file, "*** RESET TO HEAD INITIATED ***\n");
            exec($this->config->gitPath . " reset --hard HEAD 2>&1", $output, $exit);
            $output = !empty($output) ? implode("\n", $output) : "[no output]";
            $output .= "\n";
            if ($exit !== 0) {
                http_response_code(500);
                $error = "=== ERROR: Reset to head failed using GIT `" . $this->config->gitPath . "` ===\n" . $output;
                fputs($file, $error);
                fclose($file);
                return $this->response->setBody($error)->setStatusCode(500);
            }
            fputs($file, $output);
        }

        // BEFORE_PULL if set
        if (!empty($this->config->beforePull)) {
            fputs($file, "*** BEFORE_PULL INITIATED ***\n");
            exec($this->config->beforePull . " 2>&1", $output, $exit);
            $output = !empty($output) ? implode("\n", $output) : "[no output]";
            $output .= "\n";
            if ($exit !== 0) {
                http_response_code(500);
                $error = "=== ERROR: BEFORE_PULL `" . $this->config->beforePull . "` failed ===\n" . $output;
                fputs($file, $error);
                fclose($file);
                return $this->response->setBody($error)->setStatusCode(500);
            }
            fputs($file, $output);
        }

        // Git Pull
        exec($this->config->gitPath . " pull 2>&1", $output, $exit);
        $output = !empty($output) ? implode("\n", $output) : "[no output]";
        $output .= "\n";

        if ($exit !== 0) {
            http_response_code(500);
            $error = "=== ERROR: Pull failed using GIT `" . $this->config->gitPath . "` and DIR `" . $dir . "` ===\n" . $output;
            fputs($file, $error);
            fclose($file);
            return $this->response->setBody($error)->setStatusCode(500);
        }
        fputs($file, $output);

        // Reset to specific sha if provided
        if (!empty($sha)) {
            fputs($file, "*** RESET TO HASH INITIATED ***\n");
            exec($this->config->gitPath . " reset --hard {$sha} 2>&1", $output, $exit);
            $output = !empty($output) ? implode("\n", $output) : "[no output]";
            $output .= "\n";

            if ($exit !== 0) {
                http_response_code(500);
                $error = "=== ERROR: Reset failed using GIT `" . $this->config->gitPath . "` and sha `" . $sha . "` ===\n" . $output;
                fputs($file, $error);
                fclose($file);
                return $this->response->setBody($error)->setStatusCode(500);
            }
            fputs($file, $output);
        }

        // AFTER_PULL if set
        if (!empty($this->config->afterPull)) {
            fputs($file, "*** AFTER_PULL INITIATED ***\n");
            exec($this->config->afterPull . " 2>&1", $output, $exit);
            $output = !empty($output) ? implode("\n", $output) : "[no output]";
            $output .= "\n";

            if ($exit !== 0) {
                http_response_code(500);
                $error = "=== ERROR: AFTER_PULL `" . $this->config->afterPull . "` failed ===\n" . $output;
                fputs($file, $error);
                fclose($file);
                return $this->response->setBody($error)->setStatusCode(500);
            }
            fputs($file, $output);
        }

        // Optional: Composer Update
        if ($this->config->runComposerUpdate) {
            fputs($file, "*** COMPOSER UPDATE INITIATED ***\n");
            exec('composer update 2>&1', $composerOutput, $composerExitCode);
            $composerOutputText = !empty($composerOutput) ? implode("\n", $composerOutput) : "[no output]";
            $composerOutputText .= "\n";

            if ($composerExitCode !== 0) {
                http_response_code(500);
                $error = "=== ERROR: Composer update failed ===\n" . $composerOutputText;
                fputs($file, $error);
                fclose($file);
                return $this->response->setBody($error)->setStatusCode(500);
            } else {
                fputs($file, $composerOutputText);
            }
        }

        fputs($file, "*** AUTO PULL COMPLETE ***\n");
        fclose($file);

        return $this->response->setBody("Auto pull (and optional composer update) complete.\n")->setStatusCode(200);
    }
}
