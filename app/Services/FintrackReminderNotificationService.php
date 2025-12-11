<?php

namespace App\Services;

use App\Models\BudgetGoal;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class FintrackReminderNotificationService
{
    public function __construct(
        protected OpenAiTextService $ai,
    ) {}

    /**
     * Reminder jika progress budget/goal sudah lewat 50%, 60%, dst.
     */
    public function buildBudgetGoalReminder(BudgetGoal $goal, float $progress): string
    {
        $systemPrompt = <<<SYS
Aku adalah asisten keuangan pribadi untuk aplikasi pencatatan keuangan bernama "FintracR".
Jawaban selalu dalam bahasa Indonesia, singkat (maksimal 2 kalimat), nada ramah tapi tegas.
Jangan gunakan emoji. Jangan pakai kata "OpenAI" atau "model".
SYS;

        $userPrompt = sprintf(
            'User punya %s dengan nama "%s" dengan target Rp %s. Progress saat ini %.1f%%. '
            . 'Buat pesan pengingat singkat agar user tetap disiplin dan menjelaskan risiko kalau melewati batas.',
            $goal->type === 'goal' ? 'goal tabungan' : 'budget pengeluaran',
            $goal->name,
            number_format($goal->target_amount ?? 0, 0, ',', '.'),
            $progress
        );

        return $this->ai->generateShortText($systemPrompt, $userPrompt, 160);
    }

    /**
     * Reminder transaksi harian jam 09:00 & 19:00.
     */
    public function buildDailyTransactionReminder(User $user, string $timeOfDay, bool $hasTodayTransactions): string
    {
        $systemPrompt = <<<SYS
Aku adalah asisten keuangan pribadi untuk aplikasi "FintracR".
Jawaban selalu dalam bahasa Indonesia, singkat (1–2 kalimat saja).
Nada ramah dan memotivasi, jangan menggurui berlebihan, jangan pakai emoji.
SYS;

        $today = Carbon::today()->format('d M Y');

        $status = $hasTodayTransactions
            ? 'User sudah mencatat setidaknya satu transaksi hari ini.'
            : 'User belum mencatat transaksi apa pun hari ini.';

        $userPrompt = sprintf(
            '%s Sekarang %s di zona waktu user (tanggal %s). '
            . 'Buat pesan reminder singkat yang mendorong user mencatat transaksi harian dengan disiplin.',
            $status,
            $timeOfDay === 'pagi' ? 'pagi hari' : 'malam hari',
            $today
        );

        return $this->ai->generateShortText($systemPrompt, $userPrompt, 120);
    }
}
