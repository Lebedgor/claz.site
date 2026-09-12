<?php

use App\Support\ReadingTime;

it('estimates reading time from html', function () {
    $long = '<p>'.str_repeat('word ', 250).'</p>';

    expect(ReadingTime::estimate($long))->toBe(2)
        ->and(ReadingTime::estimate('<p>few words here</p>'))->toBe(1)
        ->and(ReadingTime::estimate(null))->toBe(1);
});
