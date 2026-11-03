<?php

namespace App\Http\Middleware;

use App\Caching\PublicMenuCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicMenu
{
    public function __construct(private readonly PublicMenuCache $cache) {}

    public function handle(Request $request, Closure $next): Response
    {
        $version = $this->cache->version($request->getSchemeAndHttpHost());
        $cached = $this->cache->get($version);

        if ($cached !== null) {
            return $this->cachedResponse($request, $cached);
        }

        $response = $next($request);

        if ($response->getStatusCode() !== Response::HTTP_OK || ! is_string($response->getContent())) {
            return $response;
        }

        $html = $response->getContent();
        $etag = '"'.hash('sha256', $html).'"';
        $this->cache->put($version, $html, $etag);

        return $this->htmlResponse($html, $etag);
    }

    /**
     * @param  array{html: string, etag: string}  $cached
     */
    private function cachedResponse(Request $request, array $cached): Response
    {
        if ($this->matchesEtag($request->header('If-None-Match', ''), $cached['etag'])) {
            return response('', Response::HTTP_NOT_MODIFIED, $this->cacheHeaders($cached['etag']));
        }

        return $this->htmlResponse($cached['html'], $cached['etag']);
    }

    private function htmlResponse(string $html, string $etag): Response
    {
        return response($html, Response::HTTP_OK, $this->cacheHeaders($etag));
    }

    /**
     * @return array<string, string>
     */
    private function cacheHeaders(string $etag): array
    {
        return [
            'Cache-Control' => 'public, max-age=0, must-revalidate',
            'Content-Type' => 'text/html; charset=UTF-8',
            'ETag' => $etag,
        ];
    }

    private function matchesEtag(string $header, string $etag): bool
    {
        foreach (explode(',', $header) as $candidate) {
            $candidate = trim($candidate);

            if ($candidate === '*' || trim(str_starts_with($candidate, 'W/') ? substr($candidate, 2) : $candidate) === $etag) {
                return true;
            }
        }

        return false;
    }
}
