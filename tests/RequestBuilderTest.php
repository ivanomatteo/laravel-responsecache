<?php

namespace Spatie\ResponseCache\Test;

use Spatie\ResponseCache\CacheItemSelector\RequestBuilder;

class RequestBuilderTest extends TestCase
{

    /** @test */
    public function request_builder_works()
    {
        $uri = '/foo';
        $cookies = [
            'cookie1' => 'cookie1_value',
            'cookie2' => 'cookie2_value',
        ];
        $headers = [
            'Header1' => 'Header1_value',
            'Header2' => 'Header2_value',
        ];
        $parameters = [
            'Param1' => 'Param1_value',
            'Param2' => 'Param2_value',
        ];

        $cacheNameSuffix = 'suffix';

        $request = (new RequestBuilder)
            ->setParameters($parameters)
            ->setHeaders($headers)
            ->setCookies($cookies)
            ->setRemoteAddress('127.0.1.1')
            ->setCacheNameSuffix($cacheNameSuffix)
            ->build($uri);

        foreach ($parameters as $key => $value) {
            $this->assertEquals($request->query($key), $value);
        }
        foreach ($headers as $key => $value) {
            $this->assertEquals($request->header($key), $value);
        }
        foreach ($cookies as $key => $value) {
            $this->assertEquals($request->cookie($key), $value);
        }
        $this->assertEquals($request->getRequestUri(), $uri . '?' . http_build_query($parameters));
        $this->assertEquals($request->getMethod(), 'GET');
        $this->assertEquals($request->ip(), '127.0.1.1');
        $this->assertEquals($request->attributes->get('responsecache.cacheNameSuffix'), $cacheNameSuffix);


        $request = (new RequestBuilder)
            ->setMethod('POST')
            ->setParameters($parameters)
            ->setHeaders($headers)
            ->setCookies($cookies)
            ->setRemoteAddress('127.0.1.1')
            ->setCacheNameSuffix($cacheNameSuffix)
            ->build($uri);

        foreach ($parameters as $key => $value) {
            $this->assertEquals($request->input($key), $value);
        }
        foreach ($headers as $key => $value) {
            $this->assertEquals($request->header($key), $value);
        }
        foreach ($cookies as $key => $value) {
            $this->assertEquals($request->cookie($key), $value);
        }
        $this->assertEquals($request->getRequestUri(), $uri);
        $this->assertEquals($request->getMethod(), 'POST');
        $this->assertEquals($request->ip(), '127.0.1.1');
        $this->assertEquals($request->attributes->get('responsecache.cacheNameSuffix'), $cacheNameSuffix);
    }
}
