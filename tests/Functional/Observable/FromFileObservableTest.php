<?php

namespace Rx\React\Tests\Functional\Observable;

use React\EventLoop\LoopInterface;
use Rx\Observable;
use Rx\Observer\CallbackObserver;
use Rx\React\FromFileObservable;

class FromFileObservableTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function fromFile_basic()
    {
        /** @var LoopInterface $loop */
        $loop     = \EventLoop\getLoop();
        $source   = new FromFileObservable(__DIR__ . '/../test.txt');
        $result   = false;
        $complete = false;
        $error    = false;

        $source->subscribe(new CallbackObserver(
            function ($value) use (&$result) {
                $result = $value;
            },
            function ($e) use (&$error) {
                $error = true;
            },
            function () use (&$complete) {
                $complete = true;
            }
        ));


        $loop->tick();

        $this->assertEquals("1 2 3 4 5", $result);
        $this->assertTrue($complete);
        $this->assertFalse($error);

    }

    /**
     * @test
     */
    public function fromFile_missing_file()
    {
        /** @var LoopInterface $loop */
        $loop     = \EventLoop\getLoop();
        $source   = new FromFileObservable(__DIR__ . '/../nofile.txt');
        $result   = false;
        $complete = false;
        $error    = false;

        $source->subscribe(new CallbackObserver(
            function ($value) use (&$result) {
                $result = $value;
            },
            function ($e) use (&$error) {
                $error = true;
            },
            function () use (&$complete) {
                $complete = true;
            }
        ));


        $loop->tick();

        $this->assertFalse($result);
        $this->assertFalse($complete);
        $this->assertTrue($error);

    }

    /**
     * @test
     */
    public function fromFile_exceed_buffer()
    {
        //Create a 10k temp file
        $temp = tmpfile();
        fwrite($temp, str_repeat("1", 10000));
        $meta_data = stream_get_meta_data($temp);
        $filename  = $meta_data["uri"];

        /** @var LoopInterface $loop */
        $loop     = \EventLoop\getLoop();
        $source   = new FromFileObservable($filename);
        $result   = false;
        $complete = false;
        $error    = false;

        $source->subscribe(new CallbackObserver(
            function ($value) use (&$result) {
                $result = $value;
            },
            function ($e) use (&$error) {
                $error = true;
            },
            function () use (&$complete) {
                $complete = true;
            }
        ));


        $loop->tick();

        $this->assertEquals("4096", strlen($result));
        $this->assertFalse($complete);
        $this->assertFalse($error);


        $loop->tick();

        $this->assertEquals("4096", strlen($result));
        $this->assertFalse($complete);
        $this->assertFalse($error);


        $loop->tick();

        $this->assertEquals("1808", strlen($result));
        $this->assertTrue($complete);
        $this->assertFalse($error);
    }
}
