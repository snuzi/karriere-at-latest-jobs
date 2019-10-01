<?php

namespace sabri\karriere;

use GuzzleHttp\Client;
use Exception;

class Karriere
{
    private $params = [];

    /** @var array required parameter rules */
    private $requiredParams = [
        'keywords' => 'string',
        'locations' => 'string',
        'jobFields' => 'array', // Should be array in parameter jobFields[]
        'employmentTypes' => 'array', // Should be array in parameter employmentTypes[]
        'states' => 'array' // Sshould be array in parameter states[]
    ];

    private $headers = [
        ':authority' => 'www.karriere.at',
        ':method' => 'GET',
        ':path' => '/jobs?keywords=php&locations=linz&page=3&jobFields%5B%5D=2172&employmentTypes%5B%5D=3960&states%5B%5D=2411',
        ':scheme' => 'https',
        'accept' => '*/*',
        'accept-encoding' => 'gzip, deflate, br',
        'accept-language ' => 'en,en-US;q=0.9,sq;q=0.8,de;q=0.7,und;q=0.6',
        'cache-control' => 'no-cache',
        'cookie' => 'K3_LTC=5cd138af87d04; jobahontas=excluded-v5; PHPSESSID=cdf02gr6uf37kicutgciflrov6; K3_CA=true; XSRF-TOKEN=eyJpdiI6IlwvTHhLRVJBOEdNNzhkclNnT1lWdGdRPT0iLCJ2YWx1ZSI6IkoxcXZBMXNJNmxtQXRFaXQwZ3l5alJwU2RIMGFZRmszRDdBY3hvWU9KQndydXdMdlErb09JaTlLUDJcL2hSU0IyIiwibWFjIjoiNTZmYWZhZTc5Mzc3OTlhNjIwN2FhM2IzMGI2MmIyNmZjZDIxODJkNzJmMzI1ZmY5NTZhMTliOGJhMTUzYjk5YSJ9; laravel_session=eyJpdiI6InRjUmpvR0J5RHJCelA1cm9mSTdJRXc9PSIsInZhbHVlIjoiV25SM1Z3WEFEeUVCWW1yWnYxNGwwZWlMaUJNK1JCeXdlUDRYY3FLaCt2ZkxaNzBjZGVUY0M5b3J3ak5rOXJNVSIsIm1hYyI6IjRhNzZjYTU3NDE1ZDU1YThkZjI0OTIxMWFlYTEyZGRjODE1NjZjNmU0MzZjOWViOTNjMmJjY2Q1NTBiNjQ3ODEifQ%3D%3D',
        'pragma: no-cache',
        'referer' => 'https://www.karriere.at/jobs/php/',
        'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.90 Safari/537.36',
        'x-requested-with' => 'XMLHttpRequest'
    ];

    /**
     * @param $parameters
     * @return array
     * @throws Exception
     */
    public function getJobs($parameters)
    {
        $params = $this->getParameters($parameters);

        $content = $this->getResult($params);

        $moreContent = [];

        while (isset($content['data']['jobsSearchList']['pagination'])
            && $content['data']['jobsSearchList']['pagination']['number'] < $content['data']['jobsSearchList']['pagination']['pages']
        ) {

            $params['page'] = $content['data']['jobsSearchList']['pagination']['next'];

            $moreContent = $this->getResult($params);

            if ($moreContent) {
                $content['data']['jobsSearchList']['pagination'] = $moreContent['data']['jobsSearchList']['pagination'];

                $content['data']['jobsSearchList']['activeItems']['items'] = array_merge(
                    $content['data']['jobsSearchList']['activeItems']['items'],
                    $moreContent['data']['jobsSearchList']['activeItems']['items']
                );

            } else {
                break;
            }
        }

        return $this->sortJobs($content['data']['jobsSearchList']['activeItems']['items']);
    }

    /**
     * @param $params
     * @return array
     * @throws \Exception
     */
    private function getParameters($params)
    {
        $parameters = [
            'page' => 1
        ];

        foreach ($this->requiredParams as $key => $type) {
            if (!isset($params[$key])) {
                throw new Exception(
                    'Error: these query prameters are required ' . implode(",", $this->requiredParams)
                );
            }

            if ($type == 'array') {
                $parameters[$key . '[]'] = implode(',', $params[$key]);
            } else {
                $parameters[$key] = $_GET[$key];
            }
        }

        return $parameters;
    }

    /**
     * @param $params
     * @return mixed
     */
    function getResult($params)
    {
        $client = new Client([
            'base_uri' => 'https://www.karriere.at',
        ]);

        $response = $client->request('GET', '/jobs', [
            'query' => $params,
            'headers' => $this->headers,
        ]);

        $content = json_decode($response->getBody()->getContents(), true);

        return $content;
    }

    /**
     * @param $jobs
     * @return array
     */
    function sortJobs($jobs)
    {
        $jobsArray = [];

        foreach ($jobs as $item) {
            if (!isset($item['jobsItem'])) {
                continue;
            }

            $origDate = str_replace('am ', '', $item['jobsItem']['date']);
            $splitedDate = explode('.', $origDate);

            $month = $splitedDate[1] < 10 ? '0' . $splitedDate[1] : $splitedDate[1];
            $date = $splitedDate[2] . '-' . $month . '-' . $splitedDate[0];
            $item['jobsItem']['date'] = $origDate;

            $item['jobsItem']['timestamp'] = strtotime($date);
            $jobsArray[] = $item;
        }

        usort($jobsArray, function ($a, $b) {
            if ($a['jobsItem']['timestamp'] == $b['jobsItem']['timestamp']) {
                return 0;
            }
            return ($a['jobsItem']['timestamp'] < $b['jobsItem']['timestamp']) ? 1 : -1;
        });

        return $jobsArray;
    }
}