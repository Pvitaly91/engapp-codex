<script>
/**
 * Marker Theory functionality shared across V2 test views
 * Requires: MARKER_THEORY_URL, TEST_SLUG, CSRF_TOKEN, state, showLoader, persistState, html functions
 */

function fetchMarkerTheory(idx, marker) {
  const item = state.items[idx];
  if (!item) return;

  // Initialize markerTheoryCache if not present
  if (!item.markerTheoryCache || typeof item.markerTheoryCache !== 'object') {
    item.markerTheoryCache = {};
  }

  // If already in cache, just show it
  if (Object.prototype.hasOwnProperty.call(item.markerTheoryCache, marker)) {
    renderMarkerTheoryPanel(idx, marker, item.markerTheoryCache[marker]);
    return;
  }

  const payload = {
    question_id: item.id,
    marker: marker,
  };

  if (typeof TEST_SLUG !== 'undefined' && TEST_SLUG) {
    payload.test_slug = TEST_SLUG;
  }

  if (typeof showLoader === 'function') showLoader(true);
  
  fetch(MARKER_THEORY_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
    },
    body: JSON.stringify(payload),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error('Failed to load marker theory');
      }
      return response.json();
    })
    .then((data) => {
      const theoryBlock = data && data.theory_block ? data.theory_block : null;
      item.markerTheoryCache[marker] = theoryBlock;
      renderMarkerTheoryPanel(idx, marker, theoryBlock);
      if (typeof persistState === 'function') persistState(state);
    })
    .catch((error) => {
      console.error(error);
      item.markerTheoryCache[marker] = null;
      renderMarkerTheoryPanel(idx, marker, null);
    })
    .finally(() => {
      if (typeof showLoader === 'function') showLoader(false);
    });
}

function renderMarkerTheoryPanel(idx, marker, block) {
  // Try to find theory panel - it may have different ID patterns
  let panel = document.getElementById(`theory-panel-${idx}`);
  if (!panel) {
    panel = document.getElementById('theory-panel');
  }
  if (!panel) return;

  if (!block) {
    panel.innerHTML = `
      <div class="p-4 bg-gradient-to-r from-cyan-50 to-sky-50 rounded-2xl border border-cyan-200">
        <div class="flex items-center gap-2 mb-2">
          <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
          <span class="text-sm font-semibold text-cyan-900">📚 Theory for ${html(marker)}</span>
        </div>
        <p class="text-sm text-cyan-700">No matching theory found for this marker.</p>
      </div>
    `;
    panel.classList.remove('hidden');
    return;
  }

  let content = '';
  
  try {
    const body = typeof block.body === 'string' ? JSON.parse(block.body) : block.body;
    
    if (body.title) {
      content += `<h4 class="font-semibold text-cyan-900 mb-2">${html(body.title)}</h4>`;
    }
    if (body.intro) {
      content += `<p class="text-sm text-cyan-800 mb-3">${body.intro}</p>`;
    }
    if (body.sections && Array.isArray(body.sections)) {
      body.sections.forEach(section => {
        content += `<div class="mb-3">`;
        if (section.label) {
          content += `<p class="text-sm font-semibold text-cyan-700">${html(section.label)}</p>`;
        }
        if (section.description) {
          content += `<p class="text-sm text-cyan-800">${section.description}</p>`;
        }
        if (section.examples && Array.isArray(section.examples)) {
          content += `<ul class="mt-1 space-y-1">`;
          section.examples.forEach(ex => {
            content += `<li class="text-sm"><span class="text-cyan-900 font-medium">${html(ex.en || '')}</span>`;
            if (ex.ua) content += ` — <span class="text-cyan-700">${html(ex.ua)}</span>`;
            content += `</li>`;
          });
          content += `</ul>`;
        }
        if (section.note) {
          content += `<p class="text-xs text-cyan-600 mt-1">${html(section.note)}</p>`;
        }
        content += `</div>`;
      });
    }
    if (body.items && Array.isArray(body.items)) {
      content += `<ul class="list-disc list-inside space-y-1">`;
      body.items.forEach(item => {
        if (typeof item === 'string') {
          content += `<li class="text-sm text-cyan-800">${html(item)}</li>`;
        } else if (item.title) {
          content += `<li class="text-sm"><span class="font-medium text-cyan-900">${html(item.title)}</span>`;
          if (item.subtitle) content += ` — <span class="text-cyan-700">${html(item.subtitle)}</span>`;
          content += `</li>`;
        }
      });
      content += `</ul>`;
    }
  } catch (e) {
    content = `<p class="text-sm text-cyan-800">${html(block.body || '')}</p>`;
  }

  const matchedTagsHtml = block.matched_tags && block.matched_tags.length > 0
    ? `<div class="mt-2 text-xs text-cyan-600">Matched tags: ${block.matched_tags.map(t => html(t)).join(', ')}</div>`
    : '';

  panel.innerHTML = `
    <div class="p-4 bg-gradient-to-r from-cyan-50 to-sky-50 rounded-2xl border border-cyan-200">
      <div class="flex items-center gap-2 mb-2">
        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <span class="text-sm font-semibold text-cyan-900">📚 Theory for ${html(marker)}</span>
        ${block.level ? `<span class="ml-auto px-2 py-0.5 text-xs font-bold rounded-full bg-cyan-200 text-cyan-800">${html(block.level)}</span>` : ''}
      </div>
      ${content}
      ${matchedTagsHtml}
    </div>
  `;
  panel.classList.remove('hidden');
}

/**
 * Helper to render the marker theory button HTML
 */
function renderMarkerTheoryButton(marker, idx, hasMarkerTags) {
  if (!hasMarkerTags) return '';
  return ` <button type="button" class="marker-theory-btn inline-flex items-center text-[11px] px-1.5 py-0.5 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-700 hover:text-cyan-800 font-medium transition-colors" data-marker="${marker}" data-idx="${idx}"><svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>T</button>`;
}

/**
 * Helper to check if a question has marker tags for a given marker
 */
function hasMarkerTags(q, marker) {
  return q.marker_tags && q.marker_tags[marker] && q.marker_tags[marker].length > 0;
}
</script>
