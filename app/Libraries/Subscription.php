<?php

namespace App\Libraries;

use App\Models\UsersubscriptionModel;
use CodeIgniter\I18n\Time;

class Subscription {

    /**
     * @throws \Exception
     */
    /**
     * Get all active user subscriptions with their details
     *
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function getActiveUserSubscriptions(int $userId) {
        $currentDate = date('Y-m-d'); // Assuming valid_start_date and valid_stop_date are in 'Y-m-d' format
        $usersSubscriptionsModel = new \App\Models\UsersubscriptionModel();

        return $usersSubscriptionsModel
            ->where('user_id', $userId)
            ->where('is_active', 1)
            //->where('valid_start_date <=', $currentDate)
            ->where('valid_stop_date >=', $currentDate)
            ->findAll();
    }

    /**
     * Get all active user subscriptions with their details
     *
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function hasAValidUserSubscription(int $userId, string $category_name) {
        $currentDate = date('Y-m-d'); // Assuming valid_start_date and valid_stop_date are in 'Y-m-d' format
        $usersSubscriptionsModel = new \App\Models\UsersubscriptionModel();

        return $usersSubscriptionsModel
            ->where('user_id', $userId)
            ->where('subscription_type_category', $category_name)
            ->where('is_active', 1)
            //->where('valid_start_date <=', $currentDate)
            ->where('valid_stop_date >=', $currentDate)
            ->findAll();
    }

    /**
     * Get upcoming subscription renewals for a user
     *
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    public function getUpcomingSubscriptionRenewals(int $userId) {
        $usersSubscriptionsModel = new \App\Models\UsersubscriptionModel();

        return $usersSubscriptionsModel
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->where('valid_stop_date >=', Time::now())
            ->orderBy('valid_stop_date', 'asc')
            ->findAll();
    }

    /**
     * Get all subscription types
     *
     * @return array
     * @throws \Exception
     */
    public function getSubscriptionTypes() {
        $subscriptionTypesModel = new \App\Models\SubscriptiontypeModel();

        return $subscriptionTypesModel
            ->where('deleted_at', null)
            ->findAll();
    }

    /**
     * Get subscription type options for a select dropdown
     *
     * @return array
     * @throws \Exception
     */
    public function getSubscriptionTypeOptions($only_public = 1) {
        $subscriptionTypesModel = new \App\Models\SubscriptiontypeModel();

        if ($only_public) {
            $subscriptionTypes = $subscriptionTypesModel
                ->where('deleted_at', null)
                ->where('subscription_type_is_public', 1)
                ->asArray()
                ->findAll();
        } else {
            $subscriptionTypes = $subscriptionTypesModel
                ->where('deleted_at', null)
                ->asArray()
                ->findAll();
        }

        $options = [];
        if (is_array($subscriptionTypes)) {
            foreach ($subscriptionTypes as $subscriptionType) {
                $options[$subscriptionType['subscription_type_id']] = $subscriptionType['subscription_type_name'];
            }
        }

        return $options;
    }

    /**
     * Find the oldest active and valid subscription package
     *
     * @param int $userId
     * @return object
     * @throws \Exception
     */
    public function getActiveSubscription(int $userId) {
        $userSubscriptionModel = new \App\Models\UsersubscriptionModel();

        $activeSubscription = $userSubscriptionModel
            ->where('user_id', $userId)
            ->where('is_active', 1)
            //->where('valid_start_date <=', Time::now())
            ->where('valid_stop_date >=', Time::now())
            ->orderBy('valid_stop_date', 'asc')
            ->asObject()
            ->first();

        if (!$activeSubscription) {
            // If no active subscription with lessons_left > 0, get the first active within the valid date range
            $activeSubscription = $userSubscriptionModel
                ->where('user_id', $userId)
                ->where('is_active', 1)
                //->where('valid_start_date <=', Time::now())
                ->where('valid_stop_date >=', Time::now())
                ->orderBy('valid_stop_date', 'asc')
                ->asObject()
                ->first();
        }

        return $activeSubscription;
    }

    /**
     * Find the oldest active and valid subscription package
     *
     * @param int $userId
     * @return object
     * @throws \Exception
     */
    public function getActiveSubscriptionByStartDate(int $userId, $startDate) {
        $userSubscriptionModel = new \App\Models\UsersubscriptionModel();

        $activeSubscription = $userSubscriptionModel
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->where('valid_start_date <=', $startDate)
            ->where('valid_stop_date >=', Time::now())
            ->orderBy('valid_stop_date', 'asc')
            ->asObject()
            ->first();

        if (!$activeSubscription) {
            // If no active subscription with lessons_left > 0, get the first active within the valid date range
            $activeSubscription = $userSubscriptionModel
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->where('valid_start_date <=', $startDate)
                ->where('valid_stop_date >=', Time::now())
                ->orderBy('valid_stop_date', 'asc')
                ->asObject()
                ->first();
        }

        return $activeSubscription;
    }

    /**
     * Find all active subscriptions by startdate
     *
     * @param string $startDate
     * @return array
     * @throws \Exception
     */
    public function getAllActiveSubscriptionsByStartDate($startDate) {
        $userSubscriptionModel = new \App\Models\UsersubscriptionModel();

        return $userSubscriptionModel
            ->where('is_active', 1)
            ->where('valid_start_date <=', $startDate)
            ->where('valid_stop_date >=', Time::now())
            ->orderBy('valid_stop_date', 'asc')
            ->asObject()
            ->findAll();
    }

    /**
     * Find all active subscriptions
     *
     * @param string $startDate
     * @return array
     * @throws \Exception
     */
    public function getAllActiveSubscriptions() {
        $userSubscriptionModel = new \App\Models\UsersubscriptionModel();

        return $userSubscriptionModel
            //->where('is_active', 1)
            ->where('valid_start_date <=', Time::now())
            ->where('valid_stop_date >=', Time::now())
            ->orderBy('valid_stop_date', 'asc')
            ->asArray()
            ->findAll();
    }

    /**
     * Find the oldest active and valid subscription package
     *
     * @param int $userId
     * @param string $inDate
     * @return object
     * @throws \Exception
     */
    public function getActiveSubscriptionInDate(int $userId, string $inDate) {
        $userSubscriptionModel = new \App\Models\UsersubscriptionModel();

        $inDate = date("Y-m-d", strtotime($inDate));

        $activeSubscription = $userSubscriptionModel
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->where('valid_start_date <=', $inDate)
            ->where('valid_stop_date >=', $inDate)
            ->orderBy('valid_stop_date', 'asc')
            ->asObject()
            ->first();

        if (!$activeSubscription) {
            // If no active subscription with lessons_left > 0, get the first active within the valid date range
            $activeSubscription = $userSubscriptionModel
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->where('valid_start_date <=', $inDate)
                ->where('valid_stop_date >=', $inDate)
                ->orderBy('valid_stop_date', 'asc')
                ->asObject()
                ->first();
        }

        return $activeSubscription;
    }

    public function hasOncePurchasedASubscription(int $userId) {
        $usersSubscriptionsModel = new \App\Models\UsersubscriptionModel();

        $userSubscriptionsCount = $usersSubscriptionsModel
            ->where('user_id', $userId)
            ->countAllResults();

        return ($userSubscriptionsCount > 0);
    }

    public function getSubscriptionUntil(int $userId) {
        $usersSubscriptionsModel = new \App\Models\UsersubscriptionModel();

        $userSubscription = $usersSubscriptionsModel
            ->where('user_id', $userId)
            ->where('valid_stop_date IS NOT NULL')
            ->orderBy('valid_stop_date', 'DESC')
            ->first();

        if(isset($userSubscription) && is_object($userSubscription)) {
            return strtotime($userSubscription->valid_stop_date . ' 23:59:59');
        }

        return 0;
    }




    /**
     * Renew a subscription by extending its end date
     *
     * @param int $subscriptionId
     * @param string $newEndDate
     * @return bool
     */
    public function renew(int $subscriptionId, string $newEndDate)
    {
        $userSubscriptionModel = new UsersubscriptionModel();
        return $userSubscriptionModel->extendSubscription($subscriptionId, $newEndDate);
    }

    /**
     * Mark a subscription as failed
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function failed(int $subscriptionId)
    {
        $userSubscriptionModel = new UsersubscriptionModel();
        return $userSubscriptionModel->updateStatus($subscriptionId, 'failed');
    }

    /**
     * Cancel a subscription
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function cancel(int $subscriptionId)
    {
        $userSubscriptionModel = new UsersubscriptionModel();
        return $userSubscriptionModel->updateStatus($subscriptionId, 'cancelled');
    }

    /**
     * Mark a subscription as in notice
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function notice(int $subscriptionId)
    {
        $userSubscriptionModel = new UsersubscriptionModel();
        return $userSubscriptionModel->updateStatus($subscriptionId, 'in_notice');
    }

    /**
     * Mark a subscription as paid
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function paid(int $subscriptionId)
    {
        $userSubscriptionModel = new UsersubscriptionModel();
        return $userSubscriptionModel->updateStatus($subscriptionId, 'paid');
    }

    /**
     * Refund a subscription
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function refund(int $subscriptionId)
    {
        $userSubscriptionModel = new UsersubscriptionModel();
        return $userSubscriptionModel->updateStatus($subscriptionId, 'refunded');
    }

    /**
     * Partially refund a subscription
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function partiallyRefund(int $subscriptionId)
    {
        $userSubscriptionModel = new UsersubscriptionModel();
        return $userSubscriptionModel->updateStatus($subscriptionId, 'partially-refunded');
    }

    /**
     * Mark a subscription as chargeback
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function chargeback(int $subscriptionId)
    {
        $userSubscriptionModel = new UsersubscriptionModel();
        return $userSubscriptionModel->updateStatus($subscriptionId, 'chargeback');
    }

    /**
     * Mark a subscription as uncaptured
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function uncaptured(int $subscriptionId)
    {
        $userSubscriptionModel = new UsersubscriptionModel();
        return $userSubscriptionModel->updateStatus($subscriptionId, 'uncaptured');
    }

    /**
     * Check if the user has already booked a specific subscription type and return the count
     *
     * @param int $userId
     * @param int $subscriptionTypeId
     * @return int
     * @throws \Exception
     */
    public function countUserSubscriptionsByType(int $userId, int $subscriptionTypeId): int {
        $usersSubscriptionsModel = new UsersubscriptionModel();

        return $usersSubscriptionsModel
            ->where('user_id', $userId)
            ->where('subscription_type_id', $subscriptionTypeId)
            ->countAllResults();
    }

    /**
     * Check if the user has already booked a specific subscription type and return a boolean
     *
     * @param int $userId
     * @param int $subscriptionTypeId
     * @return bool
     * @throws \Exception
     */
    public function hasUserBookedSubscriptionType(int $userId, int $subscriptionTypeId): bool {
        return $this->countUserSubscriptionsByType($userId, $subscriptionTypeId) > 0;
    }

}
