<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ReportsExportRoutesTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a mock user without database queries
        // Set exists to true to prevent database queries
        $this->user = new User([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
            'email_verified_at' => now(),
        ]);
        $this->user->exists = true; // Prevent database queries
    }

    /** @test */
    public function export_cashflow_route_requires_authentication(): void
    {
        $response = $this->get('/admin/reports/export-cashflow');

        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function export_budget_goal_route_requires_authentication(): void
    {
        $response = $this->get('/admin/reports/export-budget-goal');

        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function export_daily_route_requires_authentication(): void
    {
        $response = $this->get('/admin/reports/export-daily');

        $response->assertRedirect('/admin/login');
    }

    /** @test */
    public function authenticated_user_can_access_export_cashflow_route(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/export-cashflow');

        // Should return PDF download (stream response)
        $response->assertStatus(200);
        // Stream download responses may have different content types
        $this->assertNotNull($response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function authenticated_user_can_access_export_budget_goal_route(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/export-budget-goal');

        // Should return PDF download (stream response)
        $response->assertStatus(200);
        $this->assertNotNull($response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function authenticated_user_can_access_export_daily_route(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/export-daily');

        // Should return PDF download (stream response)
        $response->assertStatus(200);
        $this->assertNotNull($response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function export_routes_accept_query_parameters(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/export-cashflow?from=2024-01-01&to=2024-01-31');

        $response->assertStatus(200);
        $this->assertNotNull($response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function export_cashflow_returns_correct_filename(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/export-cashflow');

        $response->assertStatus(200);

        // Check if Content-Disposition header contains the filename
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('cashflow-report', $contentDisposition);
        $this->assertStringContainsString('.pdf', $contentDisposition);
    }

    /** @test */
    public function export_budget_goal_returns_correct_filename(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/export-budget-goal');

        $response->assertStatus(200);

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('budget-goal-report', $contentDisposition);
        $this->assertStringContainsString('.pdf', $contentDisposition);
    }

    /** @test */
    public function export_daily_returns_correct_filename(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/export-daily?date=2024-01-15');

        $response->assertStatus(200);

        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('daily-report', $contentDisposition);
        $this->assertStringContainsString('.pdf', $contentDisposition);
    }

    /** @test */
    public function export_routes_are_registered_with_correct_names(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('filament.admin.pages.reports.export-cashflow')
        );

        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('filament.admin.pages.reports.export-budget-goal')
        );

        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('filament.admin.pages.reports.export-daily')
        );
    }

    /** @test */
    public function export_routes_use_get_method(): void
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();

        $cashflowRoute = $routes->getByName('filament.admin.pages.reports.export-cashflow');
        $this->assertNotNull($cashflowRoute);
        $this->assertContains('GET', $cashflowRoute->methods());

        $budgetGoalRoute = $routes->getByName('filament.admin.pages.reports.export-budget-goal');
        $this->assertNotNull($budgetGoalRoute);
        $this->assertContains('GET', $budgetGoalRoute->methods());

        $dailyRoute = $routes->getByName('filament.admin.pages.reports.export-daily');
        $this->assertNotNull($dailyRoute);
        $this->assertContains('GET', $dailyRoute->methods());
    }
}

