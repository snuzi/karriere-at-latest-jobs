<?php


namespace sabri\karriere\tests;

use PHPUnit\Framework\TestCase;
use sabri\karriere\Karriere;

class KarriereTest extends TestCase
{
    public function testGetJobs()
    {
        $params = [
            'keywords' => 'java',
            'locations' => 'linz',
            'jobFields' => [2172],
            'employmentTypes' => [3960],
            'states' => [2411]
        ];

        $karriere = new Karriere();
        $jobs = $karriere->getJobs($params);

        $this->assertArrayHasKey('jobsItem', $jobs[0]);
    }
}
