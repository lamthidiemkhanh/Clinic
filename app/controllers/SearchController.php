<?php
class SearchController {
    public function index(): void {
        $q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
        if (strtolower($q) === 'search') {
            $q = '';
        }

        $serviceFilter = $this->normaliseService($_GET['service'] ?? 'all');
        $limit = max(1, min(200, (int)($_GET['per_page'] ?? 100)));

        $filters = [
            'service' => $serviceFilter,
            'service_keywords' => $this->serviceKeywords()[$serviceFilter] ?? [],
        ];

        $clinics = [];
        try {
            $mdl = new Clinic();
            $result = $mdl->paginate(1, $limit, $q, $filters);
            $clinics = $result['data'];
        } catch (Throwable $e) {
            $clinics = [];
        }

        $filtered = $this->filterClinics($clinics, $q, $serviceFilter);
        $resultsCount = count($filtered);

        view('search/index', [
            'title' => 'Tìm kiếm',
            'pageId' => 'clinic-search-page',
            'clinics' => $filtered,
            'q' => $q,
            'service' => $serviceFilter,
            'serviceOptions' => $this->serviceOptions(),
            'resultsCount' => $resultsCount,
            'pagination' => [
                'page' => 1,
                'perPage' => $limit,
                'total' => $resultsCount,
                'pages' => 1,
            ],
        ]);
    }

    private function filterClinics(array $clinics, string $keyword, string $service): array
    {
        $keywordLower = mb_strtolower($keyword, 'UTF-8');
        $keywordSlug = $this->slugify($keyword);

        $serviceSlugs = [];
        if ($service !== 'all') {
            $serviceSlugs[] = $service;
            $serviceSlugs = array_merge($serviceSlugs, $this->serviceKeywords()[$service] ?? []);
            $serviceSlugs = array_values(array_unique(array_map([$this, 'slugify'], $serviceSlugs)));
        }

        $filtered = [];
        foreach ($clinics as $clinic) {
            $candidates = $this->collectSearchableValues($clinic);
            $matchesKeyword = $keywordLower === '' && $keywordSlug === ''
                ? true
                : $this->matchesKeyword($candidates, $keywordLower, $keywordSlug);

            if (!$matchesKeyword) {
                continue;
            }

            $matchesService = empty($serviceSlugs)
                ? true
                : $this->matchesAnyService($candidates, $serviceSlugs);

            if ($matchesService) {
                $filtered[] = $clinic;
            }
        }

        return $filtered;
    }

    private function collectSearchableValues(array $clinic): array
    {
        $fields = [
            $clinic['name'] ?? '',
            $clinic['description'] ?? '',
            $clinic['address'] ?? '',
            $clinic['service_categories'] ?? '',
            $clinic['services'] ?? '',
            $clinic['pets'] ?? '',
        ];

        $values = [];
        foreach ($fields as $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }
            $lower = mb_strtolower($value, 'UTF-8');
            $slug = $this->slugify($value);
            $values[] = [$lower, $slug];
        }
        return $values;
    }

    private function matchesKeyword(array $candidates, string $keywordLower, string $keywordSlug): bool
    {
        foreach ($candidates as [$lower, $slug]) {
            if ($keywordLower !== '' && mb_strpos($lower, $keywordLower) !== false) {
                return true;
            }
            if ($keywordSlug !== '' && $slug !== '' && strpos($slug, $keywordSlug) !== false) {
                return true;
            }
        }
        return false;
    }

    private function matchesAnyService(array $candidates, array $serviceSlugs): bool
    {
        foreach ($candidates as [, $slug]) {
            if ($slug === '') {
                continue;
            }
            foreach ($serviceSlugs as $needle) {
                if ($needle !== '' && strpos($slug, $needle) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    private function normaliseService($value): string
    {
        if (!is_string($value)) {
            return 'all';
        }
        $value = trim($value);
        if ($value === '') {
            return 'all';
        }
        $value = strtolower($value);
        $value = str_replace(['_', ' '], '-', $value);
        return preg_replace('/[^a-z-]/', '', $value) ?: 'all';
    }

    private function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $value = mb_strtolower($value, 'UTF-8');
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($converted) && $converted !== '') {
            $value = $converted;
        }
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
        return trim($value, '-');
    }

    private function serviceOptions(): array
    {
        return [
            'all' => 'Tất cả',
            'kham-benh' => 'Khám bệnh',
            'tiem-phong' => 'Tiêm phòng',
            'spa' => 'Spa & Grooming',
            'khach-san' => 'Khách sạn',
            'phau-thuat' => 'Phẫu thuật',
            'khac' => 'Khác',
        ];
    }

    private function serviceKeywords(): array
    {
        return [
            'kham-benh' => ['khám', 'kham', 'bệnh', 'benh', 'khám bệnh', 'kham benh'],
            'tiem-phong' => ['tiêm', 'tiem', 'phòng', 'phong', 'vaccine', 'chích', 'tiêm phòng', 'tiem phong'],
            'spa' => ['spa', 'groom', 'grooming'],
            'khach-san' => ['khách sạn', 'khach san', 'lưu trú', 'luu tru', 'hotel'],
            'phau-thuat' => ['phẫu thuật', 'phau thuat', 'surgery', 'phẫu'],
            'khac' => ['khác', 'khac'],
        ];
    }
}
