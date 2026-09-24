<?php
/**
 * Parse the training workbook layout without requiring a spreadsheet package.
 */
function scheduleExcelColumnIndex($letters) {
    $index = 0;
    foreach (str_split(strtoupper($letters)) as $letter) {
        $index = ($index * 26) + ord($letter) - 64;
    }
    return $index - 1;
}

function scheduleWorkbookMonth($value) {
    if (!preg_match('/^\s*([A-Za-z]+)\s*-\s*(\d{4})\s*$/', trim($value), $matches)) {
        return null;
    }

    $date = DateTime::createFromFormat('!M Y', ucfirst(strtolower(substr($matches[1], 0, 3))) . ' ' . $matches[2]);
    return $date ? $date->format('Y-m-01') : null;
}

function scheduleDurationHours($value) {
    if (!preg_match('/([0-9]+(?:\.[0-9]+)?)\s*(hours?|hrs?|h|days?|d|weeks?|w)?/i', (string) $value, $matches)) {
        return null;
    }

    $amount = (float) $matches[1];
    $unit = strtolower($matches[2] ?? 'hours');
    if (str_starts_with($unit, 'day') || $unit === 'd') $amount *= 8;
    if (str_starts_with($unit, 'week') || $unit === 'w') $amount *= 40;
    return $amount;
}

function parseTrainingScheduleXlsx($filePath) {
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('PHP ZipArchive is required to import .xlsx files.');
    }

    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        throw new RuntimeException('The Excel workbook could not be opened.');
    }

    $sharedStrings = [];
    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml !== false) {
        $shared = simplexml_load_string($sharedXml);
        if ($shared) {
            foreach ($shared->si as $item) $sharedStrings[] = (string) $item->t ?: implode('', array_map('strval', $item->xpath('.//a:t')));
        }
    }

    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    if ($sheetXml === false) throw new RuntimeException('The workbook has no readable first sheet.');

    $sheet = simplexml_load_string($sheetXml);
    if (!$sheet) throw new RuntimeException('The workbook sheet could not be parsed.');

    $rows = [];
    foreach ($sheet->sheetData->row as $row) {
        $values = [];
        foreach ($row->c as $cell) {
            $ref = (string) $cell['r'];
            preg_match('/^([A-Z]+)/', $ref, $refMatch);
            if (!$refMatch) continue;
            $index = scheduleExcelColumnIndex($refMatch[1]);
            $value = '';
            if ((string) $cell['t'] === 's') $value = $sharedStrings[(int) $cell->v] ?? '';
            elseif ((string) $cell['t'] === 'inlineStr') $value = (string) $cell->is->t;
            else $value = (string) $cell->v;
            $values[$index] = trim($value);
        }
        $rows[] = $values;
    }

    $panels = [];
    $records = [];
    foreach ($rows as $values) {
        foreach ($values as $index => $value) {
            $month = scheduleWorkbookMonth($value);
            if ($month) $panels[$index] = $month;
        }

        foreach ($panels as $startColumn => $monthStart) {
            $courseName = trim($values[$startColumn + 1] ?? '');
            $durationText = trim($values[$startColumn + 2] ?? '');
            $trainingDates = trim($values[$startColumn + 3] ?? '');
            $venue = trim($values[$startColumn + 4] ?? '');
            if ($courseName === '' || strtoupper($courseName) === 'COURSES') continue;
            $durationHours = scheduleDurationHours($durationText);
            if ($durationHours === null || $trainingDates === '' || $venue === '') continue;
            $records[] = [
                'month_start' => $monthStart,
                'course_name' => $courseName,
                'duration_hours' => $durationHours,
                'training_dates' => $trainingDates,
                'venue' => $venue,
                'sort_order' => count(array_filter($records, static fn($record) => $record['month_start'] === $monthStart)) + 1,
            ];
        }
    }

    return $records;
}

function parseTrainingScheduleCsv($filePath) {
    $handle = fopen($filePath, 'r');
    if (!$handle) throw new RuntimeException('The CSV file could not be opened.');

    $headers = array_map('trim', fgetcsv($handle) ?: []);
    $required = ['month_start', 'course_name', 'duration_hours', 'training_dates', 'venue'];
    if (array_diff($required, $headers)) {
        fclose($handle);
        throw new RuntimeException('The CSV must include month_start, course_name, duration_hours, training_dates, and venue columns.');
    }

    $records = [];
    while (($row = fgetcsv($handle)) !== false) {
        if (count(array_filter($row, static fn($value) => trim((string) $value) !== '')) === 0) continue;
        $record = array_combine($headers, array_pad($row, count($headers), ''));
        $monthDate = DateTime::createFromFormat('!Y-m-d', trim($record['month_start'] ?? ''));
        $duration = filter_var(trim($record['duration_hours'] ?? ''), FILTER_VALIDATE_FLOAT);
        if (!$monthDate || $duration === false || $duration <= 0 || trim($record['course_name'] ?? '') === '' || trim($record['training_dates'] ?? '') === '' || trim($record['venue'] ?? '') === '') continue;
        $records[] = [
            'month_start' => $monthDate->format('Y-m-01'),
            'course_name' => trim($record['course_name']),
            'duration_hours' => $duration,
            'training_dates' => trim($record['training_dates']),
            'venue' => trim($record['venue']),
            'sort_order' => max(1, (int) ($record['sort_order'] ?? 1)),
        ];
    }
    fclose($handle);
    return $records;
}
