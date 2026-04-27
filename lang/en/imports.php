<?php

return [
    'invalid_academic_references' => 'College, department, or semester not found. Use exact names as in the system.',
    'duplicate_email' => 'Duplicate student email.',
    'duplicate_staff_row' => 'Duplicate staff row for the same context.',
    'staff_subject_format' => 'Staff column must be: StaffID | Name | Subject (or use six columns: ID, Name, Subject, College, Department, Semester).',
    'students_help' => 'Row 1 headers: Email, English name, Kurdish name, Arabic name, College, Department, Semester. Student emails must be @stud.uoz.edu.krd. Existing emails are updated (same person, new semester/year).',
    'staff_help' => 'Either 4 columns: Combined (StaffID | Name | Subject), College, Department, Semester — or 6 columns: Staff ID, Name, Subject, College, Department, Semester. Matching staff+subject+context rows are updated instead of rejected.',
    'upload' => 'Upload',
    'created' => 'Inserted (new)',
    'updated' => 'Updated (existing)',
    'skipped' => 'Skipped rows',
    'errors' => 'Errors',
    'download_template' => 'Download Excel template',
    'template_hint' => 'Download the template, fill it, then upload. The first sheet contains columns; read the Instructions sheet for rules.',
];
