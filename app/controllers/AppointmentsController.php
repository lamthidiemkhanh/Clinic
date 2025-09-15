<?php
class AppointmentsController {
    public function index(): void {
        $appts = [];
        try { $appts = (new Appointment())->recent(20); } catch (Throwable $e) { $appts = []; }
        view('appointments/index', [ 'title' => 'Lịch hẹn', 'pageId' => 'appointments-page', 'appts' => $appts ]);
    }
}
