<?php
class SearchController {
    public function index(): void {
        $q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        if (strtolower($q) === 'search') {
            $q = '';
        }

        $serviceFilter = $this->normaliseFilter($_GET['service'] ?? 'all');
        $petFilter = $this->normaliseFilter($_GET['pet'] ?? 'all');
        $limit = max(1, min(200, (int)($_GET['per_page'] ?? 100)));

        $clinics = [];
        try {
            $mdl = new Clinic();
            $clinics = $mdl->search($q, $limit);
        } catch (Throwable $e) {
            $clinics = [];
        }

        $filtered = [];
        foreach ($clinics as $clinic) {
            $serviceSlugs = $this->splitAndSlug($clinic['service_categories'] ?? $clinic['services'] ?? '');
            $petSlugs = $this->splitAndSlug($clinic['pets'] ?? '');

            $serviceMatch = $this->matchesFilter($serviceFilter, $serviceSlugs, true);
            $petMatch = $this->matchesFilter($petFilter, $petSlugs, false);
            if ($serviceMatch && $petMatch) {
                $filtered[] = $clinic;
            }
        }

        $serviceOptions = [
            'all' => 'Tat ca',
            'kham-benh' => 'Kham benh',
            'tiem-phong' => 'Tiem phong',
            'spa' => 'Spa & Grooming',
            'khach-san' => 'Khach san',
            'phau-thuat' => 'Phau thuat',
            'khac' => 'Khac',
        ];

        $petOptions = [
            'all' => 'Tat ca',
            'cho' => 'Cho',
            'meo' => 'Meo',
            'khac' => 'Khac',
        ];

        view('search/index', [
            'title' => 'Tim kiem',
            'pageId' => 'clinic-search-page',
            'clinics' => $filtered,
            'q' => $q,
            'service' => $serviceFilter,
            'pet' => $petFilter,
            'serviceOptions' => $serviceOptions,
            'petOptions' => $petOptions,
            'resultsCount' => count($filtered),
        ]);
    }

    private function normaliseFilter($value): string {
        if (!is_string($value)) {
            return 'all';
        }
        $value = trim($value);
        if ($value === '') {
            return 'all';
        }
        $slug = $this->slugify($value);
        return $slug === '' ? 'all' : $slug;
    }

    private function splitAndSlug(?string $list): array {
        if ($list === null || $list === '') {
            return [];
        }
        $parts = array_filter(array_map('trim', explode(',', $list)), static function ($item) {
            return $item !== '';
        });
        $slugs = [];
        foreach ($parts as $item) {
            $slug = $this->slugify($item);
            if ($slug !== '') {
                $slugs[] = $slug;
            }
        }
        return $slugs;
    }

    private function matchesFilter(string $filter, array $values, bool $allowPartial): bool {
        if ($filter === 'all') {
            return true;
        }
        foreach ($values as $slug) {
            if ($slug === $filter) {
                return true;
            }
            if ($allowPartial && strpos($slug, $filter) !== false) {
                return true;
            }
        }
        return false;
    }

    private function slugify(string $value): string {
        $value = strtolower(trim($value));
        if ($value === '') {
            return '';
        }
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($converted) && $converted !== '') {
            $value = $converted;
        }
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }
}