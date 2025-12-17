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
      // Re-render the tags display to show highlighting
      updateMarkerTagsHighlighting(idx, marker, item);
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
    // If body is not valid JSON, it may be raw HTML content from trusted server-side sources
    // Render it directly without escaping
    content = `<div class="text-sm text-cyan-800">${block.body || ''}</div>`;
  }

  const matchedTagsHtml = block.matched_tags && block.matched_tags.length > 0
    ? `<div class="mt-2 text-xs text-cyan-600">Matched tags: ${block.matched_tags.map(t => html(t)).join(', ')}</div>`
    : '';

  // Add link to full theory page if available
  const pageLink = block.page_url 
    ? `<a href="${html(block.page_url)}" target="_blank" class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-cyan-700 hover:text-cyan-900 hover:underline transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
        </svg>
        ${block.page_title ? html(block.page_title) : 'Open full theory page'} →
      </a>`
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
      ${pageLink}
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

/**
 * Get marker tags for a specific marker in a question
 */
function getMarkerTags(q, marker) {
  if (!q.marker_tags || !q.marker_tags[marker]) return [];
  return q.marker_tags[marker];
}

/**
 * Get matched tags for a specific marker from the theory cache
 */
function getMatchedTagsForMarker(q, marker) {
  if (!q.markerTheoryCache || !q.markerTheoryCache[marker]) return [];
  const theoryBlock = q.markerTheoryCache[marker];
  if (!theoryBlock || !theoryBlock.matched_tags) return [];
  return theoryBlock.matched_tags;
}

/**
 * Render marker tags inline for debugging
 * Shows tags in a collapsible badge next to markers
 * Highlights tags that match with theory blocks
 */
function renderMarkerTagsDebug(q, marker, idx) {
  const tags = getMarkerTags(q, marker);
  if (!tags || tags.length === 0) return '';
  
  const matchedTags = getMatchedTagsForMarker(q, marker);
  const matchedTagsLower = matchedTags.map(t => t.toLowerCase());
  
  const tagId = `marker-tags-${idx}-${marker}`;
  const tagsHtml = tags.map(t => {
    const isMatched = matchedTagsLower.includes(t.toLowerCase());
    const matchClass = isMatched 
      ? 'bg-emerald-200 text-emerald-800 ring-1 ring-emerald-400' 
      : 'bg-violet-100 text-violet-700';
    return `<span class="inline-block px-1.5 py-0.5 rounded ${matchClass} text-[9px] font-medium mr-1 mb-1">${html(t)}${isMatched ? ' ✓' : ''}</span>`;
  }).join('');
  
  const matchCount = tags.filter(t => matchedTagsLower.includes(t.toLowerCase())).length;
  const badgeClass = matchCount > 0 
    ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600 hover:text-emerald-700' 
    : 'bg-violet-50 hover:bg-violet-100 text-violet-600 hover:text-violet-700';
  const matchIndicator = matchCount > 0 ? ` <span class="text-emerald-500">(${matchCount}✓)</span>` : '';
  
  // Add button for adding tags from theory page
  const addTagBtn = `<button type="button" class="marker-add-tag-btn inline-flex items-center text-[9px] px-1 py-0.5 rounded bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium transition-colors ml-1" onclick="showAddTagModal(${idx}, '${marker}')" title="Add tag from theory page"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></button>`;
  
  return ` <button type="button" class="marker-tags-toggle inline-flex items-center text-[10px] px-1.5 py-0.5 rounded-lg ${badgeClass} font-medium transition-colors" onclick="toggleMarkerTags('${tagId}')" title="Show/hide marker tags"><svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>${tags.length}${matchIndicator}</button><span id="${tagId}" class="marker-tags-list hidden ml-1 inline-flex flex-wrap items-center">${tagsHtml}${addTagBtn}</span>`;
}

/**
 * Update marker tags highlighting after theory is fetched
 * This re-renders the tags list with proper highlighting
 */
function updateMarkerTagsHighlighting(idx, marker, q) {
  const tagId = `marker-tags-${idx}-${marker}`;
  const tagsListEl = document.getElementById(tagId);
  if (!tagsListEl) return;
  
  const tags = getMarkerTags(q, marker);
  if (!tags || tags.length === 0) return;
  
  const matchedTags = getMatchedTagsForMarker(q, marker);
  const matchedTagsLower = matchedTags.map(t => t.toLowerCase());
  
  // Re-render tags with highlighting
  const tagsHtml = tags.map(t => {
    const isMatched = matchedTagsLower.includes(t.toLowerCase());
    const matchClass = isMatched 
      ? 'bg-emerald-200 text-emerald-800 ring-1 ring-emerald-400' 
      : 'bg-violet-100 text-violet-700';
    return `<span class="inline-block px-1.5 py-0.5 rounded ${matchClass} text-[9px] font-medium mr-1 mb-1">${html(t)}${isMatched ? ' ✓' : ''}</span>`;
  }).join('');
  
  // Add button for adding tags from theory page
  const addTagBtn = `<button type="button" class="marker-add-tag-btn inline-flex items-center text-[9px] px-1 py-0.5 rounded bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium transition-colors ml-1" onclick="showAddTagModal(${idx}, '${marker}')" title="Add tag from theory page"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></button>`;
  
  tagsListEl.innerHTML = tagsHtml + addTagBtn;
  
  // Also update the toggle button to show match count
  const toggleBtn = tagsListEl.previousElementSibling;
  if (toggleBtn && toggleBtn.classList.contains('marker-tags-toggle')) {
    const matchCount = tags.filter(t => matchedTagsLower.includes(t.toLowerCase())).length;
    const badgeClass = matchCount > 0 
      ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600 hover:text-emerald-700' 
      : 'bg-violet-50 hover:bg-violet-100 text-violet-600 hover:text-violet-700';
    
    // Update button classes
    toggleBtn.className = `marker-tags-toggle inline-flex items-center text-[10px] px-1.5 py-0.5 rounded-lg ${badgeClass} font-medium transition-colors`;
    
    // Update button content
    const matchIndicator = matchCount > 0 ? ` <span class="text-emerald-500">(${matchCount}✓)</span>` : '';
    toggleBtn.innerHTML = `<svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>${tags.length}${matchIndicator}`;
  }
}

/**
 * Toggle visibility of marker tags debug display
 */
function toggleMarkerTags(tagId) {
  const el = document.getElementById(tagId);
  if (el) {
    el.classList.toggle('hidden');
  }
}

/**
 * Show modal to add tags from theory page to marker
 */
function showAddTagModal(idx, marker) {
  const item = state.items[idx];
  if (!item) return;
  
  // Get page_id from cached theory block
  const theoryBlock = item.markerTheoryCache && item.markerTheoryCache[marker];
  if (!theoryBlock || !theoryBlock.page_id) {
    alert('Please fetch theory first by clicking the T button');
    return;
  }
  
  // Create or get modal
  let modal = document.getElementById('add-tag-modal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'add-tag-modal';
    modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden';
    modal.innerHTML = `
      <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full mx-4 max-h-[80vh] flex flex-col">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="font-semibold text-gray-900">Add Tag to Marker</h3>
          <button type="button" onclick="closeAddTagModal()" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="p-4 overflow-y-auto flex-1">
          <p class="text-sm text-gray-600 mb-3">Select tags from the theory page:</p>
          <div id="add-tag-modal-tags" class="flex flex-wrap gap-2">
            <span class="text-gray-400">Loading...</span>
          </div>
        </div>
        <div class="p-4 border-t border-gray-200 flex justify-end gap-2">
          <button type="button" onclick="closeAddTagModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
            Close
          </button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  }
  
  // Store current context
  modal.dataset.idx = idx;
  modal.dataset.marker = marker;
  modal.dataset.questionId = item.id;
  
  // Show modal
  modal.classList.remove('hidden');
  
  // Fetch available tags
  fetchAvailableTags(theoryBlock.page_id, idx, marker);
}

/**
 * Close the add tag modal
 */
function closeAddTagModal() {
  const modal = document.getElementById('add-tag-modal');
  if (modal) {
    modal.classList.add('hidden');
  }
}

/**
 * Fetch available tags from theory page
 */
function fetchAvailableTags(pageId, idx, marker) {
  const container = document.getElementById('add-tag-modal-tags');
  if (!container) return;
  
  container.innerHTML = '<span class="text-gray-400">Loading...</span>';
  
  fetch('{{ route("question.marker-tag.available") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
    },
    body: JSON.stringify({ page_id: pageId }),
  })
    .then(response => response.json())
    .then(data => {
      const tags = data.tags || [];
      const item = state.items[idx];
      const currentTags = getMarkerTags(item, marker);
      const currentTagsLower = currentTags.map(t => t.toLowerCase());
      
      if (tags.length === 0) {
        container.innerHTML = '<span class="text-gray-400">No tags available</span>';
        return;
      }
      
      container.innerHTML = tags.map(tag => {
        const isAlreadyAdded = currentTagsLower.includes(tag.toLowerCase());
        const btnClass = isAlreadyAdded
          ? 'bg-gray-200 text-gray-500 cursor-not-allowed'
          : 'bg-blue-100 hover:bg-blue-200 text-blue-700 cursor-pointer';
        return `<button type="button" 
          class="px-2 py-1 rounded text-xs font-medium transition-colors ${btnClass}"
          onclick="addTagToMarker(${idx}, '${marker}', '${html(tag)}')"
          ${isAlreadyAdded ? 'disabled' : ''}
        >${html(tag)}${isAlreadyAdded ? ' ✓' : ''}</button>`;
      }).join('');
    })
    .catch(error => {
      console.error(error);
      container.innerHTML = '<span class="text-red-500">Failed to load tags</span>';
    });
}

/**
 * Add a tag to a marker
 */
function addTagToMarker(idx, marker, tag) {
  const item = state.items[idx];
  if (!item) return;
  
  fetch('{{ route("question.marker-tag.add") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': CSRF_TOKEN,
    },
    body: JSON.stringify({
      question_id: item.id,
      marker: marker,
      tag: tag,
    }),
  })
    .then(response => response.json())
    .then(data => {
      if (data.marker_tags) {
        // Update the item's marker_tags
        if (!item.marker_tags) item.marker_tags = {};
        item.marker_tags[marker] = data.marker_tags;
        
        // Re-render the tags display
        updateMarkerTagsHighlighting(idx, marker, item);
        
        // Refresh the modal to show updated state
        const theoryBlock = item.markerTheoryCache && item.markerTheoryCache[marker];
        if (theoryBlock && theoryBlock.page_id) {
          fetchAvailableTags(theoryBlock.page_id, idx, marker);
        }
        
        // Persist state
        if (typeof persistState === 'function') persistState(state);
      }
    })
    .catch(error => {
      console.error(error);
      alert('Failed to add tag');
    });
}
</script>
