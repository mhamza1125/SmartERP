<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Attendance machine database (ZKTeco ATT2000.mdb)
    |--------------------------------------------------------------------------
    |
    | default_path: used while Settings > Company has no path saved. Relative
    | paths are resolved against the project root.
    |
    | allowed_dirs: folders the configured file must be inside, separated by
    | ";" (e.g. "D:\ZKTeco;E:\Attendance"). Empty = any folder on the server.
    | The project root's public/resources folder is always allowed so the
    | default keeps working.
    |
    */

    'default_path' => env('ATTENDANCE_DB_PATH', 'public/resources/att2000.mdb'),

    'allowed_dirs' => array_values(array_filter(array_map('trim', explode(';', (string) env('ATTENDANCE_DB_ALLOWED_DIRS', ''))))),

    'allowed_extensions' => ['mdb', 'accdb'],

    // Punches by the same person closer together than this are one punch (double press on the device)
    'duplicate_punch_seconds' => 120,

    // Longest date range one attendance query may cover
    'max_range_days' => 366,
];
