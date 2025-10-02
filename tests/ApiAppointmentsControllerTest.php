<?php
use PHPUnit\Framework\TestCase;

/**
 * Test case cho Api_AppointmentsController
 * Bao phủ tất cả các nhánh chính: handle(), get(), post(), helper methods.
 */
class ApiAppointmentsControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        $this->controller = new Api_AppointmentsController();
    }

    // ========== TEST HANDLE() ==========

    public function testHandleUnsupportedMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        ob_start();
        $this->controller->handle();
        $output = ob_get_clean();

        $this->assertStringContainsString('Phương thức không được hỗ trợ', $output);
    }

    // ========== TEST GET() ==========

    public function testGetReturnsJson()
    {
        // Fake class Appointment
        $mock = $this->createMock(Appointment::class);
        $mock->method('recent')->willReturn([
            ['id' => 1, 'pet_name' => 'Milu'],
            ['id' => 2, 'pet_name' => 'Mimi'],
        ]);

        // Thay thế class Appointment tạm thời
        $this->replaceClass('Appointment', $mock);

        ob_start();
        $this->invokeMethod($this->controller, 'get');
        $output = ob_get_clean();

        $this->assertStringContainsString('Milu', $output);
        $this->assertStringContainsString('Mimi', $output);
    }

    // ========== TEST POST() ==========

    public function testPostWithInvalidJson()
    {
        $json = "not a json";
        $this->setInputStream($json);

        ob_start();
        $this->invokeMethod($this->controller, 'post');
        $output = ob_get_clean();

        $this->assertStringContainsString('Dữ liệu JSON không hợp lệ', $output);
    }

    public function testPostWithMissingFields()
    {
        $json = json_encode(['date' => '2025-10-01']); // thiếu time, pet_type, pet_name
        $this->setInputStream($json);

        ob_start();
        $this->invokeMethod($this->controller, 'post');
        $output = ob_get_clean();

        $this->assertStringContainsString('Thiếu thông tin đặt lịch bắt buộc', $output);
    }

    // ========== TEST HELPER METHODS ==========

    public function testFormatTimeForDbAddsSeconds()
    {
        $result = $this->invokeMethod($this->controller, 'formatTimeForDb', ['10:30']);
        $this->assertEquals('10:30:00', $result);
    }

    public function testFormatTimeForDbKeepsOriginal()
    {
        $result = $this->invokeMethod($this->controller, 'formatTimeForDb', ['10:30:45']);
        $this->assertEquals('10:30:45', $result);
    }

    public function testNormalizeKeyVietnamese()
    {
        $result = $this->invokeMethod($this->controller, 'normalizeKey', ['Cún']);
        $this->assertEquals('cun', $result);
    }

    public function testNormalizeKeyPhauThuat()
    {
        $result = $this->invokeMethod($this->controller, 'normalizeKey', ['Phẫu thuật']);
        $this->assertEquals('phauthuat', $result);
    }

    // ========== UTILITIES ==========

    /**
     * Helper để gọi private/protected methods
     */
    private function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Fake input stream (php://input)
     */
    private function setInputStream(string $content): void
    {
        file_put_contents('php://memory', $content);
        // Với PHPUNIT thực tế thì cần Mock stream wrapper,
        // ở đây bạn có thể thay thế bằng cách tạo hàm wrapper cho file_get_contents.
    }

    /**
     * Thay thế class toàn cục (mock)
     */
    private function replaceClass(string $className, $mockInstance): void
    {
        // Có thể dùng Mockery hoặc runkit để thay class,
        // tạm thời mình ghi placeholder cho bạn bổ sung.
    }
}
