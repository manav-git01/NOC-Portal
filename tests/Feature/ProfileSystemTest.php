<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $faculty;
    protected Role $studentRole;
    protected Role $facultyRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->studentRole = Role::where('name', 'student')->first();
        $this->facultyRole = Role::where('name', 'faculty')->first();

        $this->student = User::create([
            'name' => 'Manav Badiyani',
            'email' => 'manav@charusat.edu.in',
            'password' => bcrypt('password123'),
            'role_id' => $this->studentRole->id,
            'enrollment_number' => '23IT001',
            'department' => 'IT',
            'semester' => 6,
            'phone' => '9876543210',
        ]);

        $this->faculty = User::create([
            'name' => 'Bimal Patel',
            'email' => 'bimalpatel@charusat.ac.in',
            'password' => bcrypt('password123'),
            'role_id' => $this->facultyRole->id,
            'faculty_id' => 'FAC001',
            'department' => 'IT',
            'phone' => '9876543211',
        ]);
    }

    /**
     * Test initials_avatar attribute on User model.
     */
    public function test_initials_avatar_attribute(): void
    {
        $this->assertEquals('MB', $this->student->initials_avatar);
        $this->assertEquals('BP', $this->faculty->initials_avatar);

        $userSingleName = User::factory()->make(['name' => 'Admin']);
        $this->assertEquals('A', $userSingleName->initials_avatar);

        $userThreeNames = User::factory()->make(['name' => 'First Middle Last']);
        $this->assertEquals('FM', $userThreeNames->initials_avatar);
    }

    /**
     * Test settings page is displayed and rendered with correct elements.
     */
    public function test_profile_settings_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->student)->get(route('profile.settings'));
        $response->assertOk();
        $response->assertSee('Profile Settings');
        $response->assertSee($this->student->name);
        $response->assertSee($this->student->enrollment_number);
        // Navbar display name & role
        $response->assertSee('Manav Badiyani');
        $response->assertSee('Student');
    }

    /**
     * Test updating profile settings successfully.
     */
    public function test_profile_settings_can_be_updated(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('profile.png');

        $response = $this->actingAs($this->student)->put(route('profile.settings.update'), [
            'name' => 'Manav Patel Badiyani',
            'email' => 'manav.new@charusat.edu.in',
            'phone' => '1112223330',
            'department' => 'CSE',
            'semester' => 7,
            'profile_photo' => $photo,
        ]);

        $response->assertRedirect(route('profile.settings'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->student->refresh();

        $this->assertEquals('Manav Patel Badiyani', $this->student->name);
        $this->assertEquals('manav.new@charusat.edu.in', $this->student->email);
        $this->assertEquals('1112223330', $this->student->phone);
        $this->assertEquals('CSE', $this->student->department);
        $this->assertEquals(7, $this->student->semester);
        $this->assertNotNull($this->student->profile_photo_path);

        // Verify photo exists in storage
        Storage::disk('public')->assertExists($this->student->profile_photo_path);

        // Verify Audit Log
        $log = AuditLog::where('action', 'Profile Updated')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Manav Patel Badiyani (Student)', $log->admin_name);
        $this->assertStringContainsString('updated their profile details', $log->target);
    }

    /**
     * Test read-only fields protection on profile settings update.
     */
    public function test_read_only_fields_are_protected_from_modification(): void
    {
        $response = $this->actingAs($this->student)->put(route('profile.settings.update'), [
            'name' => 'Manav Badiyani',
            'email' => 'manav@charusat.edu.in',
            'phone' => '9876543210',
            'department' => 'IT',
            'semester' => 6,
            // Attempt to spoof read-only values
            'id' => 999,
            'enrollment_number' => 'SPOOFED',
            'role_id' => $this->facultyRole->id,
            'authority_type' => 'noc_authority',
        ]);

        $this->student->refresh();

        // Ensure primary keys, enrollment numbers and roles remain unaffected
        $this->assertNotEquals(999, $this->student->id);
        $this->assertEquals('23IT001', $this->student->enrollment_number);
        $this->assertEquals($this->studentRole->id, $this->student->role_id);
    }

    /**
     * Test profile photo removal works correctly.
     */
    public function test_profile_photo_can_be_removed(): void
    {
        Storage::fake('public');

        // 1. Upload photo first
        $photo = UploadedFile::fake()->image('avatar.jpg');
        $this->actingAs($this->student)->put(route('profile.settings.update'), [
            'name' => 'Manav Badiyani',
            'email' => 'manav@charusat.edu.in',
            'phone' => '9876543210',
            'department' => 'IT',
            'semester' => 6,
            'profile_photo' => $photo,
        ]);

        $this->student->refresh();
        $filePath = $this->student->profile_photo_path;
        $this->assertNotNull($filePath);
        Storage::disk('public')->assertExists($filePath);

        // 2. Remove photo
        $response = $this->actingAs($this->student)->put(route('profile.settings.update'), [
            'name' => 'Manav Badiyani',
            'email' => 'manav@charusat.edu.in',
            'phone' => '9876543210',
            'department' => 'IT',
            'semester' => 6,
            'remove_photo' => '1',
        ]);

        $response->assertRedirect(route('profile.settings'));
        $this->student->refresh();

        $this->assertNull($this->student->profile_photo_path);
        Storage::disk('public')->assertMissing($filePath);
    }

    /**
     * Test faculty user requires Department.
     */
    public function test_faculty_must_have_department(): void
    {
        // Missing Department
        $response = $this->actingAs($this->faculty)->put(route('profile.settings.update'), [
            'name' => 'Bimal Patel',
            'email' => 'bimalpatel@charusat.ac.in',
            'phone' => '9876543211',
            'department' => '',
        ]);
        $response->assertSessionHasErrors('department');

        // Success
        $response = $this->actingAs($this->faculty)->put(route('profile.settings.update'), [
            'name' => 'Bimal Patel',
            'email' => 'bimalpatel@charusat.ac.in',
            'phone' => '9876543211',
            'department' => 'CSE',
        ]);
        $response->assertSessionHasNoErrors();
        $this->faculty->refresh();
        $this->assertEquals('CSE', $this->faculty->department);
        $this->assertEquals('FAC001', $this->faculty->faculty_id); // faculty_id should not change
    }

    /**
     * Test Faculty ID field is not visible in settings.
     */
    public function test_faculty_id_field_not_visible_in_settings(): void
    {
        $response = $this->actingAs($this->student)->get(route('profile.settings'));
        $response->assertOk();
        $response->assertDontSee('name="faculty_id"', false);
        $response->assertSee('name="semester"', false);
        
        $response = $this->actingAs($this->faculty)->get(route('profile.settings'));
        $response->assertOk();
        $response->assertDontSee('name="faculty_id"', false);
        $response->assertDontSee('name="semester"', false);
    }

    /**
     * Test change password page is displayed.
     */
    public function test_change_password_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->student)->get(route('profile.change-password'));
        $response->assertOk();
        $response->assertSee('Change Password');
        $response->assertSee('Ensure your account is using a secure password');
    }

    /**
     * Test updating password successfully.
     */
    public function test_password_can_be_updated_from_change_password_page(): void
    {
        $response = $this->actingAs($this->student)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'newPassword777',
            'password_confirmation' => 'newPassword777',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->student->refresh();

        $this->assertTrue(Hash::check('newPassword777', $this->student->password));
        $this->assertFalse($this->student->must_change_password);

        // Verify Audit Log
        $log = AuditLog::where('action', 'Password Changed')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Manav Badiyani (Student)', $log->admin_name);
    }

    /**
     * Test weak password update is blocked.
     */
    public function test_weak_password_update_is_blocked(): void
    {
        // 1. Weak due to short length
        $response = $this->actingAs($this->student)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'abc1',
            'password_confirmation' => 'abc1',
        ]);
        $response->assertSessionHasErrorsIn('updatePassword', 'password');

        // 2. Weak due to letters only
        $response = $this->actingAs($this->student)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'lettersOnly',
            'password_confirmation' => 'lettersOnly',
        ]);
        $response->assertSessionHasErrorsIn('updatePassword', 'password');

        // 3. Weak due to numbers only
        $response = $this->actingAs($this->student)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => '1234567890',
            'password_confirmation' => '1234567890',
        ]);
        $response->assertSessionHasErrorsIn('updatePassword', 'password');

        $this->student->refresh();
        $this->assertTrue(Hash::check('password123', $this->student->password));
    }

    /**
     * Test updating password validation constraints.
     */
    public function test_password_update_validates_correct_current_password(): void
    {
        $response = $this->actingAs($this->student)->put(route('profile.password.update'), [
            'current_password' => 'wrong_current_password',
            'password' => 'newPassword777',
            'password_confirmation' => 'newPassword777',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
        
        $this->student->refresh();
        $this->assertTrue(Hash::check('password123', $this->student->password));
    }

    /**
     * Test smart header rendering for single-role and multi-role users.
     */
    public function test_smart_header_renders_correct_details(): void
    {
        // Single role student - does not see current dashboard subtitle or Switch dropdown
        $response = $this->actingAs($this->student)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee('Current Dashboard:');

        // Give faculty NOC + Guide permissions to make them multi-authority
        $this->faculty->assignPermission('noc_authority');
        $this->faculty->assignPermission('guide');

        // Multi role faculty - sees current active dashboard
        $response = $this->actingAs($this->faculty)->get(route('faculty.guide-dashboard'));
        $response->assertOk();
        $response->assertSee('Current Dashboard: Guide Dashboard');
        $response->assertSee('Guide Dashboard');
        $response->assertSee('NOC Dashboard');
    }
}
