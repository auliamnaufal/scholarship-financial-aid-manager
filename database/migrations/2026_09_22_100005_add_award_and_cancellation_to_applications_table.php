<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // What the coordinator promised on approval. The money still owed
            // is this minus the disbursements, worked out on read rather than
            // stored, so the two can never disagree.
            $table->decimal('awarded_amount', 12, 2)->nullable()->after('status');

            // The status carries `cancelled`; this records when, which the
            // status alone cannot.
            $table->timestamp('cancelled_at')->nullable()->after('awarded_amount');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['awarded_amount', 'cancelled_at']);
        });
    }
};
