<?php
class BookingController {
    public function index(): void {
        view('booking/index', [
            'title' => 'Đặt lịch',
            'pageId' => 'booking-page',
        ]);
    }
}

