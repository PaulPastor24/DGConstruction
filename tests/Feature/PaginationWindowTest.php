<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PaginationWindowTest extends TestCase
{
    public function test_admin_supervisor_and_client_pagination_render_only_three_page_numbers(): void
    {
        $views = [
            'vendor.pagination.bootstrap-5-limited',
            'vendor.pagination.inventory-compact',
        ];
        $cases = [
            1 => [1, 2, 3],
            30 => [29, 30, 31],
            100 => [98, 99, 100],
        ];

        foreach ($views as $viewName) {
            foreach ($cases as $currentPage => $expectedPages) {
                $paginator = new LengthAwarePaginator(
                    [],
                    1000,
                    10,
                    $currentPage,
                    ['path' => '/test']
                );
                $html = view($viewName, compact('paginator'))->render();

                foreach ($expectedPages as $page) {
                    if ($page === $currentPage) {
                        $this->assertStringContainsString('aria-current="page">'.$page.'</span>', $html);
                    } else {
                        $this->assertStringContainsString('aria-label="Page '.$page.'"', $html);
                    }
                }

                $this->assertSame(
                    $viewName === 'vendor.pagination.inventory-compact' ? 3 : 5,
                    substr_count($html, $viewName === 'vendor.pagination.inventory-compact'
                        ? 'class="page-item d-none d-sm-block'
                        : 'class="page-item')
                );
                $this->assertStringNotContainsString('aria-label="Page '.($expectedPages[0] - 1).'"', $html);
                $this->assertStringNotContainsString('aria-label="Page '.($expectedPages[2] + 1).'"', $html);
            }
        }
    }
}
