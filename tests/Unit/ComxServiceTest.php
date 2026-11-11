<?php

use App\Services\ComxService;
use App\Services\TmdbService;

function comxFixtureDir(): string
{
    $dir = sys_get_temp_dir().'/comx_'.bin2hex(random_bytes(6));
    mkdir($dir, 0777, true);

    $pdo = new PDO('sqlite:'.$dir.'/cuevana_peliculas.db');
    $pdo->exec('CREATE TABLE peliculas (id_tmdb TEXT PRIMARY KEY, titulo TEXT)');
    $pdo->exec('CREATE TABLE enlaces (id INTEGER PRIMARY KEY AUTOINCREMENT, id_tmdb TEXT, idioma TEXT, servidor TEXT, url TEXT)');
    $pdo->exec("INSERT INTO peliculas VALUES ('603','Matrix'),('550','Fight Club')");
    $pdo->exec("INSERT INTO enlaces (id_tmdb, idioma, servidor, url) VALUES ('603','latino','Dood','https://doodstream.com/e/abc')");

    $pdo = new PDO('sqlite:'.$dir.'/cuevana_series.db');
    $pdo->exec('CREATE TABLE series (id_tmdb TEXT PRIMARY KEY, titulo TEXT)');
    $pdo->exec('CREATE TABLE temporadas (id INTEGER PRIMARY KEY AUTOINCREMENT, serie_tmdb TEXT, numero INTEGER)');
    $pdo->exec('CREATE TABLE episodios (id INTEGER PRIMARY KEY AUTOINCREMENT, temporada_id INTEGER, numero INTEGER, titulo TEXT)');
    $pdo->exec('CREATE TABLE enlaces (id INTEGER PRIMARY KEY AUTOINCREMENT, episodio_id INTEGER, idioma TEXT, servidor TEXT, url TEXT)');
    $pdo->exec("INSERT INTO series VALUES ('1396','Breaking Bad')");
    $pdo->exec("INSERT INTO temporadas (serie_tmdb, numero) VALUES ('1396',1)");
    $pdo->exec("INSERT INTO episodios (temporada_id, numero, titulo) VALUES (1,1,'Pilot'),(1,2,'Cat')");
    $pdo->exec("INSERT INTO enlaces (episodio_id, idioma, servidor, url) VALUES (1,'latino','Voe','https://voe.sx/e/xyz')");

    return $dir;
}

function comxMeta(array $overrides = []): array
{
    return array_merge([
        'id' => 603,
        'title' => 'The Matrix',
        'poster_path' => '/m.jpg',
        'backdrop_path' => '/b.jpg',
        'overview' => 'Neo discovers the truth.',
        'vote_average' => 8.7,
        'release_date' => '1999-03-30',
        'genre_ids' => [28, 878],
    ], $overrides);
}

test('meta and episode ids are parsed', function () {
    $tmdb = $this->createMock(TmdbService::class);
    $svc = new ComxService($tmdb, comxFixtureDir());

    expect($svc->metaTmdbId('comx:603'))->toBe('603')
        ->and($svc->metaTmdbId('tt1234567'))->toBeNull()
        ->and($svc->seriesEpisodeId('comx:1396:1:1'))->toBe(['1396', 1, 1])
        ->and($svc->seriesEpisodeId('comx:1396'))->toBeNull();
});

test('series videos list only episodes that have a link', function () {
    $tmdb = $this->createMock(TmdbService::class);
    $svc = new ComxService($tmdb, comxFixtureDir());

    $videos = $svc->seriesVideos('1396');

    expect($videos)->toHaveCount(1);
    expect($videos[0])->toMatchArray([
        'id' => 'comx:1396:1:1',
        'season' => 1,
        'episode' => 1,
        'title' => 'Pilot',
    ]);
});

test('movie streams include the aggregator and the repo links', function () {
    $tmdb = $this->createMock(TmdbService::class);
    $svc = new ComxService($tmdb, comxFixtureDir());

    $streams = $svc->streams('movie', 'comx:603');

    expect($streams)->toHaveCount(2);
    expect($streams[0]['name'])->toBe('MovieMaster (HD)');
    expect($streams[1])->toMatchArray([
        'name' => 'Dood (latino)',
        'externalUrl' => 'https://doodstream.com/e/abc',
    ]);
    expect($streams[1]['behaviorHints']['yammboEmbed'])->toBeTrue();
});

test('search metas cross tmdb results with the repo', function () {
    $tmdb = $this->createMock(TmdbService::class);
    $tmdb->method('search')->willReturn([
        'results' => [
            comxMeta(),
            comxMeta(['id' => 999, 'title' => 'Not In Repo', 'poster_path' => '/n.jpg']),
        ],
    ]);
    $svc = new ComxService($tmdb, comxFixtureDir());

    $metas = $svc->searchMetas('movie', 'matrix');

    expect($metas)->toHaveCount(1);
    expect($metas[0]['id'])->toBe('comx:603');
    expect($metas[0]['poster'])->toBe('https://image.tmdb.org/t/p/w500/m.jpg');
});

test('all metas list every repo id, newest first', function () {
    $tmdb = $this->createMock(TmdbService::class);
    $tmdb->method('movie')->willReturn([
        'title' => 'X',
        'poster_path' => '/m.jpg',
        'overview' => 'x',
        'vote_average' => 7.0,
        'release_date' => '2000-01-01',
        'genres' => [['id' => 28, 'name' => 'Action']],
    ]);
    $svc = new ComxService($tmdb, comxFixtureDir());

    $metas = $svc->allMetas('movie', 0);

    expect($metas)->toHaveCount(2);
    expect($metas[0]['id'])->toBe('comx:603');
    expect($metas[1]['id'])->toBe('comx:550');
});

test('genre metas cross discover results with the repo', function () {
    $tmdb = $this->createMock(TmdbService::class);
    $tmdb->method('discover')->willReturn([
        'results' => [
            comxMeta(),
            comxMeta(['id' => 999, 'title' => 'Not In Repo']),
        ],
    ]);
    $svc = new ComxService($tmdb, comxFixtureDir());

    $metas = $svc->genreMetas('movie', 28, 0);

    expect($metas)->toHaveCount(1);
    expect($metas[0]['id'])->toBe('comx:603');
});
