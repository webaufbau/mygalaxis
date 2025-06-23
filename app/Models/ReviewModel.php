<?php namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $allowedFields = ['recipient_id', 'rating', 'comment', 'created_at', 'created_by'];

    protected $useTimestamps = true;
}
