# News Share Metadata HEAD Request Fix

Date: 2026-05-07

## Summary

- Live metadata for `https://official.genbijambi.com/news/genbi-goes-to-campus-uin-sts-jambi` is present and correct for normal GET crawler requests.
- The same clean URL returned `404` for `HEAD` requests because the router matched only exact HTTP methods.
- Updated the router so `HEAD` requests dispatch through matching `GET` routes.
- Updated response helpers so `HEAD` requests send headers/status only and suppress the response body.

## Why This Matters

Some link validators, social crawlers, and preview services probe a URL with `HEAD` before or around a normal `GET`. Returning `404` for `HEAD` can cause those services to treat the URL as unavailable even when the HTML metadata exists on `GET`.

## Live Findings

- `GET /news/genbi-goes-to-campus-uin-sts-jambi` returns `200` and includes `og:title`, `og:description`, `og:image`, Twitter Card tags, and canonical URL.
- `HEAD /news/genbi-goes-to-campus-uin-sts-jambi` returned `404` before this fix.
- `GET /uploads/news-96.jpg` returns `200` with `content-type: image/jpeg`.

## Verification

- `php -l app/Core/Router.php && php -l app/Core/Response.php && php -l tests/php/RouterHeadRequestTest.php`
- `php -d zend.assertions=1 -d assert.exception=1 tests/php/RouterHeadRequestTest.php`
- `for f in tests/php/*.php; do php -d zend.assertions=1 -d assert.exception=1 "$f"; done`
- `npm test`

## Deployment Note

After deployment, retest:

```sh
/usr/bin/curl -I -L https://official.genbijambi.com/news/genbi-goes-to-campus-uin-sts-jambi
```

Expected result: `HTTP/2 200`, not `404`.
