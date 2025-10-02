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
    // 1. ID không hợp lệ
    public function testInvalidIdReturns400()
    {
        $_GET['id'] = 0;

        $controller = new Api_ClinicController();

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $this->assertJson($output);
        $data = json_decode($output, true);

        $this->assertEquals('Invalid clinic id', $data['error']);
        $this->assertEquals(400, http_response_code());
    }

    // 2. ID hợp lệ nhưng không tìm thấy clinic
    public function testNotFoundClinicReturns404()
    {
        $_GET['id'] = 999;

        // Mock model find() trả về null
        $clinicMock = $this->createMock(Clinic::class);
        $clinicMock->method('find')->willReturn(null);

        $controller = $this->getMockBuilder(Api_ClinicController::class)
            ->onlyMethods(['createClinic'])
            ->getMock();
        $controller->method('createClinic')->willReturn($clinicMock);

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->assertEquals('Clinic not found', $data['error']);
        $this->assertEquals(404, http_response_code());
    }

    // 3. Trả về phân trang
    public function testPaginationReturnsList()
    {
        unset($_GET['id']);
        $_GET['page_number'] = 1;
        $_GET['per_page'] = 2;

        $clinicMock = $this->createMock(Clinic::class);
        $clinicMock->method('paginate')->willReturn([
            ['id' => 1, 'name' => 'Phòng khám A'],
            ['id' => 2, 'name' => 'Phòng khám B']
        ]);

        $controller = $this->getMockBuilder(Api_ClinicController::class)
            ->onlyMethods(['createClinic'])
            ->getMock();
        $controller->method('createClinic')->willReturn($clinicMock);

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->assertCount(2, $data);
        $this->assertEquals('Phòng khám A', $data[0]['name']);
    }

    // 4. Xử lý exception
    public function testIndexHandlesException()
    {
        $controller = $this->getMockBuilder(Api_ClinicController::class)
            ->onlyMethods(['createClinic'])
            ->getMock();

        $controller->method('createClinic')->willThrowException(new \Exception("DB Error"));

        ob_start();
        $controller->index();
        $output = ob_get_clean();

        $this->assertJson($output);
        $data = json_decode($output, true);
        $this->assertArrayHasKey('error', $data);
    }
}


