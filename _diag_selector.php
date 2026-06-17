<?php
$teacher = App\Models\Teacher::find(3);
echo "=== Teacher: {$teacher->id} {$teacher->name} (user_id {$teacher->user_id}) ===\n";

$classLists = App\Models\ClassSubject::active()
    ->where('teacher_id', $teacher->id)
    ->with('classList')
    ->get()
    ->filter(fn ($cs) => $cs->classList && is_null($cs->classList->archived_at))
    ->groupBy('class_list_id')
    ->map(function ($rows) {
        $class = $rows->first()->classList;
        return (object) [
            'id'         => $class->id,
            'class_name' => $class->class_name,
            'subjects'   => $rows->pluck('subject')->unique()->values()->all(),
        ];
    })
    ->sortBy('class_name')
    ->values();

echo "\n=== 1. \$classLists as passed to view ===\n";
foreach ($classLists as $cl) {
    echo 'item -> ' . json_encode($cl) . "\n";
}

$ownClassIds = App\Models\ClassSubject::active()->where('teacher_id', $teacher->id)->pluck('class_list_id')->unique()->values();
echo "\n\$ownClassIds = " . json_encode($ownClassIds) . "\n";

echo "\n=== 2. class_lists rows for 18 and 15 ===\n";
foreach ([18, 15] as $id) {
    $c = App\Models\ClassList::find($id);
    echo "class_lists.id {$id} -> class_name=" . ($c ? $c->class_name : '(not found)') . "\n";
}

echo "\n=== 3. active student counts ===\n";
foreach ([18, 15] as $id) {
    echo "class_list_id {$id} active count = " . App\Models\Student::active()->where('class_list_id', $id)->count() . "\n";
}

echo "\n=== 5. \$selectedClassId on normal load (no param) ===\n";
$selectedClassId = null;
if ($selectedClassId === null || ! $ownClassIds->contains((int) $selectedClassId)) {
    $selectedClassId = $ownClassIds->first();
}
echo 'selectedClassId = ' . json_encode($selectedClassId) . "\n";

echo "\n=== 4. rendered <option> tags ===\n";
foreach ($classLists as $cl) {
    $sel = ((int) $selectedClassId === (int) $cl->id) ? ' selected' : '';
    echo "<option value=\"{$cl->id}\"{$sel}>{$cl->class_name}</option>\n";
}
