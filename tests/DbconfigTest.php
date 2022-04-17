<?php

class DbconfigTest extends \PHPUnit\Framework\TestCase {

    public function testAfterRemovedProductA(){
        $service = $this->createMock(Dbconfig::class);
        $result = $service->afterRemovedProductA(4);
        $this->assertEquals(60.00, $result);
    }
    
}