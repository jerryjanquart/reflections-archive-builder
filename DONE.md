## Definition of Done

The project is done when Jerry can process batches of Daily Reflections URLs into correctly named, review-ready Word files organized by scripture reference, with clear logs, duplicate protection, and instructions for volunteers to review the output.

---

## Status

### ✅ Done

- [x] Add generated footer to every Word doc
- [x] Confirm italics preservation
- [x] Add skipped/created report
- [x] make a button, "run next batch"
- [x] Refactor app structure (move logic out of web.php into controller, parser service, and export service; think SOLID principles)

---

### 🔧 To Do

- [ ] Get bible book abbreviations file from Jim and import to app
- [ ] Confirm bible book normalization (e.g., "The Song of Solomon" → "Song of Solomon")
- [ ] Build out error reports to show more than just skipped files
- [ ] Implement batch workflow (import all Daily Reflections URLs, process next unprocessed batch, track status/output)