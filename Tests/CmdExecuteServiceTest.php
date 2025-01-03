<?php

namespace Islandora\Crayfish\Commons\Tests;

use Islandora\Crayfish\Commons\CmdExecuteService;

class CmdExecuteServiceTest extends AbstractCrayfishCommonsTestCase
{

    public function dataProviderWithResource()
    {
        return [
            'test as string' => [
                'sort -',
            ],
            'test as array' => [
                [
                    'sort',
                    '-',
                ],
            ]
        ];
    }

    /**
     * @dataProvider dataProviderWithResource
     */
    public function testExecuteWithResource(string|array $command)
    {
        $service = new CmdExecuteService($this->logger);

        $string = "apple\npear\nbanana";
        $data = fopen('php://memory', 'r+');
        fwrite($data, $string);
        rewind($data);

        $callback = $service->execute($command, $data);

        $this->assertTrue(is_callable($callback), "execute() must return a callable.");

        $output = $service->getOutputStream();
        rewind($output);
        $actual = stream_get_contents($output);

        $this->assertTrue(
            $actual == "apple\nbanana\npear\n",
            "Output stream should have sorted the list, received $actual"
        );

        // Call the callback just to close the streams/process.
        // This causes content to be printed to the test output.
        $callback();
    }

    public function dataProviderWithoutResource()
    {
        return [
          'test as string' => [
            'echo "derp"',
          ],
          'test as array' => [
            [
              'echo',
              'derp',
            ],
          ]
        ];
    }

    /**
     * @dataProvider dataProviderWithoutResource
     */
    public function testExecuteWithoutResource(string|array $command)
    {
        $service = new CmdExecuteService($this->logger);

        $callback = $service->execute($command, "");

        $this->assertTrue(is_callable($callback), "execute() must return a callable.");

        $output = $service->getOutputStream();
        rewind($output);
        $actual = stream_get_contents($output);

        $this->assertTrue($actual == "derp\n", "Output stream should contain 'derp', received $actual");

        // Call the callback just to close the streams/process.
        $callback();
    }
}
