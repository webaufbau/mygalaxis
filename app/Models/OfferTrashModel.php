<?php

namespace App\Models;

use CodeIgniter\Model;

class OfferTrashModel extends Model
{
    protected $table = 'offers_trash';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'original_offer_id',
        'original_table',
        'type',
        'original_type',
        'sub_type',
        'title',
        'status',
        'price',
        'discounted_price',
        'buyers',
        'bought_by',
        'language',
        'firstname',
        'lastname',
        'company',
        'email',
        'phone',
        'work_start_date',
        'additional_service',
        'service_url',
        'uuid',
        'customer_type',
        'city',
        'zip',
        'country',
        'platform',
        'form_fields',
        'form_fields_combo',
        'headers',
        'referer',
        'verified',
        'verify_type',
        'from_campaign',
        'checked_at',
        'reminder_sent_at',
        'verification_token',
        'form_name',
        'group_id',
        'access_hash',
        'type_specific_data',
        'deleted_at',
        'deleted_by_user_id',
        'deletion_reason',
        'original_created_at',
        'original_updated_at',
    ];

    protected $useTimestamps = false; // We manage deleted_at manually

    /**
     * Archive an offer to trash before deletion
     *
     * @param int $offerId Original offer ID
     * @param string $type Offer type (e.g., 'plumbing', 'cleaning')
     * @param int|null $deletedByUserId User ID who is deleting
     * @param string|null $reason Reason for deletion
     * @return bool Success status
     */
    public function archiveOffer(int $offerId, string $type, ?int $deletedByUserId = null, ?string $reason = null): bool
    {
        $db = \Config\Database::connect();

        // Get main offer data
        $offerModel = new OfferModel();
        $offer = $offerModel->find($offerId);

        if (!$offer) {
            log_message('error', "Cannot archive offer #{$offerId}: Offer not found");
            return false;
        }

        // Get type-specific data
        $typeSpecificData = $this->getTypeSpecificData($offerId, $type);
        $originalTable = $this->getOriginalTableName($type);

        // Prepare trash data
        $trashData = [
            'original_offer_id' => $offerId,
            'original_table' => $originalTable,
            'deleted_at' => date('Y-m-d H:i:s'),
            'deleted_by_user_id' => $deletedByUserId,
            'deletion_reason' => $reason,
            'original_created_at' => $offer['created_at'] ?? null,
            'original_updated_at' => $offer['updated_at'] ?? null,
            'type_specific_data' => !empty($typeSpecificData) ? json_encode($typeSpecificData) : null,
        ];

        // Copy all offer fields
        foreach ($this->allowedFields as $field) {
            if (isset($offer[$field]) && !isset($trashData[$field])) {
                $trashData[$field] = $offer[$field];
            }
        }

        // Insert into trash
        try {
            $this->insert($trashData);
            log_message('info', "Offer #{$offerId} archived to trash (type: {$type})");
            return true;
        } catch (\Exception $e) {
            log_message('error', "Failed to archive offer #{$offerId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get type-specific data from specialized offer tables
     *
     * @param int $offerId
     * @param string $type
     * @return array|null
     */
    protected function getTypeSpecificData(int $offerId, string $type): ?array
    {
        $tableName = $this->getOriginalTableName($type);

        if (!$tableName) {
            return null;
        }

        $db = \Config\Database::connect();

        try {
            $query = $db->table($tableName)
                ->where('offer_id', $offerId)
                ->get();

            $result = $query->getRowArray();
            return $result ?: null;
        } catch (\Exception $e) {
            log_message('warning', "Could not fetch type-specific data for offer #{$offerId} from {$tableName}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the original table name for a given offer type
     *
     * @param string $type
     * @return string|null
     */
    protected function getOriginalTableName(string $type): ?string
    {
        $typeToTable = [
            'plumbing' => 'offers_plumbing',
            'cleaning' => 'offers_cleaning',
            'painting' => 'offers_painting',
            'gardening' => 'offers_gardening',
            'electrician' => 'offers_electrician',
            'heating' => 'offers_heating',
            'tiling' => 'offers_tiling',
            'flooring' => 'offers_flooring',
            'move' => 'offers_move',
            'move_cleaning' => 'offers_move_cleaning',
        ];

        return $typeToTable[$type] ?? null;
    }

    /**
     * Get all trashed offers with filters
     *
     * @param array $filters Optional filters (type, platform, date_from, date_to)
     * @return array
     */
    public function getTrashedOffers(array $filters = []): array
    {
        $builder = $this->builder();

        // Apply filters
        if (!empty($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        if (!empty($filters['platform'])) {
            $builder->where('platform', $filters['platform']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('deleted_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $builder->where('deleted_at <=', $filters['date_to'] . ' 23:59:59');
        }

        if (!empty($filters['deleted_by'])) {
            $builder->where('deleted_by_user_id', $filters['deleted_by']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart();
            $builder->like('title', $filters['search']);
            $builder->orLike('email', $filters['search']);
            $builder->orLike('company', $filters['search']);
            $builder->orLike('firstname', $filters['search']);
            $builder->orLike('lastname', $filters['search']);
            $builder->groupEnd();
        }

        return $builder->orderBy('deleted_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Optional: Restore an offer from trash
     * This is complex and may require recreating entries in multiple tables
     *
     * @param int $trashId
     * @return bool
     */
    public function restoreOffer(int $trashId): bool
    {
        $trashedOffer = $this->find($trashId);

        if (!$trashedOffer) {
            log_message('error', "Cannot restore: Trash entry #{$trashId} not found");
            return false;
        }

        $db = \Config\Database::connect();

        try {
            $db->transStart();

            // Restore main offer
            $offerModel = new OfferModel();
            $offerData = [];

            foreach ($offerModel->allowedFields as $field) {
                if (isset($trashedOffer[$field])) {
                    $offerData[$field] = $trashedOffer[$field];
                }
            }

            // Set timestamps
            $offerData['created_at'] = $trashedOffer['original_created_at'];
            $offerData['updated_at'] = date('Y-m-d H:i:s');

            // Don't use original ID - let it auto-increment
            // But we could use a different status to indicate it was restored
            if (!empty($offerData['status'])) {
                $offerData['status'] = 'restored';
            }

            $newOfferId = $offerModel->insert($offerData, true);

            // Restore type-specific data if available
            if (!empty($trashedOffer['type_specific_data']) && !empty($trashedOffer['type'])) {
                $typeData = json_decode($trashedOffer['type_specific_data'], true);

                if ($typeData) {
                    $tableName = $trashedOffer['original_table'];

                    if ($tableName && $db->tableExists($tableName)) {
                        $typeData['offer_id'] = $newOfferId;
                        unset($typeData['id']); // Don't use original ID

                        $db->table($tableName)->insert($typeData);
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                log_message('error', "Failed to restore offer from trash #{$trashId}");
                return false;
            }

            log_message('info', "Offer restored from trash #{$trashId} -> new offer ID: {$newOfferId}");

            // Optionally: Remove from trash after successful restore
            // $this->delete($trashId);

            return true;

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', "Exception restoring offer from trash #{$trashId}: " . $e->getMessage());
            return false;
        }
    }
}
