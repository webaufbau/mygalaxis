<?php
namespace App\Libraries;

use App\Models\TransactionModel;

class Transaction
{
    /**
     * @throws \ReflectionException
     */
    public function updateStatus($transactionId, $status, $data = []): bool {
        $transactionModel = new TransactionModel();

        $exists = $transactionModel->where('id', $transactionId)->first();

        $transactionData = [
            'id' => $transactionId,
            'uuid' => $data['uuid'],
            'amount' => $data['amount'],
            'referenceId' => $data['referenceId'],
            'time' => $data['time'],
            'status' => $data['status'],
            'lang' => $data['lang'],
            'psp' => $data['psp'],
            'pspId' => $data['pspId'],
            'payrexx_fee' => $data['payrexxFee'],
            'refundable' => $data['refundable'],
            'partially_refundable' => $data['partiallyRefundable'],
            'metadata' => json_encode($data['metadata']),
            'subscription_id' => $data['subscription']['id'] ?? null,
            'invoice_id' => $data['invoice']['number'] ?? null,
            'contact_id' => $data['contact']['id'] ?? null,
            'payment_brand' => $data['payment']['brand'] ?? null,
            'payment_wallet' => $data['payment']['wallet'] ?? null,
            'instanceName' => $data['instance']['name'] ?? null,
            'instanceUuid' => $data['instance']['uuid'] ?? null,
        ];

        if (!$exists) {
            $inserted = $transactionModel->insert($transactionData);
        } else {
            $transactionModel->update($transactionId, $transactionData);
        }

        // Status aktualisieren
        return $transactionModel->updateStatus($transactionId, $status);
    }

    public function refund($transactionId, $data = []): bool {
        return $this->updateStatus($transactionId, 'refunded', $data);
    }

    public function partiallyRefund($transactionId, $data = []): bool {
        return $this->updateStatus($transactionId, 'partially-refunded', $data);
    }

    public function chargeback($transactionId, $data = []): bool {
        return $this->updateStatus($transactionId, 'chargeback', $data);
    }

    public function uncaptured($transactionId, $data = []): bool {
        return $this->updateStatus($transactionId, 'uncaptured', $data);
    }


}
