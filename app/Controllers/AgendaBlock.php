<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\BlockedDayModel;

class AgendaBlock extends BaseController
{
    public function index()
    {
        $userId = auth()->id(); // oder manuell aus Session holen
        $model = new BlockedDayModel();
        $blockedDates = $model->getDatesByUser($userId);

        return view('account/agenda_block', [
            'blockedDates' => $blockedDates
        ]);
    }

    public function toggle()
    {
        $userId = auth()->id();
        $date = $this->request->getPost('date'); // format: YYYY-MM-DD

        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Ungültiges Datum']);
        }

        $model = new BlockedDayModel();

        if ($model->isBlocked($userId, $date)) {
            $model->remove($userId, $date);
        } else {
            $model->add($userId, $date);
        }

        return $this->response->setJSON(['success' => true,
            'csrf' => csrf_hash(),]);
    }

    public function blocked_events()
    {
        $userId = auth()->id(); // oder manuell aus Session
        $timestamp = (int) $this->request->getGet('ts');

        $date = date('Y-m-01', $timestamp); // Ersten Tag des Monats
        $start = date('Y-m-01', strtotime($date));
        $end = date('Y-m-t', strtotime($date)); // Letzter Tag des Monats

        log_message('debug', "BlockedEvents: user=$userId, ts=$timestamp, start=$start, end=$end");

        $model = new BlockedDayModel();
        $dates = $model
            ->where('user_id', $userId)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->findAll();

        log_message('debug', "Found blocked days: " . count($dates));

        $events = [];
        foreach ($dates as $row) {
            $dateParts = explode('-', $row['date']);
            $events[] = [
                'eventName' => 'Blockiert',
                'eventDate' => $dateParts[0] . ', ' . $dateParts[1] . ', ' . $dateParts[2],
                'className' => 'event-red', // eigene CSS-Klasse
                'dateColor' => '#dc3545',
            ];
        }

        return $this->response->setJSON([
            'segment_month' => date('F Y', strtotime($date)),
            'styles' => '', // optional, falls du Styles dynamisch setzen willst
            'events' => $events,

            'csrf' => csrf_hash(),
        ]);
    }

}
