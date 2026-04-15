<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProfileSlowApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.profile_api_requests', false)) {
            return $next($request);
        }

        $startedAt = microtime(true);
        $queries = [];

        DB::listen(function (QueryExecuted $query) use (&$queries): void {
            $queries[] = [
                'time_ms' => round($query->time, 2),
                'sql' => $this->normalizeSql($query->sql),
            ];
        });

        $response = $next($request);
        $durationMs = round((microtime(true) - $startedAt) * 1000, 2);
        $thresholdMs = (float) config('app.profile_api_requests_threshold_ms', 1000);

        if ($durationMs < $thresholdMs) {
            return $response;
        }

        $dbTimeMs = round(collect($queries)->sum('time_ms'), 2);
        $topQueries = collect($queries)
            ->sortByDesc('time_ms')
            ->take(max(1, (int) config('app.profile_api_requests_top_queries', 5)))
            ->values()
            ->all();

        Log::channel('performance')->warning('Slow API request detected', [
            'method' => $request->method(),
            'path' => '/' . ltrim($request->path(), '/'),
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'db_time_ms' => $dbTimeMs,
            'query_count' => count($queries),
            'top_queries' => $topQueries,
        ]);

        $response->headers->set('X-Profile-Duration-Ms', (string) $durationMs);
        $response->headers->set('X-Profile-Db-Time-Ms', (string) $dbTimeMs);
        $response->headers->set('X-Profile-Query-Count', (string) count($queries));

        return $response;
    }

    private function normalizeSql(string $sql): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($sql)) ?? trim($sql);

        return mb_strimwidth($normalized, 0, 500, '...');
    }
}
