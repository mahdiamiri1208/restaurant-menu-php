<?php

namespace App\Caching;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;

final class PublicMenuCache
{
    private const VERSION_KEY = 'public-menu:version';

    private const HTML_KEY = 'public-menu:html:';

    private const ORIGINS_KEY = 'public-menu:origins';

    public function version(string $origin, string $path = '/menu'): string
    {
        $store = $this->store();
        $generation = $store->get(self::VERSION_KEY);

        if (! is_string($generation)) {
            $generation = (string) Str::uuid();
            $store->forever(self::VERSION_KEY, $generation);
        }

        $version = hash('sha256', implode("\0", [
            $origin,
            $path,
            $this->manifestFingerprint(),
            $this->serviceWorkerCacheVersion(),
            $generation,
        ]));
        $originKey = hash('sha256', $origin."\0".$path);
        $origins = $store->get(self::ORIGINS_KEY, []);

        if (! is_array($origins)) {
            $origins = [];
        }

        $previousVersion = $origins[$originKey] ?? null;

        if (is_string($previousVersion) && $previousVersion !== $version) {
            $store->forget(self::HTML_KEY.$previousVersion);
        }

        if ($previousVersion !== $version) {
            $origins[$originKey] = $version;
            $store->forever(self::ORIGINS_KEY, $origins);
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
        $origins = $store->get(self::ORIGINS_KEY, []);

        if (is_array($origins)) {
            foreach ($origins as $version) {
                if (is_string($version)) {
                    $store->forget(self::HTML_KEY.$version);
                }
            }
        }

        $store->forget(self::ORIGINS_KEY);
        $store->forever(self::VERSION_KEY, (string) Str::uuid());
    }

    private function manifestFingerprint(): string
    {
        $manifestPath = public_path('build/manifest.json');
        $fingerprint = hash_file('sha256', $manifestPath);

        if (! is_string($fingerprint)) {
            throw new RuntimeException('The Vite build manifest is missing or unreadable.');
        }

        return $fingerprint;
    }

    private function serviceWorkerCacheVersion(): string
    {
        $serviceWorker = file_get_contents(public_path('sw.js'));

        if (! is_string($serviceWorker) || ! preg_match("/^\\s*const\\s+CACHE_NAME\\s*=\\s*['\"]([^'\"]+)['\"];?\\s*$/m", $serviceWorker, $matches)) {
            throw new RuntimeException('The service worker cache version is missing or unreadable.');
        }

        return $matches[1];
    }

    private function store(): Repository
    {
        return Cache::store('file');
    }
}
