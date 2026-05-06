## Definition of Done

The project is done when Jerry can process batches of Daily Reflections URLs into correctly named, review-ready Word files organized by scripture reference, with clear logs, duplicate protection, and instructions for volunteers to review the output.

---

## Status

### ✅ Done

- system built ✅
- pipeline tested ✅
- output validated ✅
- only execution left

---

- [x] Add generated footer to every Word doc
- [x] Confirm italics preservation
- [x] Add skipped/created report
- [x] make a button, "run next batch"
- [x] Refactor app structure (move logic out of web.php into controller, parser service, and export service; think SOLID principles)
- [x] Implement batch workflow (import all Daily Reflections URLs, process next unprocessed batch, track status/output)
- [x] Get bible book abbreviations file from Jim and import to app
- [x] Get date ranges of already completed reflections

---

### 🔧 To Do

- [ ] Confirm bible book normalization (e.g., "The Song of Solomon" → "Song of Solomon")
- [ ] Build out error reports to show more than just skipped files

### Date Ranges to Skip (76 total url done by volunteers)

- 2010: January 1 through May 13, 2010. (Catherine C) 142-160
- 2011: February 25 through April 1, 2011. (Scott M) 202 - 206
- 2012: May 1, 2012 through May 31, 2012 (Jeffrey B) 264 - 267
- June 1, 2012 through June 30, 2012 268 - 271
- 2014: April 1 to to October 15, 2014 (Scott M) 366 - 391
- 2016: July 1 through October 31, 2016 (Terrell C) 478 - 495

use App\Models\ReflectionSource;

$ranges = [
    [142, 160],
    [202, 206],
    [264, 267],
    [268, 271],
    [366, 391],
    [478, 495]
];

foreach ($ranges as [$start, $end]) {
    ReflectionSource::whereBetween('id', [$start, $end])
        ->update([
            'status' => 'volunteer_done',
            'error_message' => null,
        ]);
}