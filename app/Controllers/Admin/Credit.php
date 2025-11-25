<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BookingModel;

class Credit extends BaseController
{
    /**
     * Zeigt Formular zum manuellen Verarbeiten einer Rückerstattung
     */
    public function refund()
    {
        // Hole alle Transaktionen die refundable sind
        $transactionModel = new \App\Models\TransactionModel();
        $transactions = $transactionModel
            ->where('refundable', 1)
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('admin/credits/refund', ['transactions' => $transactions]);
    }

    /**
     * Verarbeitet eine manuelle Rückerstattung (z.B. nach Saferpay Backend-Refund)
     */
    public function processRefund()
    {
        $transactionId = $this->request->getPost('transaction_id');
        $amount = floatval($this->request->getPost('amount'));
        $refundType = $this->request->getPost('refund_type') ?? 'refunded';
        $description = $this->request->getPost('description');

        if (!$transactionId || $amount <= 0) {
            return redirect()->back()->with('error', 'Ungültige Eingaben');
        }

        try {
            // Finde Transaktion
            $transactionModel = new \App\Models\TransactionModel();
            $transaction = $transactionModel->find($transactionId);

            if (!$transaction) {
                return redirect()->back()->with('error', 'Transaktion nicht gefunden');
            }

            // Finde User-ID
            $userId = null;

            // Versuch 1: Über Subscription
            if (!empty($transaction->subscription_id)) {
                $subscriptionModel = new \App\Models\UsersubscriptionModel();
                $subscription = $subscriptionModel->find($transaction->subscription_id);
                if ($subscription) {
                    $userId = $subscription->user_id;
                }
            }

            // Versuch 2: Über metadata
            if (!$userId && !empty($transaction->metadata)) {
                $metadata = json_decode($transaction->metadata, true);
                if (isset($metadata['user_id'])) {
                    $userId = $metadata['user_id'];
                }
            }

            if (!$userId) {
                return redirect()->back()->with('error', 'User-ID konnte nicht ermittelt werden');
            }

            // Erstelle Refund-Booking
            $bookingModel = new BookingModel();

            $refundTypeLabel = match($refundType) {
                'refunded' => 'Rückerstattung',
                'refundpending' => 'Rückerstattung (ausstehend)',
                'partially-refunded' => 'Teilrückerstattung',
                default => 'Rückerstattung'
            };

            $finalDescription = $description ?: sprintf(
                '%s - %s - %.2f CHF',
                $refundTypeLabel,
                $transaction->payment_brand ?? 'Unbekannt',
                $amount
            );

            $bookingId = $bookingModel->insert([
                'user_id' => $userId,
                'type' => 'refund',
                'description' => $finalDescription,
                'amount' => $amount,
                'paid_amount' => 0.00,
                'reference_id' => $transactionId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Update Transaktion-Status
            $transactionModel->update($transactionId, ['status' => $refundType]);

            log_message('info', 'Manuelle Refund-Verarbeitung erfolgreich', [
                'bookingId' => $bookingId,
                'transactionId' => $transactionId,
                'userId' => $userId,
                'amount' => $amount,
                'admin_user' => auth()->user()->id ?? 'unknown'
            ]);

            return redirect()->to('/admin/credits/refund')->with('success', 'Rückerstattung erfolgreich verarbeitet');

        } catch (\Exception $e) {
            log_message('error', 'Fehler bei manueller Refund-Verarbeitung', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Fehler: ' . $e->getMessage());
        }
    }
}
