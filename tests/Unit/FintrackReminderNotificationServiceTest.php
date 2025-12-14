<?php

namespace Tests\Unit;

use App\Models\BudgetGoal;
use App\Models\User;
use App\Services\FintrackReminderNotificationService;
use App\Services\NotificationAiService;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class FintrackReminderNotificationServiceTest extends TestCase
{
    protected FintrackReminderNotificationService $service;
    protected $mockAiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockAiService = Mockery::mock(NotificationAiService::class);
        $this->service = new FintrackReminderNotificationService($this->mockAiService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_builds_morning_daily_transaction_reminder_successfully(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('DAILY_REMINDER_MORNING', Mockery::on(function ($context) {
                return isset($context['user_name'])
                    && isset($context['has_transactions'])
                    && isset($context['waktu'])
                    && isset($context['date'])
                    && isset($context['unlogged_count']);
            }))
            ->andReturnUsing(function () {
                return [
                    'title' => 'Pagi bestie, catat dulu ☕',
                    'body' => 'Biar rapi sampai akhir bulan, isi transaksi hari ini ya. 30 detik doang, serius.',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildDailyTransactionReminder(
            $user,
            'pagi',
            false,
            0
        );

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('transaksi', $result);
    }

    /** @test */
    public function it_builds_evening_daily_transaction_reminder_successfully(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('DAILY_REMINDER_EVENING', Mockery::any())
            ->andReturnUsing(function () {
                return [
                    'title' => 'Daily wrap-up time 🌙',
                    'body' => 'Sebelum rebahan, input transaksi hari ini ya.',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildDailyTransactionReminder(
            $user,
            'malam',
            true,
            2
        );

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_falls_back_when_ai_service_fails_for_daily_reminder(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->andThrow(new \Exception('AI service failed'));

        Log::shouldReceive('warning')->once();

        $result = $this->service->buildDailyTransactionReminder(
            $user,
            'pagi',
            false
        );

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        // Should contain fallback message
        $this->assertStringContainsString('transaksi', $result);
    }

    /** @test */
    public function it_builds_budget_warning_50_reminder_successfully(): void
    {
        $budget = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'budget',
            'name' => 'Budget Makan',
            'target_amount' => 1000000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('BUDGET_WARNING_50', Mockery::on(function ($context) {
                return isset($context['budget_name'])
                    && isset($context['progress'])
                    && $context['budget_name'] === 'Budget Makan';
            }))
            ->andReturnUsing(function () {
                return [
                    'title' => 'Budget kamu udah setengah jalan 🧾',
                    'body' => 'Kamu udah pakai 50% dari Budget Makan.',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildBudgetGoalReminder($budget, 50.0);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_builds_budget_warning_above_50_reminder_successfully(): void
    {
        $budget = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'budget',
            'name' => 'Budget Transport',
            'target_amount' => 500000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('BUDGET_WARNING', Mockery::any())
            ->andReturnUsing(function () {
                return [
                    'title' => 'Budget mulai panas 🔥',
                    'body' => '70% udah kepake. Mode: hemat tipis-tipis.',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildBudgetGoalReminder($budget, 70.0);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_builds_goal_progress_50_reminder_successfully(): void
    {
        $goal = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'goal',
            'name' => 'Tabungan Liburan',
            'target_amount' => 10000000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('GOAL_PROGRESS_50', Mockery::any())
            ->andReturnUsing(function () {
                return [
                    'title' => 'Goal kamu udah 50% 🎯',
                    'body' => 'Mantap! Tabungan Liburan tinggal separuh lagi.',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildBudgetGoalReminder($goal, 50.0);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_builds_goal_progress_80_reminder_successfully(): void
    {
        $goal = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'goal',
            'name' => 'Tabungan Liburan',
            'target_amount' => 10000000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('GOAL_PROGRESS_80', Mockery::any())
            ->andReturnUsing(function () {
                return [
                    'title' => '80% bro… tinggal gas tipis 🚀',
                    'body' => 'Tabungan Liburan udah 80%.',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildBudgetGoalReminder($goal, 80.0);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_builds_goal_progress_90_reminder_successfully(): void
    {
        $goal = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'goal',
            'name' => 'Tabungan Liburan',
            'target_amount' => 10000000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('GOAL_PROGRESS_90', Mockery::any())
            ->andReturnUsing(function () {
                return [
                    'title' => '90%: ini udah depan mata 👀',
                    'body' => 'Tabungan Liburan udah 90%.',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildBudgetGoalReminder($goal, 90.0);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_builds_goal_completed_reminder_successfully(): void
    {
        $goal = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'goal',
            'name' => 'Tabungan Liburan',
            'target_amount' => 10000000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('GOAL_COMPLETED', Mockery::any())
            ->andReturnUsing(function () {
                return [
                    'title' => 'GOAL COMPLETED ✅🔥',
                    'body' => 'Tabungan Liburan tembus 100%!',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildBudgetGoalReminder($goal, 100.0);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('100%', $result);
    }

    /** @test */
    public function it_builds_goal_progress_below_50_reminder_successfully(): void
    {
        $goal = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'goal',
            'name' => 'Tabungan Liburan',
            'target_amount' => 10000000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('GOAL_PROGRESS', Mockery::any())
            ->andReturnUsing(function () {
                return [
                    'title' => 'Progress Update',
                    'body' => 'Tabungan Liburan udah 30%.',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildBudgetGoalReminder($goal, 30.0);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_falls_back_when_ai_service_fails_for_budget_goal_reminder(): void
    {
        $budget = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'budget',
            'name' => 'Budget Test',
            'target_amount' => 1000000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->andThrow(new \Exception('AI service failed'));

        Log::shouldReceive('warning')->once();

        $result = $this->service->buildBudgetGoalReminder($budget, 60.0);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        // Should contain fallback message
        $this->assertStringContainsString('Budget', $result);
    }

    /** @test */
    public function it_calculates_budget_context_correctly(): void
    {
        $budget = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'budget',
            'name' => 'Budget Test',
            'target_amount' => 1000000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('BUDGET_WARNING_50', Mockery::on(function ($context) {
                return $context['budget_name'] === 'Budget Test'
                    && $context['progress'] === 50.0
                    && $context['percent_used'] === 50.0
                    && $context['target_amount'] === 1000000
                    && $context['limit'] === 1000000
                    && $context['spent'] === 500000.0
                    && $context['remaining'] === 500000.0;
            }))
            ->andReturnUsing(function () {
                return [
                    'title' => 'Test',
                    'body' => 'Test',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildBudgetGoalReminder($budget, 50.0);

        // Assert that the service was called and returned a result
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /** @test */
    public function it_calculates_goal_context_correctly(): void
    {
        $goal = new BudgetGoal([
            'id' => 1,
            'user_id' => 1,
            'type' => 'goal',
            'name' => 'Goal Test',
            'target_amount' => 10000000,
        ]);

        $this->mockAiService
            ->shouldReceive('generate')
            ->once()
            ->with('GOAL_PROGRESS_50', Mockery::on(function ($context) {
                return $context['goal_name'] === 'Goal Test'
                    && $context['progress'] === 50.0
                    && $context['target_amount'] === 10000000
                    && $context['target'] === 10000000
                    && $context['current'] === 5000000.0
                    && $context['remaining'] === 5000000.0;
            }))
            ->andReturnUsing(function () {
                return [
                    'title' => 'Test',
                    'body' => 'Test',
                    'source' => 'ai',
                ];
            });

        $result = $this->service->buildBudgetGoalReminder($goal, 50.0);

        // Assert that the service was called and returned a result
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}

