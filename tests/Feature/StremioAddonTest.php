<?php

use function Pest\Laravel\getJson;

test('manifest exposes a single unified addon with all catalogs', function () {
    $response = getJson('/manifest.json');

    $response->assertOk();
    $response->assertJsonPath('id', 'com.yammbo.tv');
    $response->assertJsonPath('idPrefixes', ['tt', 'yammbo', 'comx']);

    $ids = array_column($response->json('catalogs'), 'id');

    expect($ids)->toContain('yammbo-movies-popular', 'comx-all-movies', 'comx-movie-28', 'yammbo-search');

    $search = collect($response->json('catalogs'))->firstWhere('id', 'yammbo-search');
    expect(collect($search['extra'])->pluck('name')->contains('search'))->toBeTrue();
});

test('unknown catalog returns empty metas', function () {
    getJson('/catalog/movie/does-not-exist.json')
        ->assertOk()
        ->assertJsonPath('metas', []);
});

test('comx catalog returns empty metas when no data dir is present', function () {
    getJson('/catalog/movie/comx-all-movies.json')
        ->assertOk()
        ->assertJsonPath('metas', []);
});
