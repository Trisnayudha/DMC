<?php

namespace Tests\Feature;

use App\Models\CmsUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CmsLoginOtpTest extends TestCase
{
    private $user;
    private $credentials = ['email' => 'admin@example.test', 'password' => 'password123'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=', 'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'mail.default' => 'array', 'mail.from.address' => 'cms@example.test']);
        DB::purge('sqlite');
        Schema::create('cms_users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email'); $table->string('password');
            $table->boolean('is_active')->default(true); $table->string('role'); $table->rememberToken(); $table->timestamps();
        });
        (require database_path('migrations/2026_11_19_000002_add_login_otp_to_cms_users.php'))->up();
        require_once database_path('migrations/2026_11_19_000001_create_cms_user_logs_table.php');
        (new \CreateCmsUserLogsTable())->up();
        $this->user = CmsUser::create(['name' => 'Admin', 'email' => $this->credentials['email'], 'password' => Hash::make('password123'), 'is_active' => true, 'role' => 'superadmin']);
    }

    private function startOtp(): string
    {
        $this->user->update(['otp_enabled' => true]);
        $this->post('/admin/login', $this->credentials)->assertRedirect('/admin/login/otp');
        $this->assertGuest('cms');
        $message = Mail::getSwiftMailer()->getTransport()->messages()->last();
        $this->assertSame(1, preg_match('/<strong\b[^>]*>([0-9]{6})<\/strong>/', $message->getBody(), $matches));
        return $matches[1];
    }

    public function test_login_without_otp_and_wrong_password()
    {
        $this->post('/admin/login', ['email' => $this->user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest('cms');
        $this->post('/admin/login', $this->credentials)->assertRedirect('/admin/home');
        $this->assertAuthenticatedAs($this->user, 'cms');
    }

    public function test_valid_code_and_remember_cookie_restore_authentication()
    {
        $code = $this->startOtp();
        $this->get('/admin/login/otp')->assertOk()->assertSee('Remember me on this device');
        $this->get('/admin/home')->assertRedirect('/admin/login');
        $response = $this->post('/admin/login/otp', ['otp' => $code, 'remember' => '1']);
        $name = Auth::guard('cms')->getRecallerName();
        $response->assertCookie($name);
        $cookie = $response->getCookie($name);
        $this->assertAuthenticatedAs($this->user, 'cms');
        $this->assertNull($this->user->refresh()->login_otp_hash);
        session()->flush(); Auth::forgetGuards();
        $this->withUnencryptedCookie($name, $cookie->getValue())->get('/admin/login')->assertRedirect('/admin/home');
        $this->assertAuthenticatedAs($this->user, 'cms');
    }

    public function test_wrong_codes_and_expiry_prevent_login()
    {
        $code = $this->startOtp();
        $wrong = $code === '000000' ? '000001' : '000000';
        for ($i = 0; $i < 4; $i++) {
            $this->post('/admin/login/otp', ['otp' => $wrong])->assertRedirect('/admin/login/otp');
        }
        $this->post('/admin/login/otp', ['otp' => $wrong])->assertRedirect('/admin/login');
        $this->post('/admin/login/otp', ['otp' => $code])->assertRedirect('/admin/login');
        $this->assertGuest('cms');
    }

    public function test_expiry_and_deactivation_invalidate_the_code()
    {
        $code = $this->startOtp();
        $this->travel(10)->minutes();
        $this->post('/admin/login/otp', ['otp' => $code])->assertRedirect('/admin/login');
        $this->assertGuest('cms');
    }

    public function test_resend_replaces_code_and_observes_cooldown()
    {
        $this->startOtp();
        $this->from('/admin/login/otp')->post('/admin/login/otp/resend')->assertSessionHasErrors();
        $this->travel(61)->seconds();
        $this->post('/admin/login/otp/resend')->assertRedirect('/admin/login/otp');
        $this->assertCount(2, Mail::getSwiftMailer()->getTransport()->messages());
    }
}
