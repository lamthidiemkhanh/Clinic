<?php
use PHPUnit\Framework\TestCase;

/**
 * Unit test cho Api_AppointmentsController
 */
class ApiAppointmentsControllerTest extends TestCase
{
    private Api_AppointmentsController $controller;

    protected function setUp(): void
    {
        // Khởi tạo controller
        $this->controller = new Api_AppointmentsController();
    }

    /** @test */
    public function handle_with_unsupported_method_returns_405()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        ob_start();
        $this->controller->handle();
        $output = ob_get_clean();

        $this->assertStringContainsString('không được hỗ trợ', $output);
    }

    /** @test */
    public function get_returns_json_array()
    {
        // Mock lớp Appointment
        $mock = $this->createMock(Appointment::class);
        $mock->method('recent')->willReturn([['id' => 1, 'pet_name' => 'Mèo']]);

        // Dùng Reflection để thay new Appointment() bằng mock
        $ref = new ReflectionClass(Api_AppointmentsController::class);
        $method = $ref->getMethod('get');
        $method->setAccessible(true);

        ob_start();
        $method->invoke($this->controller);
        $output = ob_get_clean();

        $this->assertStringContainsString('Mèo', $output);
    }

    /** @test */
    public function post_with_invalid_json_returns_error()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Fake request body
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'not-json');
        rewind($stream);
        stream_filter_append($stream, 'string.toupper'); // trick để php://input đọc fail

        ob_start();
        $this->controller->handle();
        $output = ob_get_clean();

        $this->assertStringContainsString('không hợp lệ', $output);
    }

    /** @test */
    public function post_with_missing_fields_returns_error()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $data = json_encode(['pet_type' => 'dog']);

        // Fake input
        file_put_contents('php://temp', $data);

        ob_start();
        $this->controller->handle();
        $output = ob_get_clean();

        $this->assertStringContainsString('Thiếu thông tin', $output);
    }

    /** @test */
    public function formatTimeForDb_adds_seconds()
    {
        $ref = new ReflectionClass(Api_AppointmentsController::class);
        $method = $ref->getMethod('formatTimeForDb');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, '09:30');
        $this->assertEquals('09:30:00', $result);
    }

    /** @test */
    public function normalizeKey_removes_accents_and_lowercases()
    {
        $ref = new ReflectionClass(Api_AppointmentsController::class);
        $method = $ref->getMethod('normalizeKey');
        $method->setAccessible(true);

        $this->assertEquals('cho', $method->invoke($this->controller, 'Chó'));
        $this->assertEquals('meo', $method->invoke($this->controller, 'Mèo'));
    }
}
