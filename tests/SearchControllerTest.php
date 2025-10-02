<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/controllers/SearchController.php';

class SearchControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        $this->controller = new SearchController();
    }

    // ========== TEST INDEX() ==========

    public function testIndexWithNormalKeyword()
    {
        $_GET['q'] = 'Phòng khám thú y';
        $_GET['service'] = 'all';
        $_GET['per_page'] = 50;

        // Mock Clinic
        $mock = $this->getMockBuilder(Clinic::class)
                     ->onlyMethods(['paginate'])
                     ->getMock();
        $mock->method('paginate')->willReturn([
            'data' => [
                ['name' => 'Phòng khám thú y ABC', 'description' => 'Khám bệnh cho chó mèo']
            ]
        ]);

        $this->replaceClass('Clinic', $mock);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $this->assertStringContainsString('Tìm kiếm', $output);
        $this->assertStringContainsString('ABC', $output);
    }

    public function testIndexWithServiceFilter()
    {
        $_GET['q'] = '';
        $_GET['service'] = 'kham-benh';

        $mock = $this->getMockBuilder(Clinic::class)
                     ->onlyMethods(['paginate'])
                     ->getMock();
        $mock->method('paginate')->willReturn([
            'data' => [
                ['name' => 'PetCare', 'services' => 'Khám bệnh cho chó']
            ]
        ]);

        $this->replaceClass('Clinic', $mock);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $this->assertStringContainsString('PetCare', $output);
    }

    public function testIndexWithExceptionInModel()
    {
        $_GET['q'] = 'test';
        $_GET['service'] = 'all';

        $mock = $this->getMockBuilder(Clinic::class)
                     ->onlyMethods(['paginate'])
                     ->getMock();
        $mock->method('paginate')->willThrowException(new Exception("DB error"));

        $this->replaceClass('Clinic', $mock);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $this->assertStringContainsString('Tìm kiếm', $output);
    }

    // ========== TEST HELPER METHODS ==========

    public function testNormaliseService()
    {
        $result = $this->invokeMethod($this->controller, 'normaliseService', ['Khám bệnh']);
        $this->assertEquals('kham-benh', $result);

        $result2 = $this->invokeMethod($this->controller, 'normaliseService', ['']);
        $this->assertEquals('all', $result2);
    }

    public function testSlugify()
    {
        $result = $this->invokeMethod($this->controller, 'slugify', ['Phẫu thuật']);
        $this->assertEquals('phau-thuat', $result);

        $result2 = $this->invokeMethod($this->controller, 'slugify', ['Spa & Groom']);
        $this->assertEquals('spa-groom', $result2);
    }

    public function testTokenize()
    {
        $tokens = $this->invokeMethod($this->controller, 'tokenize', ['Khám bệnh chó mèo']);
        $this->assertContains('kham', $tokens);
        $this->assertContains('benh', $tokens);
        $this->assertContains('cho', $tokens);
    }

    public function testFilterClinicsByKeyword()
    {
        $clinics = [
            ['name' => 'Pet Hospital', 'description' => 'Khám bệnh cho thú cưng']
        ];
        $filtered = $this->invokeMethod(
            $this->controller,
            'filterClinics',
            [$clinics, 'khám', 'all']
        );

        $this->assertCount(1, $filtered);
    }

    // ========== UTILITIES ==========

    private function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    private function replaceClass(string $className, $mockInstance): void
    {
        // TODO: tuỳ theo cách autoload bạn có thể inject mock class,
        // ở đây mình chỉ placeholder. Khi chạy thực tế nên dùng DI (Dependency Injection).
    }
}
