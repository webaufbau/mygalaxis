<?php

namespace App\Libraries;

use CodeIgniter\Email\Email;

/**
 * Custom Email class that automatically adds logs@webaufbau.com as BCC
 */
class CustomEmail extends Email
{
    protected $autoBCC = 'logs@webaufbau.com';

    /**
     * Override setBCC to always include logs@webaufbau.com
     */
    public function setBCC($bcc, $limit = '')
    {
        // Konvertiere BCC in Array falls String
        if (is_string($bcc)) {
            $bccArray = array_map('trim', explode(',', $bcc));
        } else {
            $bccArray = (array) $bcc;
        }

        // Füge logs@webaufbau.com hinzu wenn nicht bereits vorhanden
        if (!in_array($this->autoBCC, $bccArray)) {
            $bccArray[] = $this->autoBCC;
        }

        // Rufe Parent-Methode mit erweiterter BCC-Liste auf
        return parent::setBCC($bccArray, $limit);
    }

    public function send($autoClear = true): bool
    {
        // Falls setBCC nie aufgerufen wurde, füge logs@webaufbau.com hinzu
        if (empty($this->BCCArray)) {
            $this->setBCC($this->autoBCC);
        }

        return parent::send($autoClear);
    }
}
