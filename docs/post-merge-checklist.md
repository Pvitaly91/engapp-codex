# Post-Merge Checklist

Use this right after merging to `main`. If the change is not deployed yet, complete step 1 immediately and complete the live-site checks after deployment.

1. Confirm the merge commit on `main` has green GitHub Actions results for `Theory Smoke`, `Public Flows Smoke`, and `Admin Flows Smoke`.
2. Open the live home page: `https://gramlyze.com/`.
3. Open the live theory root: `https://gramlyze.com/theory`.
4. Check the surface touched by the pull request for an obvious broken route, blank page, or server error.
5. If any smoke check or live verification fails, treat it as a release issue: fix forward immediately or revert the merge.
