<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->string('status')->default('pending_activation');
            $table->boolean('is_personal')->default(false);

            $table->boolean('is_organization')->default(false);

            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('profile_picture')->nullable();

            $table->string('cover_picture')->nullable();

            $table->text('bio')->nullable();

            $table->string('website')->nullable();

            $table->string('location')->nullable();

            $table->string('phone')->nullable();

            $table->string('gender')->nullable();

            $table->date('birthdate')->nullable();

            $table->boolean('is_banned')->default(false);

            $table->timestamp('banned_until')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        //oauths 
        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('client_id');
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_personal_access_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('client_id');
            $table->timestamps();
        });

        //jobs 
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        //companies 
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->text('note')->nullable();
            $table->string('status');
            $table->boolean('has_multiple_subscriptions')->default(false);
            $table->unsignedBigInteger('original_admin_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            if (Schema::hasTable('users')) {
                $table->foreign('original_admin_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }
        });

        Schema::create('company_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('email')->unique();
            $table->string('role')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });





        Schema::create('company_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreignId('user_id')->constrained('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->text('note')->collation('utf8mb4_unicode_ci');
            $table->timestamps();
        });


        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->collation('utf8mb4_unicode_ci');
            $table->string('email')->collation('utf8mb4_unicode_ci');
            $table->string('phone')->nullable()->collation('utf8mb4_unicode_ci');
            $table->text('address')->nullable()->collation('utf8mb4_unicode_ci');
            $table->timestamps();
            $table->unique('email', 'customers_email_unique');
        });


        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->date('invoice_date')->nullable(false);
            $table->date('due_date')->nullable(false);
            $table->decimal('total', 10, 2)->nullable(false);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->collation('utf8mb4_unicode_ci')->nullable(false);
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index('customer_id', 'invoices_customer_id_foreign');
        });


        Schema::create('note_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained('company_notes')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreignId('replier_id')->constrained('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->text('reply')->collation('utf8mb4_unicode_ci');
            $table->timestamps();
        });


        Schema::create('pivot_request_notes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });


        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->collation('utf8mb4_unicode_ci')->nullable(false);
            $table->string('product_key')
                ->unique()
                ->nullable();
            $table->text('description')->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('type')->collation('utf8mb4_unicode_ci')->nullable();
            $table->boolean('active')->default(1)->comment('Flag to determine if product is active or not');
            $table->decimal('price', 8, 2)->nullable(false);
            $table->unsignedInteger('quantity')->nullable(false);
            $table->boolean('is_subscription')->default(0)->comment('Flag to indicate if product is a subscription product.');
            $table->string('subscription_period')->collation('utf8mb4_unicode_ci')->nullable()->comment('Subscription period for the product. valid period values are: daily, weekly, monthly, yearly. Only valid for subscription products.');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->string('feature_name')->collation('utf8mb4_unicode_ci');
            $table->string('feature_value')->collation('utf8mb4_unicode_ci');
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->decimal('amount', 8, 2);
            $table->string('currency', 3)->default('MYR')->collation('utf8mb4_unicode_ci');
            $table->boolean('default_price')->default(0);
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->collation('utf8mb4_unicode_ci');
            $table->text('value')->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('type', 50)->collation('utf8mb4_unicode_ci');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->unique('key', 'site_settings_key_unique');
        });


        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_subscription_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->date('start_date')->nullable()->comment('Start date of the subscription.');
            $table->date('end_date')->nullable()->comment('End date of the subscription.');
            $table->string('name')->collation('utf8mb4_unicode_ci');
            $table->text('description')->nullable()->collation('utf8mb4_unicode_ci');
            $table->boolean('is_subscription_admin')->default(0);
            $table->enum('status', ['pending', 'active', 'cancelled', 'deactivated', 'suspended', 'expired'])->collation('utf8mb4_unicode_ci');
            $table->boolean('is_shared')->default(0);
            $table->boolean('is_split')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('parent_subscription_id')->references('id')->on('subscriptions')->onUpdate('NO ACTION')->onDelete('SET NULL');
            $table->index('parent_subscription_id');
        });


        Schema::create('companies_subscription', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreignId('subscription_id')->constrained('subscriptions')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->boolean('is_default')->default(0);
            $table->timestamps();
        });


        Schema::create('subscription_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->string('subscription_status')->nullable()->collation('utf8mb4_unicode_ci');
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('NO ACTION')->onDelete('SET NULL');
            $table->date('start_date')->nullable()->comment('Start date of the subscription.');
            $table->date('end_date')->nullable()->comment('End date of the subscription.');
            $table->enum('event_type', ['created', 'updated', 'status_changed', 'shared', 'unshared'])->collation('utf8mb4_unicode_ci');
            $table->text('description')->nullable()->collation('utf8mb4_unicode_ci');
            $table->timestamp('event_date')->useCurrent();
            $table->string('product_ids')->nullable()->collation('utf8mb4_unicode_ci');
            $table->boolean('multiple_products')->default(0);
            $table->timestamps();
        });


        Schema::create('subscription_product', function (Blueprint $table) {
            $table->foreignId('subscription_id')->constrained('subscriptions')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreignId('product_id')->constrained('products')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamp('added_at')->useCurrent();
            $table->unsignedInteger('usage_limit')->default(0);
            $table->unsignedInteger('usage_count')->default(0);
            $table->string('status')->default('active')->collation('utf8mb4_unicode_ci');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_tracked')->default(1);
            $table->timestamps();
            $table->primary(['subscription_id', 'product_id']);
        });


        Schema::create('subscription_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('NO ACTION')->onDelete('SET NULL');
            $table->string('product_list')->collation('utf8mb4_unicode_ci')->comment('List of products to subscribe to. comma separated list of product ids.');
            $table->string('subscription_period')->collation('utf8mb4_unicode_ci')->comment('Subscription period for the request. valid period values are: daily, weekly, monthly, yearly. Only valid for subscription products.');
            $table->string('name')->collation('utf8mb4_unicode_ci');
            $table->string('phone')->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('email')->collation('utf8mb4_unicode_ci');
            $table->string('organization_name')->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('organization_phone')->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('organization_address')->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('organization_website')->nullable()->collation('utf8mb4_unicode_ci');
            $table->text('organization_note')->nullable()->collation('utf8mb4_unicode_ci');
            $table->enum('status', ['pending', 'approved', 'rejected'])->collation('utf8mb4_unicode_ci');
            $table->timestamps();
        });


        Schema::create('subscription_request_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_request_id')->constrained('subscription_requests')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreignId('user_id')->constrained('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->text('note')->collation('utf8mb4_unicode_ci');
            $table->timestamps();
        });


        Schema::create('subscription_request_note_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('srn_id')->constrained('subscription_request_notes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreignId('replier_id')->constrained('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->text('reply')->collation('utf8mb4_unicode_ci');
            $table->timestamps();
        });


        Schema::create('subscription_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->mediumText('description')->nullable()->collation('utf8mb4_unicode_ci');
            $table->integer('usage');
            $table->date('date');
            $table->timestamps();
            $table->integer('limit')->default(0)->comment('0 means unlimited, otherwise limit of usage');
            $table->integer('original_limit')->default(0);
            $table->boolean('is_consumed')->default(false);
            $table->boolean('is_unlimited')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('date');
            $table->index('usage');
        });


        Schema::create('file_storage', function (Blueprint $table) {
            $table->bigIncrements('file_id');
            $table->unsignedBigInteger('user_id');
            $table->string('file_name');
            $table->text('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });


        Schema::create('pivot_user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subscription_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
        Schema::create('subscription_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('subscription_id');
            $table->timestamp('snapshot_date')->useCurrent();
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });

        Schema::create('subscription_snapshot_features', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('snapshot_id');
            $table->string('feature_name');
            $table->string('feature_value');
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('subscription_snapshots')->onDelete('cascade');
        });

        Schema::create('subscription_snapshot_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('snapshot_id');
            $table->string('product_name');
            $table->text('product_detail');
            $table->timestamps();

            $table->foreign('snapshot_id')->references('id')->on('subscription_snapshots')->onDelete('cascade');
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id');
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['credit_card', 'bank_transfer', 'cash', 'gateway'])->collation('utf8mb4_unicode_ci');
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->index('invoice_id', 'payments_invoice_id_foreign');
        });

        Schema::create('user_subscription_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('usage')->default(0);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('company_id');
            $table->index('subscription_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscription_usage');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscription_snapshot_products');
        Schema::dropIfExists('subscription_snapshot_features');
        Schema::dropIfExists('subscription_snapshots');
        Schema::dropIfExists('pivot_user_subscriptions');
        Schema::dropIfExists('file_storage');
        Schema::dropIfExists('subscription_usage');
        Schema::dropIfExists('subscription_request_note_replies');
        Schema::dropIfExists('subscription_request_notes');
        Schema::dropIfExists('subscription_requests');
        Schema::dropIfExists('subscription_product');
        Schema::dropIfExists('subscription_history');
        Schema::dropIfExists('companies_subscription');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('product_features');
        Schema::dropIfExists('products');
        Schema::dropIfExists('pivot_request_notes');
        Schema::dropIfExists('note_replies');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('company_notes');
        Schema::dropIfExists('company_members');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('oauth_personal_access_clients');
        Schema::dropIfExists('oauth_clients');
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_auth_codes');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
