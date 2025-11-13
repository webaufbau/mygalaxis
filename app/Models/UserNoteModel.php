<?php

namespace App\Models;

use CodeIgniter\Model;

class UserNoteModel extends Model
{
    protected $table = 'user_notes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'admin_user_id',
        'type',
        'note_text',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'admin_user_id' => 'required|integer',
        'type' => 'required|in_list[phone,email]',
        'note_text' => 'required|min_length[3]',
    ];

    protected $validationMessages = [
        'note_text' => [
            'required' => 'Bitte geben Sie einen Notiz-Text ein.',
            'min_length' => 'Die Notiz muss mindestens 3 Zeichen lang sein.',
        ],
        'type' => [
            'required' => 'Bitte wählen Sie einen Typ aus.',
            'in_list' => 'Ungültiger Typ ausgewählt.',
        ],
    ];

    /**
     * Hole alle Notizen für einen bestimmten Benutzer
     */
    public function getNotesForUser($userId, $type = null, $dateFrom = null, $dateTo = null)
    {
        $this->select('user_notes.*, users.company_name as admin_name')
            ->join('users', 'users.id = user_notes.admin_user_id', 'left')
            ->where('user_notes.user_id', $userId);

        if ($type && $type !== 'all') {
            $this->where('user_notes.type', $type);
        }

        if ($dateFrom) {
            $this->where('DATE(user_notes.created_at) >=', $dateFrom);
        }

        if ($dateTo) {
            $this->where('DATE(user_notes.created_at) <=', $dateTo);
        }

        return $this->orderBy('user_notes.created_at', 'DESC')->findAll();
    }

    /**
     * Zähle Notizen nach Typ
     */
    public function countByType($userId)
    {
        return [
            'all' => $this->where('user_id', $userId)->countAllResults(false),
            'phone' => $this->where('user_id', $userId)->where('type', 'phone')->countAllResults(false),
            'email' => $this->where('user_id', $userId)->where('type', 'email')->countAllResults(),
        ];
    }
}
