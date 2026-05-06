# News Share Image Filename Resolution

Date: 2026-05-07

## Summary

- Fixed legacy news upload filename mismatches that caused share preview images to point at missing files.
- `News::mapRow()` now resolves relative image filenames against `public/uploads` before generating `/uploads/...` URLs.
- If a DB value references the wrong extension, the model falls back to an existing same-basename image extension, for example `news-98.jpeg` resolves to `news-98.jpg` when that file exists.

## Verification

- `php -d zend.assertions=1 -d assert.exception=1 tests/php/NewsModelTest.php`
- `php -l app/Models/News.php && php -l tests/php/NewsModelTest.php`
- `for f in tests/php/*.php; do php -d zend.assertions=1 -d assert.exception=1 "$f"; done`
- `npm test`

## Notes

- This fixes generated metadata URLs only when the matching file exists in `public/uploads` on the deployed server.
- Production still needs the corresponding upload file deployed and publicly readable under `/uploads/...` for social crawlers to fetch the image.
