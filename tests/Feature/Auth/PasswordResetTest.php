<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Forgot Password');
    }

    public function test_otp_can_be_requested(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertRedirect(route('password.verify'));
        $response->assertSessionHas('reset_email', $user->email);

        Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_verify_otp_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['reset_email' => $user->email])
            ->get('/verify-otp');

        $response->assertStatus(200);
        $response->assertSee('Verify OTP');
    }

    public function test_otp_can_be_verified(): void
    {
        $user = User::factory()->create();
        
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->withSession(['reset_email' => $user->email])
            ->post('/verify-otp', ['otp' => '123456']);

        $response->assertRedirect(route('password.reset'));
        $response->assertSessionHas('otp_verified', true);
    }

    public function test_otp_verification_fails_with_invalid_otp(): void
    {
        $user = User::factory()->create();
        
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->withSession(['reset_email' => $user->email])
            ->post('/verify-otp', ['otp' => '000000']);

        $response->assertSessionHasErrors('otp');
    }

    public function test_reset_password_screen_restricted_without_otp_verification(): void
    {
        $response = $this->get('/reset-password');
        $response->assertRedirect(route('password.request'));
    }

    public function test_password_can_be_reset(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldPassword123'),
        ]);

        $response = $this->withSession([
            'reset_email' => $user->email,
            'otp_verified' => true,
        ])->post('/reset-password', [
            'password' => 'newPassword777',
            'password_confirmation' => 'newPassword777',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newPassword777', $user->password));

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_weak_password_reset_is_blocked(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldPassword123'),
        ]);

        // Weak because it is too short
        $response = $this->withSession([
            'reset_email' => $user->email,
            'otp_verified' => true,
        ])->post('/reset-password', [
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors('password');

        // Weak because it only contains numbers
        $response = $this->withSession([
            'reset_email' => $user->email,
            'otp_verified' => true,
        ])->post('/reset-password', [
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);

        $response->assertSessionHasErrors('password');

        // Weak because it contains no uppercase
        $response = $this->withSession([
            'reset_email' => $user->email,
            'otp_verified' => true,
        ])->post('/reset-password', [
            'password' => 'newpassword777',
            'password_confirmation' => 'newpassword777',
        ]);

        $response->assertSessionHasErrors('password');

        $user->refresh();
        $this->assertTrue(Hash::check('oldPassword123', $user->password));
    }

    public function test_otp_verification_fails_when_expired(): void
    {
        $user = User::factory()->create();
        
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(11), // 11 minutes ago, so past the 10-minute expiry
        ]);

        $response = $this->withSession(['reset_email' => $user->email])
            ->post('/verify-otp', ['otp' => '123456']);

        $response->assertSessionHasErrors('otp');
    }

    public function test_otp_can_be_resent(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        
        // Insert an initial old OTP
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('111111'),
            'created_at' => now()->subMinutes(5),
        ]);

        $response = $this->withSession(['reset_email' => $user->email])
            ->post('/resend-otp');

        $response->assertRedirect(route('password.verify'));
        $response->assertSessionHas('status', 'A new 6-digit OTP has been sent to your email address.');

        Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Retrieve the newly updated token record
        $tokenRecord = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($tokenRecord);
        $this->assertFalse(Hash::check('111111', $tokenRecord->token)); // Old OTP must be invalid/replaced
        $this->assertTrue(now()->diffInSeconds($tokenRecord->created_at) < 5); // Expiration timestamp must be fresh (now)
    }
}
