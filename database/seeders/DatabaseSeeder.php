<?php

namespace Database\Seeders;

use App\Enums\FeedbackFormStatus;
use App\Models\Admin;
use App\Models\College;
use App\Models\Department;
use App\Models\FeedbackForm;
use App\Models\FeedbackFormVersion;
use App\Models\FeedbackQuestion;
use App\Models\Semester;
use App\Models\StaffSubject;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $college = College::query()->firstOrCreate(
            ['name_en' => 'Engineering College'],
            ['name_ku' => 'کۆلێژی ئەندازیاری', 'name_ar' => 'كلية الهندسة']
        );

        $dept = Department::query()->firstOrCreate(
            ['college_id' => $college->id, 'name_en' => 'Computer Science'],
            ['name_ku' => 'زانستی کۆمپیوتەر', 'name_ar' => 'علوم الحاسوب']
        );

        $sem = Semester::query()->firstOrCreate(
            ['name_en' => '2026 Spring'],
            ['name_ku' => 'بەهار ٢٠٢٦', 'name_ar' => 'ربيع 2026']
        );

        Admin::query()->firstOrCreate(
            ['email' => 'admin@uoz.edu.krd'],
            ['english_name' => 'Demo Admin', 'kurdish_name' => null, 'arabic_name' => null]
        );

        Admin::query()->firstOrCreate(
            ['email' => 'mohammad.abdulwahab@uoz.edu.krd'],
            ['english_name' => 'Mohammad Abdulwahab', 'kurdish_name' => null, 'arabic_name' => null]
        );

        $student = Student::query()->firstOrCreate(
            ['email' => 'student@stud.uoz.edu.krd'],
            [
                'english_name' => 'Demo Student',
                'kurdish_name' => null,
                'arabic_name' => null,
                'college_id' => $college->id,
                'department_id' => $dept->id,
                'semester_id' => $sem->id,
            ]
        );

        $staff = StaffSubject::query()->firstOrCreate(
            [
                'staff_employee_id' => 'T001',
                'subject_name' => 'Data Structures',
                'college_id' => $college->id,
                'department_id' => $dept->id,
                'semester_id' => $sem->id,
            ],
            ['instructor_name' => 'Dr. Demo Instructor']
        );

        $form = FeedbackForm::query()->firstOrCreate(
            ['title_en' => 'Demo teaching evaluation'],
            ['title_ku' => null, 'title_ar' => null, 'status' => FeedbackFormStatus::Draft]
        );

        $version = FeedbackFormVersion::query()->firstOrCreate(
            ['feedback_form_id' => $form->id, 'version_number' => 1],
            ['accepts_submissions' => false, 'starts_at' => null, 'ends_at' => null]
        );

        if ($version->questions()->count() === 0) {
            $version->questions()->createMany([
                [
                    'type' => 'likert_5',
                    'label_en' => 'The instructor explains topics clearly.',
                    'label_ku' => null,
                    'label_ar' => null,
                    'is_required' => true,
                    'sort_order' => 0,
                    'options' => null,
                ],
                [
                    'type' => 'yes_no',
                    'label_en' => 'Would you recommend this course?',
                    'label_ku' => null,
                    'label_ar' => null,
                    'is_required' => true,
                    'sort_order' => 1,
                    'options' => null,
                ],
                [
                    'type' => 'text',
                    'label_en' => 'Optional comments',
                    'label_ku' => null,
                    'label_ar' => null,
                    'is_required' => false,
                    'sort_order' => 2,
                    'options' => null,
                ],
            ]);
        }
    }
}
