<?php

namespace Spatie\ResponseCache\Test;

use ResponseCache;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Illuminate\Http\Request;

class CacheCleanerIntegrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set(
            'responsecache.cache_profile',
            CacheSuccessfulGetAndPostRequests::class // declared at the ends of this file
        );
    }

    /** @test */
    public function it_will_cache_a_post_request()
    {
        $firstResponse = $this->call('POST', '/random');
        $secondResponse = $this->call('POST', '/random');

        $this->assertRegularResponse($firstResponse);
        $this->assertCachedResponse($secondResponse);

        $this->assertSameResponse($firstResponse, $secondResponse);
    }

    /** @test */
    public function it_can_forget_a_specific_cached_request_using_cache_cleaner()
    {
        config()->set('app.url', 'http://spatie.be');

        $firstResponse = $this->get('/random?foo=bar');
        $this->assertRegularResponse($firstResponse);

        ResponseCache::cacheCleaner()->setParameters(['foo' => 'bar'])->forget('/random');

        $secondResponse = $this->get('/random?foo=bar');
        $this->assertRegularResponse($secondResponse);

        $this->assertDifferentResponse($firstResponse, $secondResponse);
    }

    /** @test */
    public function it_can_forget_a_specific_cached_request_using_cache_cleaner_post()
    {
        config()->set('app.url', 'http://spatie.be');

        $firstResponse = $this->post('/random');
        $this->assertRegularResponse($firstResponse);

        ResponseCache::cacheCleaner()->setMethod('POST')->forget('/random');

        $secondResponse = $this->post('/random');
        $this->assertRegularResponse($secondResponse);

        $this->assertDifferentResponse($firstResponse, $secondResponse);
    }

    /** @test */
    public function it_can_forget_several_specific_cached_requests_at_once_using_cache_cleaner()
    {
        $firstResponseFirstCall = $this->get('/random/1?foo=bar');
        $this->assertRegularResponse($firstResponseFirstCall);

        $secondResponseFirstCall = $this->get('/random/2?foo=bar');
        $this->assertRegularResponse($secondResponseFirstCall);

        ResponseCache::cacheCleaner()->setParameters(['foo' => 'bar'])
            ->forget(['/random/1', '/random/2']);

        $firstResponseSecondCall = $this->get('/random/1?foo=bar');
        $this->assertRegularResponse($firstResponseSecondCall);

        $secondResponseSecondCall = $this->get('/random/2?foo=bar');
        $this->assertRegularResponse($secondResponseSecondCall);

        $this->assertDifferentResponse($firstResponseFirstCall, $firstResponseSecondCall);
        $this->assertDifferentResponse($secondResponseFirstCall, $secondResponseSecondCall);
    }
    /** @test */
    public function it_can_forget_several_specific_cached_requests_at_once_using_cache_cleaner_post()
    {
        $firstResponseFirstCall = $this->post('/random/1');
        $this->assertRegularResponse($firstResponseFirstCall);

        $secondResponseFirstCall = $this->post('/random/2');
        $this->assertRegularResponse($secondResponseFirstCall);

        ResponseCache::cacheCleaner()->setMethod('POST')->forget(['/random/1', '/random/2']);

        $firstResponseSecondCall = $this->post('/random/1');
        $this->assertRegularResponse($firstResponseSecondCall);

        $secondResponseSecondCall = $this->post('/random/2');
        $this->assertRegularResponse($secondResponseSecondCall);

        $this->assertDifferentResponse($firstResponseFirstCall, $firstResponseSecondCall);
        $this->assertDifferentResponse($secondResponseFirstCall, $secondResponseSecondCall);
    }


    /** @test */
    public function it_can_forget_a_specific_cached_request_using_cache_cleaner_suffix()
    {
        config()->set('app.url', 'http://spatie.be');

        $user_id = 1;

        $this->actingAs(User::findOrFail($user_id));
        $firstResponse = $this->get('/random?foo=bar');
        $this->assertRegularResponse($firstResponse);
        auth()->logout();

        ResponseCache::cacheCleaner()
            ->setParameters(['foo' => 'bar'])
            // BaseCacheProfile an user is logged in
            // use user id as suffix
            ->setCacheNameSuffix((string)$user_id)
            ->forget('/random');

        $this->actingAs(User::findOrFail(1));
        $secondResponse = $this->get('/random?foo=bar');
        auth()->logout();

        $this->assertRegularResponse($secondResponse);
        $this->assertDifferentResponse($firstResponse, $secondResponse);
    }
}


class CacheSuccessfulGetAndPostRequests extends CacheAllSuccessfulGetRequests
{
    public function shouldCacheRequest(Request $request): bool
    {
        if ($request->ajax()) {
            return false;
        }
        if ($this->isRunningInConsole()) {
            return false;
        }
        return $request->isMethod('get') || $request->isMethod('post');
    }
}
