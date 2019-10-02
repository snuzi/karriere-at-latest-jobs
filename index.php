<?php

require __DIR__ . '/vendor/autoload.php';

use sabri\karriere\Karriere;

$params = [
    'page' => 1
];

$karriere = new Karriere();
$jobs = $karriere->getJobs($_GET);

printJobs($jobs);

function printJobs($jobs)
{
    echo '<b>Total: ' . count($jobs) . '</b> 
    <br> 
    <br>';

    $count = 1;
    foreach ($jobs as $item) {
        if (!isset($item['jobsItem'])) {
            continue;
        }

        $job = $item['jobsItem'];
        echo $count++ . ') '
            . '<a href="' . $job['link'] . '" target="_blank" >' . $job['title'] . '</a> <br>
        Company: <a href="' . $job['company']['link'] . '" target="_blank" >' . $job['company']['name'] . '</a>
        Posted: ' . $job['date'] . '
        <br>
        <br>';
    }
}
