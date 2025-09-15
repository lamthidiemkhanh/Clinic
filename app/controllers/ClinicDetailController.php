<?php
class ClinicDetailController {
    public function index(): void {
        view('clinic_detail/index', [ 'title' => 'Chi tiết phòng khám', 'pageId' => 'clinic-detail-page' ]);
    }
}

