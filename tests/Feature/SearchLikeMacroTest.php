<?php

use App\Models\Media;

test('searchLike builds portable case-insensitive sql across engines', function () {
    $sql = Media::query()->searchLike(['original_name', 'collection'], 'ConTRACT')->toSql();

    expect($sql)->toContain('lower(original_name) like lower(?)')
        ->and($sql)->toContain('lower(collection) like lower(?)')
        ->not->toContain('ilike');

    $bindings = Media::query()->searchLike(['original_name'], '  contract  ')->getBindings();

    expect($bindings)->toBe(['%contract%']);
});

test('searchLike groups or conditions inside a single where', function () {
    $query = Media::query()->where('size', '>', 10)->searchLike(['original_name'], 'pdf');

    $sql = $query->toSql();

    expect($sql)->toContain('and (lower(original_name) like lower(?)');
});
