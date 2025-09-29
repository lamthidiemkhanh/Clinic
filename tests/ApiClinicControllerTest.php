<?php
use PHPUnit\Framework\TestCase;
use App\Controllers\Api\Api_ClinicController;
use App\Models\Clinic;

class ApiClinicControllerTest extends TestCase
{
    public function testIndexReturnsJson()
    {
        // Mock model Clinic để không cần DB thật
        $clinicMock = $this->createMock(Clinic::class);
        $clinicMock->method('all')->willReturn([
            ['id' => 1, 'name' => 'Phòng khám A'],
            ['id' => 2, 'name' => 'Phòng khám B']
        ]);
        // Fake controller nhưng thay method createClinic() để trả mock
        $controller = $this->getMockBuilder(Api_ClinicController::class)
            ->onlyMethods(['createClinic'])
            ->getMock();
        $controller->method('createClinic')->willReturn($clinicMock);
        // Capture output do controller echo JSON
        ob_start();
        $controller->index();
        $output = ob_get_clean();
        // Kiểm tra output hợp lệ
        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertEquals('Phòng khám A', $data[0]['name']);
    }
}
