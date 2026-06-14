<?php

use App\Models\Client;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hotel booking SaaS (white-label) domain.
 *
 * A "hotel" is a tenant of the booking engine. Each tenant owns its own
 * units / rates / extras / bookings and carries its own white-label branding,
 * tracking configuration and PMS (Casablanca) + payment (hobex) credentials.
 * Tenants are linked back to the existing agency CRM via clients.id so the
 * monthly SaaS subscription can be billed through the existing project tooling.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('onboarding');

            // Public presentation / contact
            $table->string('public_domain')->nullable();
            $table->string('location')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('support_email')->nullable();
            $table->string('locale', 5)->default('de');
            $table->string('currency', 3)->default('EUR');

            // White-label branding (colors, fonts, logo) kept as JSON so new
            // tokens can be added without a migration.
            $table->json('branding')->nullable();

            // Tracking config: { ga4_id, google_ads_id, google_ads_label,
            // meta_pixel_id, gtm_id, enhanced_conversions }.
            $table->json('tracking')->nullable();

            // Localised legal copy / policy URLs (AGB, Storno, Datenschutz).
            $table->json('legal')->nullable();

            // PMS + payment provider credentials. Secrets are encrypted at the
            // model layer (casts), never exposed to the browser.
            $table->json('pms_config')->nullable();
            $table->json('payment_config')->nullable();

            // Stay / pricing defaults
            $table->decimal('city_tax_per_person_night', 8, 2)->default(0);
            $table->decimal('insurance_rate', 5, 4)->default(0.055);
            $table->unsignedTinyInteger('tax_percent')->default(10);

            // Subscription / commercials
            $table->string('subscription_plan')->default('starter');
            $table->date('subscription_started_on')->nullable();
            $table->date('trial_ends_on')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('key'); // e.g. saver | flex
            $table->string('label');
            $table->string('sublabel')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('deposit_percent', 5, 2)->default(100);
            $table->json('rules')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['hotel_id', 'key']);
        });

        Schema::create('booking_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable(); // Casablanca roomTypeId
            $table->string('category')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('max_pax')->default(2);
            $table->string('size')->nullable();
            $table->string('rooms')->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('price_per_night', 10, 2)->default(0);
            $table->string('badge')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['hotel_id', 'slug']);
        });

        Schema::create('booking_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable(); // Casablanca articleId
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('pricing_unit')->default('once');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['hotel_id', 'slug']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('status')->default('draft');

            $table->foreignId('booking_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_rate_id')->nullable()->constrained()->nullOnDelete();

            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('nights')->default(1);
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->json('child_ages')->nullable();
            $table->string('board')->nullable();
            $table->string('package')->nullable();

            // Snapshot of selected extras + insurance flag at booking time.
            $table->json('extras')->nullable();
            $table->boolean('insurance')->default(false);

            // Guest data
            $table->json('guest')->nullable();

            // Money (server-calculated, source of truth)
            $table->decimal('units_total', 10, 2)->default(0);
            $table->decimal('extras_total', 10, 2)->default(0);
            $table->decimal('insurance_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->decimal('deposit_due', 10, 2)->default(0);
            $table->string('currency', 3)->default('EUR');

            // Payment + PMS linkage
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('pms_reservation_id')->nullable();

            // Attribution
            $table->string('session_id')->nullable();
            $table->string('locale', 5)->nullable();
            $table->json('attribution')->nullable(); // utm_*, gclid, referrer
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'status']);
        });

        Schema::create('booking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->index();
            $table->string('type');
            $table->unsignedTinyInteger('step')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->json('payload')->nullable();
            $table->json('attribution')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'type']);
            $table->index(['hotel_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_events');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('booking_extras');
        Schema::dropIfExists('booking_units');
        Schema::dropIfExists('booking_rates');
        Schema::dropIfExists('hotels');
    }
};
