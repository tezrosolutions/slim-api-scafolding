<?php
class SyncTest extends LocalWebTestCase
{
    public function testHello()
    {
        $this->client->get('/hello/William');
        $this->assertEquals(200, $this->client->response->status());
        $this->assertSame('Hello, William', $this->client->response->body());
    }
}
/* End of file GetMethodTest.php */