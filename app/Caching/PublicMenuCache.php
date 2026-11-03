<?php

namespace App\Caching;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class PublicMenuCache
{
    private const VERSION_KEY = 'public-menu:version';

    private const HTML_KEY = 'public-menu:html:';

    public function version(): string
    {
        $store = $this->store();
        $version = $store->get(self::VERSION_KEY);

        if (! is_string($version)) {
            $version = (string) Str::uuid();
            $store->forever(self::VERSION_KEY, $version);
        }

        return $version;
    }

    /**
     * @return array{html: string, etag: string}|null
     */
    public function get(string $version): ?array
    {
        $entry = $this->store()->get(self::HTML_KEY.$version);

        if (! is_array($entry) || ! is_string($entry['html'] ?? null) || ! is_string($entry['etag'] ?? null)) {
            return null;
        }

        return $entry;
    }

    public function put(string $version, string $html, string $etag): void
    {
        $this->store()->forever(self::HTML_KEY.$version, ['html' => $html, 'etag' => $etag]);
    }

    public function invalidate(): void
    {
        $store = $this->store();
        $previousVersion = $store->get(self::VERSION_KEY);
        $store->forever(self::VERSION_KEY, (string) Str::uuid());

        if (is_string($previousVersion)) {
            $store->forget(self::HTML_KEY.$previousVersion);
        }
    }

    private function store(): Repository
    {
        return Cache::store('file');
    }
}
