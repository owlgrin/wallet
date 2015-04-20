<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

	/**
	 * Creates the application.
	 *
	 * @return \Symfony\Component\HttpKernel\HttpKernelInterface
	 */
	public function createApplication()
	{
		$unitTesting = true;

		$testEnvironment = 'test';

		return require __DIR__.'/../../../../bootstrap/start.php';
	}

	public function testSomethingIsTrue()
    {
        $this->assertTrue(true);
    }

}
