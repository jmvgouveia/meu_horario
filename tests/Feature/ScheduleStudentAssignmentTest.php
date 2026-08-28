<?php

namespace Tests\Feature;

use App\Filament\Resources\TeacherStudentsResource;
use App\Models\RegistrationSubject;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScheduleStudentAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_schedules_use_exact_pivot_membership(): void
    {
        $data = $this->createScheduleFixture();

        DB::table('schedules_students')->insert([
            'id_schedule' => $data['selected_schedule_id'],
            'id_student' => $data['student_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $student = Student::findOrFail($data['student_id']);

        $this->assertTrue($student->schedules->contains('id', $data['selected_schedule_id']));
        $this->assertFalse($student->schedules->contains('id', $data['other_schedule_id']));
    }

    public function test_registration_subject_selected_schedule_uses_id_schedule(): void
    {
        $data = $this->createScheduleFixture();

        $registrationSubjectId = DB::table('registrations_subjects')->insertGetId([
            'id_registration' => $data['registration_id'],
            'id_subject' => $data['subject_id'],
            'id_schedule' => $data['selected_schedule_id'],
            'shift' => 'Legacy text that is not a schedule id',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $registrationSubject = RegistrationSubject::with('selectedSchedule')->findOrFail($registrationSubjectId);

        $this->assertSame($data['selected_schedule_id'], $registrationSubject->selectedSchedule->id);
        $this->assertSame('P1234', $registrationSubject->student->number);
    }

    public function test_teacher_students_only_contains_students_assigned_to_the_teacher_schedule(): void
    {
        $data = $this->createScheduleFixture();
        $now = now();
        $user = User::factory()->create();

        DB::table('teachers')->where('id', $data['teacher_id'])->update(['id_user' => $user->id]);
        DB::table('teacher_subjects')->insert([
            'id_teacher' => $data['teacher_id'],
            'id_subject' => $data['subject_id'],
            'id_schoolyear' => $data['school_year_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $assignedRegistrationSubjectId = DB::table('registrations_subjects')->insertGetId([
            'id_registration' => $data['registration_id'],
            'id_subject' => $data['subject_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $otherStudentId = Student::withoutEvents(fn() => Student::create([
            'number' => 'P5678',
            'name' => 'Student Two',
            'birthdate' => '2010-02-01',
        ]))->id;
        $otherRegistrationId = DB::table('registrations')->insertGetId([
            'id_student' => $otherStudentId,
            'id_course' => $data['course_id'],
            'id_schoolyear' => $data['school_year_id'],
            'id_class' => $data['class_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $otherRegistrationSubjectId = DB::table('registrations_subjects')->insertGetId([
            'id_registration' => $otherRegistrationId,
            'id_subject' => $data['subject_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('schedules_students')->insert([
            'id_schedule' => $data['selected_schedule_id'],
            'id_student' => $data['student_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->actingAs($user);

        $registrationSubjectIds = TeacherStudentsResource::getEloquentQuery()->pluck('id');

        $this->assertTrue($registrationSubjectIds->contains($assignedRegistrationSubjectId));
        $this->assertFalse($registrationSubjectIds->contains($otherRegistrationSubjectId));
    }

    private function createScheduleFixture(): array
    {
        $now = now();

        $schoolYearId = DB::table('schoolyears')->insertGetId([
            'schoolyear' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $buildingId = DB::table('buildings')->insertGetId([
            'name' => 'Main',
            'address' => 'Test address',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $roomId = DB::table('rooms')->insertGetId([
            'name' => '101',
            'id_building' => $buildingId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $courseId = DB::table('courses')->insertGetId([
            'name' => 'Music',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $classId = DB::table('classes')->insertGetId([
            'name' => '1A',
            'id_course' => $courseId,
            'year' => 1,
            'id_building' => $buildingId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $subjectId = DB::table('subjects')->insertGetId([
            'name' => 'Piano',
            'acronym' => 'PIA',
            'type' => 'Letiva',
            'student_can_enroll' => true,
            'status' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $timeperiodId = DB::table('timeperiods')->insertGetId([
            'description' => '08:00-09:00',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $teacherId = DB::table('teachers')->insertGetId([
            'number' => 100,
            'name' => 'Teacher One',
            'acronym' => 'T1',
            'birthdate' => '1980-01-01',
            'startingdate' => '2020-01-01',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $studentId = Student::withoutEvents(fn() => Student::create([
            'number' => 'P1234',
            'name' => 'Student One',
            'birthdate' => '2010-01-01',
        ]))->id;

        $registrationId = DB::table('registrations')->insertGetId([
            'id_student' => $studentId,
            'id_course' => $courseId,
            'id_schoolyear' => $schoolYearId,
            'id_class' => $classId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $selectedScheduleId = Schedule::create([
            'id_schoolyear' => $schoolYearId,
            'id_timeperiod' => $timeperiodId,
            'id_room' => $roomId,
            'id_teacher' => $teacherId,
            'id_weekday' => 1,
            'id_subject' => $subjectId,
            'shift' => 'Turno A - P1234',
            'status' => 'Aprovado',
        ])->id;

        $otherScheduleId = Schedule::create([
            'id_schoolyear' => $schoolYearId,
            'id_timeperiod' => $timeperiodId,
            'id_room' => $roomId,
            'id_teacher' => $teacherId,
            'id_weekday' => 2,
            'id_subject' => $subjectId,
            'shift' => 'Turno B - P1234',
            'status' => 'Aprovado',
        ])->id;

        DB::table('schedules_classes')->insert([
            [
                'id_schedule' => $selectedScheduleId,
                'id_class' => $classId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_schedule' => $otherScheduleId,
                'id_class' => $classId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        return [
            'school_year_id' => $schoolYearId,
            'teacher_id' => $teacherId,
            'course_id' => $courseId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'student_id' => $studentId,
            'registration_id' => $registrationId,
            'selected_schedule_id' => $selectedScheduleId,
            'other_schedule_id' => $otherScheduleId,
        ];
    }
}
