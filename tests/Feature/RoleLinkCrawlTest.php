<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * For every real (non-admin) role: a user of that role follows every link the application shows
 * them (menu, buttons, rows), two levels deep. A visible link must never answer "permission denied"
 * (403) or fail (500). GET only, so nothing is changed; rolled back anyway.
 */
class RoleLinkCrawlTest extends TestCase
{
    use DatabaseTransactions;

    /** Links not to follow: logout, downloads/prints of every row would only slow the crawl. */
    private function skip(string $path): bool
    {
        return $path === '' || str_starts_with($path, 'logout') || str_contains($path, 'resources/')
            || str_contains($path, '/excel') || str_starts_with($path, 'assets') || str_starts_with($path, 'storage');
    }

    private function links(string $html): array
    {
        preg_match_all('/<a\s[^>]*href="([^"#]+)"/i', $html, $m);
        $base = rtrim(url('/'), '/');
        $out = [];
        foreach ($m[1] as $href) {
            $href = html_entity_decode($href);
            if (! str_starts_with($href, $base)) {
                continue;
            }
            $path = ltrim(substr($href, strlen($base)), '/');
            if (! $this->skip($path)) {
                $out[] = $path;
            }
        }

        return array_values(array_unique($out));
    }

    public function test_no_visible_link_is_denied_for_any_real_role(): void
    {
        $roles = Role::where('id', '!=', User::ROLE_ADMIN)->get();
        $this->assertNotEmpty($roles);
        $report = [];

        foreach ($roles as $role) {
            $user = User::create(['name' => 'Crawler', 'email' => uniqid().'@test.local', 'password' => 'secret123', 'role_id' => $role->id]);
            $this->actingAs($user);

            $seen = [];
            $queue = [['dashboard', 0]];
            $perPrefix = [];
            while ($queue && count($seen) < 400) {
                [$path, $depth] = array_shift($queue);
                if (isset($seen[$path])) {
                    continue;
                }
                // follow at most 3 links per route shape (e.g. 3 customer pages, not all of them)
                $shape = preg_replace('/\d+/', '#', strtok($path, '?'));
                if (($perPrefix[$shape] = ($perPrefix[$shape] ?? 0) + 1) > 3) {
                    continue;
                }
                $seen[$path] = true;

                $response = $this->get('/'.$path);
                $status = $response->status();
                if (in_array($status, [403, 500], true)) {
                    $report[] = "{$role->name}: /{$path} -> {$status}";

                    continue;
                }
                if ($status === 200 && $depth < 2 && str_contains((string) $response->headers->get('Content-Type'), 'html')) {
                    foreach ($this->links((string) $response->getContent()) as $link) {
                        $queue[] = [$link, $depth + 1];
                    }
                }
            }
            fwrite(STDOUT, "\n  {$role->name}: ".count($seen).' pages followed');
        }

        $this->assertSame([], $report, "Visible links that fail:\n".implode("\n", $report));
    }
}
