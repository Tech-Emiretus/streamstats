<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Streams;
use Tests\TestCase;

class StreamsTest extends TestCase
{
    /** @test */
    public function can_get_the_appropriate_method_to_fetch_streams_when_user_id_is_not_provided()
    {
        $this->assertSame('getStreams', Streams::getFetchStreamsMethod());
    }

    /** @test */
    public function can_get_the_appropriate_method_to_fetch_streams_when_user_id_is_null()
    {
        $this->assertSame('getStreams', Streams::getFetchStreamsMethod(null));
    }

    /** @test */
    public function can_get_the_appropriate_method_to_fetch_streams_when_user_id_is_provided()
    {
        $this->assertSame('getFollowedStreams', Streams::getFetchStreamsMethod(1));
    }

    /**
     * @dataProvider fetchStreamArgsProvider
     * @test
     *
     * @param null|string $cursor
     * @param null|string|int $user_id
     * @param array $expected
     * @return void
     */
    public function gets_the_appropriate_arguments_to_fetch_streams($cursor, $user_id, array $expected)
    {
        $this->assertSame($expected, Streams::getFetchStreamsArgs($cursor, $user_id));
    }

    public function fetchStreamArgsProvider(): array
    {
        return [
            'Null cursor and null user id' => [
                null, null, [100, null]
            ],
            'Cursor and null user id' => [
                'next-cursor', null, [100, 'next-cursor']
            ],
            'Null Cursor and user id' => [
                null, 1, ['1', 100, null]
            ],
            'Cursor and user id' => [
                'next-cursor', 1, ['1', 100, 'next-cursor']
            ],
        ];
    }
}
