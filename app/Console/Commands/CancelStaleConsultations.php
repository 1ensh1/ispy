<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelStaleConsultations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consultations:cancel-stale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-cancel Pending consultation bookings that are past their slot date or within 90 minutes of their slot time.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now      = Carbon::now();
        $today    = Carbon::today();
        $cutoff   = $now->copy()->addMinutes(90);
        $cancelled = 0;

        // LEFT JOIN for safety; pull only what we need to evaluate the rules.
        $bookings = DB::table('face_to_face_bookings as b')
            ->leftJoin('consultation_slots as s', 'b.slot_id', '=', 's.id')
            ->where('b.status', 'Pending')
            ->select(
                'b.id',
                'b.slot_id',
                's.scheduled_date',
                's.time_start'
            )
            ->get();

        foreach ($bookings as $booking) {
            // No slot means no date to evaluate against — skip.
            if (is_null($booking->slot_id) || is_null($booking->scheduled_date)) {
                continue;
            }

            $slotDate = Carbon::parse($booking->scheduled_date);
            $reason   = null;

            // Past-date rule: slot date is before today.
            if ($slotDate->lt($today)) {
                $reason = 'slot date has passed';
            }
            // 90-minute rule: slot is today AND its start time is less than 90 minutes away.
            elseif ($slotDate->isSameDay($today)) {
                $slotStart = Carbon::parse(
                    $slotDate->format('Y-m-d') . ' ' . $booking->time_start
                );

                if ($slotStart->lt($cutoff)) {
                    $reason = 'slot starts within 90 minutes';
                }
            }

            if (is_null($reason)) {
                continue;
            }

            // Status update only — never hard-delete, never touch consultation_slots.
            DB::table('face_to_face_bookings')
                ->where('id', $booking->id)
                ->update([
                    'status'     => 'Cancelled',
                    'updated_at' => $now,
                ]);

            // activity_logs has no updated_at — do not set it.
            DB::table('activity_logs')->insert([
                'user_id'     => null,
                'role'        => 'System',
                'action'      => 'Cancelled',
                'description' => "Booking #{$booking->id} cancelled automatically: {$reason}",
                'created_at'  => $now,
            ]);

            $cancelled++;
        }

        Log::info("consultations:cancel-stale cancelled {$cancelled} stale Pending booking(s).");
        $this->info("Cancelled {$cancelled} stale Pending booking(s).");

        return self::SUCCESS;
    }
}
