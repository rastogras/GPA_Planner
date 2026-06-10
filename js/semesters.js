// js/semesters.js — Dynamic course row management

const GRADE_OPTIONS = [
    ['', '—'],
    ['A',  'A (4.0)'],
    ['A-', 'A- (3.7)'],
    ['B+', 'B+ (3.3)'],
    ['B',  'B (3.0)'],
    ['B-', 'B- (2.7)'],
    ['C+', 'C+ (2.3)'],
    ['C',  'C (2.0)'],
    ['C-', 'C- (1.7)'],
    ['D+', 'D+ (1.3)'],
    ['D',  'D (1.0)'],
    ['E',  'E (0.0)'],
];

const GRADE_POINTS = {
    'A': 4.0, 'A-': 3.7, 'B+': 3.3, 'B': 3.0, 'B-': 2.7,
    'C+': 2.3, 'C': 2.0, 'C-': 1.7, 'D+': 1.3, 'D': 1.0, 'E': 0.0
};

function buildGradeSelect(name) {
    const sel = document.createElement('select');
    sel.name = name;
    sel.onchange = function() {
        const pts = GRADE_POINTS[this.value];
        const row = this.closest('tr');
        if (row) row.querySelector('.grade-pts').textContent = pts !== undefined ? pts.toFixed(1) : '—';
    };
    GRADE_OPTIONS.forEach(([val, label]) => {
        const opt = document.createElement('option');
        opt.value = val;
        opt.textContent = label;
        sel.appendChild(opt);
    });
    return sel;
}

function buildCreditSelect(name) {
    const sel = document.createElement('select');
    sel.name = name;
    for (let c = 1; c <= 6; c++) {
        const opt = document.createElement('option');
        opt.value = c;
        opt.textContent = c;
        if (c === 3) opt.selected = true;
        sel.appendChild(opt);
    }
    return sel;
}

function addRow(semId) {
    const tbody = document.getElementById('tbody-' + semId);
    if (!tbody) return;

    const tr = document.createElement('tr');

    // Code
    const tdCode = document.createElement('td');
    const inCode = document.createElement('input');
    inCode.type = 'text'; inCode.name = 'course_code[]'; inCode.placeholder = 'e.g. SMA2101';
    tdCode.appendChild(inCode);

    // Name
    const tdName = document.createElement('td');
    const inName = document.createElement('input');
    inName.type = 'text'; inName.name = 'course_name[]'; inName.placeholder = 'Course name'; inName.required = true;
    tdName.appendChild(inName);

    // Credits
    const tdCred = document.createElement('td');
    tdCred.appendChild(buildCreditSelect('credit_hours[]'));

    // Grade
    const tdGrade = document.createElement('td');
    tdGrade.appendChild(buildGradeSelect('grade_letter[]'));

    // Points
    const tdPts = document.createElement('td');
    tdPts.className = 'grade-pts';
    tdPts.textContent = '—';

    // Delete
    const tdDel = document.createElement('td');
    const btnDel = document.createElement('button');
    btnDel.type = 'button'; btnDel.className = 'btn-row-del';
    btnDel.textContent = '✕';
    btnDel.onclick = function() { removeRow(this); };
    tdDel.appendChild(btnDel);

    tr.append(tdCode, tdName, tdCred, tdGrade, tdPts, tdDel);
    tbody.appendChild(tr);
    inName.focus();
}

function removeRow(btn) {
    const tbody = btn.closest('tbody');
    const row   = btn.closest('tr');
    if (tbody && tbody.rows.length > 1) {
        row.remove();
    } else {
        // Keep at least one row but clear its values
        row.querySelectorAll('input').forEach(i => i.value = '');
        row.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        row.querySelector('.grade-pts').textContent = '—';
    }
}

// Live GPA preview as grades are changed
document.addEventListener('change', function(e) {
    if (e.target.name === 'grade_letter[]' || e.target.name === 'credit_hours[]') {
        const block = e.target.closest('.semester-block');
        if (!block) return;
        const rows = block.querySelectorAll('tbody tr');
        let totalPts = 0, totalCred = 0;
        rows.forEach(r => {
            const gradeEl = r.querySelector('select[name="grade_letter[]"]');
            const credEl  = r.querySelector('select[name="credit_hours[]"]');
            if (!gradeEl || !credEl) return;
            const pts  = GRADE_POINTS[gradeEl.value];
            const cred = parseInt(credEl.value) || 0;
            if (pts !== undefined && cred) {
                totalPts  += pts * cred;
                totalCred += cred;
            }
        });
        const badge = block.querySelector('.sem-gpa-badge');
        if (badge) {
            const gpa = totalCred > 0 ? (totalPts / totalCred).toFixed(2) : '—';
            badge.innerHTML = 'Semester GPA: <strong>' + gpa + '</strong>';
        }
    }
});
