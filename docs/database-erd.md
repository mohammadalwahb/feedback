# Database schema (conceptual ERD)

```mermaid
erDiagram
    users ||--o| students : links
    users ||--o| admins : links
    colleges ||--o{ departments : contains
    colleges ||--o{ students : enrolls
    departments ||--o{ students : enrolls
    semesters ||--o{ students : enrolls
    colleges ||--o{ staff_subjects : context
    departments ||--o{ staff_subjects : context
    semesters ||--o{ staff_subjects : context
    feedback_forms ||--o{ feedback_form_versions : versions
    feedback_form_versions ||--o{ feedback_questions : contains
    feedback_form_versions ||--o{ feedback_submissions : receives
    students ||--o{ feedback_submissions : submits_internal_only
    staff_subjects ||--o{ feedback_submissions : evaluated
    feedback_submissions ||--o{ feedback_answers : has
    feedback_questions ||--o{ feedback_answers : answered
    users ||--o{ admin_audit_logs : performs

    users {
        bigint id PK
        string email UK
        string google_id UK
        string role
    }

    students {
        bigint id PK
        string email UK
        bigint college_id FK
        bigint department_id FK
        bigint semester_id FK
        bigint user_id FK
    }

    staff_subjects {
        bigint id PK
        string staff_employee_id
        string instructor_name
        string subject_name
        bigint college_id FK
        bigint department_id FK
        bigint semester_id FK
    }

    feedback_submissions {
        bigint id PK
        bigint student_id FK
        bigint staff_subject_id FK
        bigint feedback_form_version_id FK
        timestamp submitted_at
    }

    feedback_answers {
        bigint id PK
        bigint feedback_submission_id FK
        bigint feedback_question_id FK
        json value
    }
```

## Notes

- **Who can evaluate whom:** There is no `student_staff` mapping table. A student may submit feedback only for `staff_subjects` rows whose `college_id`, `department_id`, and `semester_id` match the student’s.
- All administrative entities (`colleges`, `departments`, `semesters`, `students`, `admins`, `staff_subjects`, `feedback_forms`, `feedback_form_versions`, `feedback_questions`) use **soft deletes** where applicable.
- **Uniqueness** on feedback is enforced on `(student_id, staff_subject_id, feedback_form_version_id)` so each student evaluates a given instructor-subject at most once per form version.
- **Reports** aggregate `feedback_answers` only; student identifiers are not exposed in export views.
