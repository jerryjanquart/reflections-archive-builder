# Daily Reflections Project (Reflections Archive Builder)

## Overview
Parses Touchstone Daily Reflections into structured entries and generates archive-ready Word documents with consistent naming and folder hierarchy.

The resulting archive is intended to serve as the canonical, normalized version of the Daily Reflections content. Final output is designed to sync with Google Drive.

---

## Current Status

### Parsing & Data Extraction
- Laravel parser working (stable)
- Extracts structured data per reflection:
  - day
  - year
  - scripture reference
  - book
  - chapter
  - verses
  - word count
  - full content

### Content Handling
- Handles:
  - standard `Book Chapter:Verse` headings
  - `"The Book of ..."` fallback cases
  - multiple reflections within a single day
  - stricter heading detection using known Bible book names (prevents false splits)
  - removal of WordPress footer artifacts

### Formatting
- Preserves:
  - paragraph structure
  - italics (`<i>` tags) → converted to Word italics formatting

### Output Generation
- Generates fully formatted Word documents (`.docx`)
- Includes:
  - title (scripture reference)
  - source line (date)
  - word count
  - paragraph-separated body content
- Filenames are standardized and archive-ready:
  - e.g. `Lk12.57-59.291.DR10.22.2007.docx`

### Folder Structure
- Automatically creates archive structure:
  
    Category /
      Book 1–N /
        Book Chapter /
          Filename.docx

- Folder names are sanitized to prevent filesystem issues

### System Behavior
- Safe to re-run:
  - existing files are skipped (no overwrites)
- Output is incremental:
  - new files are added without affecting existing archive
- Run reporting included:
  - created vs skipped file counts

---

## Input
Source: WordPress Daily Reflections

Example:
https://www.touchstonemag.com/daily_reflections/...

---

## Output (Phase 1)

Example:

    Gospels / Luke 1–24 / Luke 12 /
    Lk12.57-59.291.DR10.22.2007.docx

---

## Workflow

Daily Reflections are processed in batches to produce standardized draft documents. These drafts are reviewed by volunteers, who ensure accuracy, consistency, and formatting quality prior to final archiving.

This hybrid workflow maximizes efficiency while preserving editorial integrity.

---

## Benefits

- Reduces manual formatting work from ~30–60 minutes per week to seconds
- Produces consistent, archive-ready documents at scale
- Preserves important textual features (e.g. italics, paragraph structure)
- Eliminates naming and formatting inconsistencies
- Allows human review to catch edge cases and ensure quality
- Establishes a clean, normalized, and maintainable long-term archive

---

## For Monday Demo

- Parse source (WordPress Daily Reflections)
- Generate structured output
- Create archive-ready Word documents
- Automatically place files into archive folder structure
- Demonstrate:
  - multi-URL processing (batch behavior)
  - real-time file generation
  - duplicate-safe re-runs (Created vs Skipped)

---

## Next Steps

- Connect output directory to Google Drive synced folder
- Allow volunteers to access files immediately upon generation
- Add processed URL tracking (database layer)
- Expand book coverage as needed
- Refine document styling (optional polish)

---

## Batch Size (Production)

- Start with: 3–5 links per batch  
- Scale to: ~8–10 links per batch once stable  
- Optimize around: human review capacity (not just processing speed)

---

## Notes

- Do not over-engineer
- Match existing workflow first
- System evolves incrementally from working baseline

---

## Review Workflow (File Status)

Because files must remain in their final archive folders, status is tracked using filename tags. Files should be viewed in list mode (not thumbnails).

After reviewing a document, append a status tag:

- `[DONE]` — review complete
- `[CHECK]` — needs further attention
- `[IN PROGRESS]` — currently being reviewed (optional)

Example:
Lk12.57-59.291.DR10.22.2007 [DONE].docx

---

## Processing & Duplication Safeguards

To prevent duplicate work:

- Existing files are checked before creation
- Files are never overwritten
- System is safe to re-run at any time

Future enhancement:
- Track processed URLs at the database level

---

## Review Checklist

When reviewing generated documents, verify the following:

- ✔ Content accuracy (nothing missing or incorrectly split)  
- ✔ Scripture reference matches the content  
- ✔ Paragraph structure is correct and readable  
- ✔ Italics are preserved where expected  
- ✔ No artifacts from parsing (extra characters, spacing issues, etc.)  

Minor edits can be made directly in the document if needed.

### Marking Completion

After review, append a status tag to the filename:

- `[CHECK]` — needs further attention  
- `[DONE]` — review complete  

Example:
Lk12.57-59.291.DR10.22.2007 [DONE].docx
