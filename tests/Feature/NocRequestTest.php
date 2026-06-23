<?php

namespace Tests\Feature;

use App\Models\InternshipApplication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NocRequestTest extends TestCase
{
    use RefreshDatabase;
    protected User $student;
    protected User $faculty;
    protected User $higherFaculty;
    protected InternshipApplication $application;

    protected function setUp(): void
    {
        parent::setUp();
        
        Mail::fake();

        $studentRole = Role::create([
            'name' => 'student',
            'display_name' => 'Student',
        ]);

        $facultyRole = Role::create([
            'name' => 'faculty',
            'display_name' => 'Faculty',
        ]);

        $higherFacultyRole = Role::create([
            'name' => 'higher_faculty',
            'display_name' => 'Higher Faculty',
        ]);

        $this->student = User::create([
            'name' => 'John Student',
            'email' => 'student@example.edu.in',
            'password' => bcrypt('password'),
            'role_id' => $studentRole->id,
            'phone' => '1234567890',
            'enrollment_number' => '2024001',
            'department' => 'Computer Science',
            'semester' => 6,
        ]);

        $this->faculty = User::create([
            'name' => 'Dr. Faculty',
            'email' => 'faculty@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $facultyRole->id,
            'phone' => '9876543210',
        ]);
        $this->faculty->assignPermission('approval_faculty');

        $this->higherFaculty = User::create([
            'name' => 'Prof. Higher Faculty',
            'email' => 'higher@example.ac.in',
            'password' => bcrypt('password'),
            'role_id' => $higherFacultyRole->id,
            'phone' => '5555555555',
        ]);
        $this->higherFaculty->assignPermission('noc_authority');

        $this->application = InternshipApplication::create([
            'user_id' => $this->student->id,
            'company_name' => 'Tech Corp',
            'company_address' => '123 Main St',
            'company_website' => 'https://techcorp.com',
            'branch_address' => '456 Branch Ave',
            'number_of_employees' => '100-500',
            'technology' => 'Laravel',
            'how_did_you_get_company' => 'Through job portal',
            'reason_to_select_company' => 'Good opportunity',
            'internship_position' => 'Backend Developer',
            'start_date' => now()->addDay(),
            'end_date' => now()->addMonths(2),
            'company_letter_path' => 'letters/sample.pdf',
            'status' => 'faculty_approved',
            'noc_requested' => false,
            'submitted_at' => now(),
            'faculty_reviewed_at' => now(),
        ]);
    }

    public function test_student_can_request_noc_when_faculty_approved(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.applications.request-noc', $this->application))
            ->assertRedirect(route('student.applications.show', $this->application))
            ->assertSessionHas('success');

        $this->application->refresh();
        $this->assertTrue($this->application->noc_requested);
        $this->assertEquals('pending_higher', $this->application->status);

        Mail::assertSent(\App\Mail\NocRequested::class);
    }

    public function test_student_cannot_request_noc_for_other_students_submissions(): void
    {
        $otherStudent = User::create([
            'name' => 'Jane Student',
            'email' => 'jane@example.edu.in',
            'password' => bcrypt('password'),
            'role_id' => $this->student->role_id,
            'phone' => '1111111111',
            'enrollment_number' => '2024002',
            'department' => 'IT',
            'semester' => 5,
        ]);

        $this->actingAs($otherStudent)
            ->post(route('student.applications.request-noc', $this->application))
            ->assertForbidden();

        $this->application->refresh();
        $this->assertFalse($this->application->noc_requested);
    }

    public function test_student_cannot_request_noc_twice(): void
    {
        $this->application->update(['noc_requested' => true]);

        $this->actingAs($this->student)
            ->post(route('student.applications.request-noc', $this->application))
            ->assertRedirect()
            ->assertSessionHas('error', 'NOC cannot be requested for this application at this time.');

        $this->application->refresh();
        $this->assertTrue($this->application->noc_requested);
    }

    public function test_student_can_request_noc_for_rejected_application(): void
    {
        $this->application->update([
            'status' => 'faculty_rejected',
            'noc_requested' => false,
        ]);

        $this->actingAs($this->student)
            ->post(route('student.applications.request-noc', $this->application))
            ->assertRedirect(route('student.applications.show', $this->application))
            ->assertSessionHas('success');

        $this->application->refresh();
        $this->assertTrue($this->application->noc_requested);
        $this->assertEquals('faculty_rejected', $this->application->status);
    }

    public function test_noc_request_sends_email_to_higher_faculty(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.applications.request-noc', $this->application));

        Mail::assertSent(\App\Mail\NocRequested::class, function ($mail) {
            return $mail->hasTo($this->higherFaculty->email);
        });
    }

    public function test_noc_requested_badge_displayed_on_application_detail(): void
    {
        $this->application->update([
            'noc_requested' => true,
            'status' => 'pending_higher'
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.applications.show', $this->application));

        $response->assertSee('NOC Requested');
    }

    public function test_unauthenticated_user_cannot_request_noc(): void
    {
        $this->post(route('student.applications.request-noc', $this->application))
            ->assertRedirect(route('login'));

        $this->application->refresh();
        $this->assertFalse($this->application->noc_requested);
    }

    public function test_faculty_cannot_request_noc(): void
    {
        $this->actingAs($this->faculty)
            ->post(route('student.applications.request-noc', $this->application))
            ->assertForbidden();

        $this->application->refresh();
        $this->assertFalse($this->application->noc_requested);
    }

    public function test_request_noc_button_visible_when_eligible(): void
    {
        $response = $this->actingAs($this->student)
            ->get(route('student.applications.show', $this->application));

        $response->assertSee('Request NOC');
        $response->assertSee(route('student.applications.request-noc', $this->application), false);
    }

    public function test_request_noc_button_not_visible_when_already_requested(): void
    {
        $this->application->update([
            'noc_requested' => true,
            'status' => 'pending_higher'
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.applications.show', $this->application));

        $response->assertDontSee(route('student.applications.request-noc', $this->application), false);
    }

    public function test_higher_faculty_sees_pending_higher_applications_in_dashboard(): void
    {
        $this->application->update([
            'noc_requested' => true,
            'status' => 'pending_higher'
        ]);

        $response = $this->actingAs($this->higherFaculty)
            ->get(route('higher-faculty.noc-dashboard'));

        $response->assertSee($this->student->name);
        $response->assertSee($this->application->company_name);
    }

    public function test_higher_faculty_can_approve_pending_higher_application(): void
    {
        $this->application->update([
            'noc_requested' => true,
            'status' => 'pending_higher'
        ]);

        $this->actingAs($this->higherFaculty)
            ->post(route('higher-faculty.applications.approve', $this->application), [
                'remarks' => 'Approved after NOC request'
            ])
            ->assertRedirect();

        $this->application->refresh();
        $this->assertEquals('noc_generated', $this->application->status);
    }
}
