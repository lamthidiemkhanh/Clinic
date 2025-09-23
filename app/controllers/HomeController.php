<?php
class HomeController {
    public function index(): void {
        $clinics = [];

        try {
            $clinicModel = new Clinic();
            $clinics = $clinicModel->all(12); // lấy tối đa 12 phòng khám
        } catch (Throwable $e) {
            $clinics = [];
        }

        view('home/index', [
            'title'  => 'Trang chủ',
            'pageId' => 'home-page',
            'clinics'=> $clinics
        ]);
    }
}
