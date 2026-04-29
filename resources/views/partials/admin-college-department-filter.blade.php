@php
    $deptRows = collect($departments)->map(function ($d) {
        return [
            'id' => (string) $d->id,
            'name' => $d->name_en,
            'college_id' => (string) $d->college_id,
        ];
    })->values()->all();
    $selectedDept = $selectedDepartmentId !== null && $selectedDepartmentId !== '' ? (string) $selectedDepartmentId : '';
    $place = $placeholder ?? __('fields.department');
@endphp
<script>
(function () {
    const deptData = @json($deptRows);
    const collegeEl = document.getElementById(@json($collegeElementId));
    const deptEl = document.getElementById(@json($departmentElementId));
    if (!collegeEl || !deptEl) {
        return;
    }
    const serverSelectedDept = @json($selectedDept);
    const placeholder = @json($place);

    function rebuild(fromCollegeChange) {
        const cid = collegeEl.value || '';
        deptEl.innerHTML = '';
        const first = document.createElement('option');
        first.value = '';
        first.textContent = placeholder;
        deptEl.appendChild(first);
        for (let i = 0; i < deptData.length; i++) {
            const d = deptData[i];
            if (cid !== '' && d.college_id !== cid) {
                continue;
            }
            const o = document.createElement('option');
            o.value = d.id;
            o.textContent = d.name;
            deptEl.appendChild(o);
        }
        const pick = fromCollegeChange ? '' : serverSelectedDept;
        if (pick && Array.from(deptEl.options).some(function (opt) { return opt.value === pick; })) {
            deptEl.value = pick;
        }
    }

    collegeEl.addEventListener('change', function () {
        rebuild(true);
    });
    rebuild(false);
})();
</script>
