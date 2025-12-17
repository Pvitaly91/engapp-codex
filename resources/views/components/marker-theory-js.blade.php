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

  if (!item.markerTheoryMatch || typeof item.markerTheoryMatch !== 'object') {
    item.markerTheoryMatch = {};
  }

  // If already in cache, just show it
  if (Object.prototype.hasOwnProperty.call(item.markerTheoryCache, marker)) {
    if (!item.markerTheoryMatch[marker]) {
      const cachedBlock = item.markerTheoryCache[marker];
      item.markerTheoryMatch[marker] = {
        block: cachedBlock,
        matched_tag_ids: Array.isArray(cachedBlock?.matched_tag_ids) ? cachedBlock.matched_tag_ids : [],
        matched_tag_names: Array.isArray(cachedBlock?.matched_tag_names)
          ? cachedBlock.matched_tag_names
          : (cachedBlock?.matched_tags || []),
      };
    }
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
      item.markerTheoryMatch[marker] = {
        block: theoryBlock,
        matched_tag_ids: Array.isArray(data?.matched_tag_ids) ? data.matched_tag_ids : [],
        matched_tag_names: Array.isArray(data?.matched_tag_names)
          ? data.matched_tag_names
          : (theoryBlock?.matched_tag_names || theoryBlock?.matched_tags || []),
      };
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

  const matchedNames = (block.matched_tag_names && block.matched_tag_names.length > 0)
    ? block.matched_tag_names
    : (block.matched_tags || []);

  const matchedTagsHtml = matchedNames.length > 0
    ? `<div class="mt-2 text-xs text-cyan-600">Matched tags: ${matchedNames.map(t => html(t)).join(', ')}</div>`
    : `<div class="mt-2 text-xs text-cyan-500">No tag match</div>`;

  // Add prominent link to full theory page if available
  const pageLink = block.page_url 
    ? `<a href="${html(block.page_url)}" target="_blank" class="mt-3 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 text-white font-semibold text-sm shadow-md hover:shadow-lg hover:from-cyan-600 hover:to-sky-600 transition-all duration-200 transform hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        Open theory page
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
        </svg>
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
 * Normalize marker tag payload into objects with id/name
 */
function normalizeMarkerTag(tag) {
  if (tag && typeof tag === 'object') {
    return {
      id: typeof tag.id === 'number' ? tag.id : null,
      name: tag.name || '',
      category: tag.category || null,
    };
  }

  return {
    id: null,
    name: typeof tag === 'string' ? tag : '',
    category: null,
  };
}

function getMarkerTagObjects(q, marker) {
  const raw = getMarkerTags(q, marker);
  return raw.map(normalizeMarkerTag);
}

function getMatchedTagIdsForMarker(q, marker) {
  if (!q.markerTheoryMatch || !q.markerTheoryMatch[marker]) return [];
  const match = q.markerTheoryMatch[marker];
  if (Array.isArray(match.matched_tag_ids)) return match.matched_tag_ids;
  return [];
}

function getMatchedTagNamesForMarker(q, marker) {
  if (!q.markerTheoryMatch || !q.markerTheoryMatch[marker]) return [];
  const match = q.markerTheoryMatch[marker];
  if (Array.isArray(match.matched_tag_names)) return match.matched_tag_names;

  const block = match.block || (q.markerTheoryCache ? q.markerTheoryCache[marker] : null);
  if (block?.matched_tag_names && Array.isArray(block.matched_tag_names)) return block.matched_tag_names;
  if (block?.matched_tags && Array.isArray(block.matched_tags)) return block.matched_tags;

  return [];
}

/**
 * Render marker tags inline for debugging
 * Shows tags in a collapsible badge next to markers
 * Highlights tags that match with theory blocks
 * Also shows "Add tags" button for questions with linked theory blocks
 */
function renderMarkerTagsDebug(q, marker, idx) {
  const tags = getMarkerTagObjects(q, marker);
  const hasTags = tags && tags.length > 0;
  const hasTheoryBlock = q.theory_block?.uuid;
  
  // For non-admin users: don't show tag UI at all
  // Admin-only: show tags toggle, highlighting, and add button
  const isAdmin = typeof IS_ADMIN !== 'undefined' && IS_ADMIN === true;
  
  if (!isAdmin) {
    // Non-admin users don't see tag management UI
    return '';
  }
  
  // Admin-only code below
  // If no tags and no theory block, don't render anything
  if (!hasTags && !hasTheoryBlock) return '';
  
  const matchedTagIds = getMatchedTagIdsForMarker(q, marker);
  const matchedNames = getMatchedTagNamesForMarker(q, marker).map(t => t.toLowerCase());
  
  const tagId = `marker-tags-${idx}-${marker}`;
  
  // If we have tags, render them
  if (hasTags) {
    const tagsHtml = tags.map(tag => {
      const isMatched = (tag.id && matchedTagIds.includes(tag.id)) || matchedNames.includes(tag.name.toLowerCase());
      const matchClass = isMatched
        ? 'matched-tag bg-emerald-200 text-emerald-800 ring-1 ring-emerald-400'
        : 'bg-violet-100 text-violet-700';
      return `<span class="inline-block px-1.5 py-0.5 rounded ${matchClass} text-[9px] font-medium mr-1 mb-1">${html(tag.name)}${isMatched ? ' ✓' : ''}</span>`;
    }).join('');

    const matchCount = tags.filter(tag => (tag.id && matchedTagIds.includes(tag.id)) || matchedNames.includes(tag.name.toLowerCase())).length;
    const badgeClass = matchCount > 0 
      ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600 hover:text-emerald-700' 
      : 'bg-violet-50 hover:bg-violet-100 text-violet-600 hover:text-violet-700';
    const matchIndicator = matchCount > 0 ? ` <span class="text-emerald-500">(${matchCount}✓)</span>` : '';
    
    // Add "Додати теги" button (admin only)
    const addTagsBtn = renderAddTagsButton(q, marker, idx);

    return ` <button type="button" class="marker-tags-toggle inline-flex items-center text-[10px] px-1.5 py-0.5 rounded-lg ${badgeClass} font-medium transition-colors" onclick="toggleMarkerTags('${tagId}')" title="Show/hide marker tags"><svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>${tags.length}${matchIndicator}</button><span id="${tagId}" class="marker-tags-list hidden ml-1 inline-flex flex-wrap items-center">${tagsHtml}${addTagsBtn}</span>`;
  }
  
  // No tags but has theory block - just show the "Add tags" button directly (admin only)
  const addTagsBtn = renderAddTagsButton(q, marker, idx);
  return ` <span id="${tagId}" class="marker-tags-list ml-1 inline-flex flex-wrap items-center">${addTagsBtn}</span>`;
}

/**
 * Render the "Додати теги" button for a marker (admin-only)
 */
function renderAddTagsButton(q, marker, idx) {
  // Only render for admins
  const isAdmin = typeof IS_ADMIN !== 'undefined' && IS_ADMIN === true;
  if (!isAdmin) return '';
  
  return `<button type="button" class="add-marker-tags-btn ml-1 inline-flex items-center text-[9px] px-1.5 py-0.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700 font-medium transition-colors" data-question-id="${q.id}" data-marker="${marker}" data-idx="${idx}" onclick="openAddTagsModal(${q.id}, '${marker}', ${idx})" title="Додати теги з теорії"><svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>+</button>`;
}

/**
 * Update marker tags highlighting after theory is fetched
 * This re-renders the tags list with proper highlighting (admin-only)
 */
function updateMarkerTagsHighlighting(idx, marker, q) {
  // Only for admins
  const isAdmin = typeof IS_ADMIN !== 'undefined' && IS_ADMIN === true;
  if (!isAdmin) return;
  
  const tagId = `marker-tags-${idx}-${marker}`;
  const tagsListEl = document.getElementById(tagId);
  if (!tagsListEl) return;
  
  const tags = getMarkerTagObjects(q, marker);
  if (!tags || tags.length === 0) return;

  const matchedTagIds = getMatchedTagIdsForMarker(q, marker);
  const matchedNames = getMatchedTagNamesForMarker(q, marker).map(t => t.toLowerCase());
  
  // Re-render tags with highlighting
  const tagsHtml = tags.map(tag => {
    const isMatched = (tag.id && matchedTagIds.includes(tag.id)) || matchedNames.includes(tag.name.toLowerCase());
    const matchClass = isMatched
      ? 'matched-tag bg-emerald-200 text-emerald-800 ring-1 ring-emerald-400'
      : 'bg-violet-100 text-violet-700';
    return `<span class="inline-block px-1.5 py-0.5 rounded ${matchClass} text-[9px] font-medium mr-1 mb-1">${html(tag.name)}${isMatched ? ' ✓' : ''}</span>`;
  }).join('');
  
  // Add the "Додати теги" button (admin only)
  const addTagsBtn = renderAddTagsButton(q, marker, idx);
  
  tagsListEl.innerHTML = tagsHtml + addTagsBtn;
  
  // Also update the toggle button to show match count
  const toggleBtn = tagsListEl.previousElementSibling;
  if (toggleBtn && toggleBtn.classList.contains('marker-tags-toggle')) {
    const matchCount = tags.filter(tag => (tag.id && matchedTagIds.includes(tag.id)) || matchedNames.includes(tag.name.toLowerCase())).length;
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

// ========== Add Tags Modal Functionality ==========

// Cache for available tags per question+marker
const availableTagsCache = {};

/**
 * Open the modal to add tags from theory page
 */
async function openAddTagsModal(questionId, marker, idx) {
  const item = state.items[idx];
  if (!item) return;
  
  // Find question UUID
  const questionUuid = item.uuid;
  if (!questionUuid) {
    showTagsToast('Помилка: UUID питання не знайдено', 'error');
    return;
  }
  
  // Create and show modal
  showAddTagsModal(questionId, marker, idx, questionUuid);
  
  // Fetch available tags
  const cacheKey = `${questionUuid}-${marker}`;
  let availableTags = availableTagsCache[cacheKey];
  
  if (!availableTags) {
    updateModalLoading(true);
    try {
      const response = await fetch(`/admin/api/v2/questions/${questionUuid}/markers/${marker}/available-theory-tags`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
        },
      });
      
      if (!response.ok) {
        throw new Error('Failed to load available tags');
      }
      
      const data = await response.json();
      availableTags = data.tags || [];
      availableTagsCache[cacheKey] = availableTags;
    } catch (error) {
      console.error(error);
      updateModalError('Не вдалося завантажити теги з теорії');
      return;
    } finally {
      updateModalLoading(false);
    }
  }
  
  // Get current marker tags to exclude them (tags are strings in marker_tags)
  const currentTags = getMarkerTags(item, marker) || [];
  const currentTagNamesLower = currentTags.map(t => (typeof t === 'string' ? t : t.name || '').toLowerCase());
  
  // Filter out already added tags
  const newTags = availableTags.filter(t => !currentTagNamesLower.includes((t.name || '').toLowerCase()));
  
  if (newTags.length === 0) {
    updateModalEmpty();
  } else {
    renderTagsList(newTags);
  }
}

/**
 * Show the add tags modal
 */
function showAddTagsModal(questionId, marker, idx, questionUuid) {
  // Remove existing modal if any
  const existingModal = document.getElementById('add-tags-modal');
  if (existingModal) {
    existingModal.remove();
  }
  
  const modalHtml = `
    <div id="add-tags-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" onclick="if(event.target === this) closeAddTagsModal()">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[80vh] flex flex-col">
        <div class="p-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Додати теги для маркера ${html(marker)}</h3>
            <button type="button" onclick="closeAddTagsModal()" class="p-1 rounded-lg hover:bg-gray-100 transition-colors">
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="mt-2">
            <input type="text" id="add-tags-search" placeholder="Пошук тегів..." oninput="filterTagsList(this.value)" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
          </div>
          <div class="mt-2 flex items-center gap-2">
            <button type="button" onclick="selectAllTags()" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Вибрати всі</button>
            <span class="text-gray-300">|</span>
            <button type="button" onclick="deselectAllTags()" class="text-xs text-gray-600 hover:text-gray-700 font-medium">Зняти вибір</button>
          </div>
        </div>
        <div id="add-tags-content" class="flex-1 overflow-y-auto p-4">
          <div class="flex items-center justify-center py-8">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
          </div>
        </div>
        <div class="p-4 border-t border-gray-200 flex justify-end gap-2">
          <button type="button" onclick="closeAddTagsModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
            Скасувати
          </button>
          <button type="button" id="add-tags-submit" onclick="submitAddTags(${questionId}, '${marker}', ${idx}, '${questionUuid}')" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            Додати вибрані
          </button>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modalHtml);
}

/**
 * Close the add tags modal
 */
function closeAddTagsModal() {
  const modal = document.getElementById('add-tags-modal');
  if (modal) {
    modal.remove();
  }
}

/**
 * Update modal to show loading state
 */
function updateModalLoading(loading) {
  const content = document.getElementById('add-tags-content');
  if (!content) return;
  
  if (loading) {
    content.innerHTML = `
      <div class="flex items-center justify-center py-8">
        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
      </div>
    `;
  }
}

/**
 * Update modal to show error
 */
function updateModalError(message) {
  const content = document.getElementById('add-tags-content');
  if (!content) return;
  
  content.innerHTML = `
    <div class="text-center py-8">
      <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
      </svg>
      <p class="text-red-600 font-medium">${html(message)}</p>
    </div>
  `;
}

/**
 * Update modal to show empty state
 */
function updateModalEmpty() {
  const content = document.getElementById('add-tags-content');
  if (!content) return;
  
  content.innerHTML = `
    <div class="text-center py-8">
      <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
      </svg>
      <p class="text-gray-600 font-medium">Немає доступних тегів для додавання</p>
      <p class="text-gray-500 text-sm mt-1">Всі теги з теорії вже додані до цього маркера</p>
    </div>
  `;
}

/**
 * Render tags list in modal
 */
function renderTagsList(tags) {
  const content = document.getElementById('add-tags-content');
  if (!content) return;
  
  const tagsHtml = tags.map(tag => {
    const tagNameLower = (tag.name || '').toLowerCase();
    return `
    <label class="tag-item flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors" data-name="${html(tagNameLower)}">
      <input type="checkbox" value="${tag.id}" class="tag-checkbox w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500" onchange="updateSubmitButton()">
      <div class="flex-1">
        <span class="text-sm font-medium text-gray-900">${html(tag.name)}</span>
        ${tag.category ? `<span class="ml-2 text-xs text-gray-500">(${html(tag.category)})</span>` : ''}
      </div>
    </label>
  `;
  }).join('');
  
  content.innerHTML = `<div class="space-y-1">${tagsHtml}</div>`;
}

/**
 * Filter tags list based on search input
 */
function filterTagsList(query) {
  const items = document.querySelectorAll('.tag-item');
  const q = query.toLowerCase().trim();
  
  items.forEach(item => {
    const name = item.dataset.name || '';
    if (q === '' || name.includes(q)) {
      item.classList.remove('hidden');
    } else {
      item.classList.add('hidden');
    }
  });
}

/**
 * Select all visible tags
 */
function selectAllTags() {
  const checkboxes = document.querySelectorAll('.tag-item:not(.hidden) .tag-checkbox');
  checkboxes.forEach(cb => cb.checked = true);
  updateSubmitButton();
}

/**
 * Deselect all tags
 */
function deselectAllTags() {
  const checkboxes = document.querySelectorAll('.tag-checkbox');
  checkboxes.forEach(cb => cb.checked = false);
  updateSubmitButton();
}

/**
 * Update submit button state based on selection
 */
function updateSubmitButton() {
  const submitBtn = document.getElementById('add-tags-submit');
  const checkedCount = document.querySelectorAll('.tag-checkbox:checked').length;
  
  if (submitBtn) {
    submitBtn.disabled = checkedCount === 0;
    submitBtn.textContent = checkedCount > 0 ? `Додати вибрані (${checkedCount})` : 'Додати вибрані';
  }
}

/**
 * Submit selected tags
 */
async function submitAddTags(questionId, marker, idx, questionUuid) {
  const checkboxes = document.querySelectorAll('.tag-checkbox:checked');
  const tagIds = Array.from(checkboxes).map(cb => parseInt(cb.value, 10));
  
  if (tagIds.length === 0) return;
  
  const submitBtn = document.getElementById('add-tags-submit');
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>Додаємо...';
  }
  
  try {
    const response = await fetch(`/admin/api/v2/questions/${questionUuid}/markers/${marker}/add-tags-from-theory-page`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF_TOKEN,
      },
      body: JSON.stringify({ tag_ids: tagIds }),
    });
    
    if (!response.ok) {
      const data = await response.json();
      throw new Error(data.message || 'Failed to add tags');
    }
    
    const data = await response.json();
    
    // Update local state
    const item = state.items[idx];
    if (item) {
      // Update marker_tags in state
      if (!item.marker_tags) item.marker_tags = {};
      item.marker_tags[marker] = data.marker_tags.map(normalizeMarkerTag);
      
      // Clear theory cache to force refetch
      if (item.markerTheoryCache) {
        delete item.markerTheoryCache[marker];
      }
      
      // Invalidate available tags cache
      delete availableTagsCache[`${questionUuid}-${marker}`];
      
      // Re-render the card to update tags display
      if (typeof rerenderCard === 'function') {
        rerenderCard(idx);
      } else {
        // Fallback: update tags display manually
        updateMarkerTagsHighlighting(idx, marker, item);
      }
      
      // Optionally refetch marker theory
      if (typeof fetchMarkerTheory === 'function') {
        fetchMarkerTheory(idx, marker);
      }
      
      // Persist state
      if (typeof persistState === 'function') {
        persistState(state);
      }
    }
    
    // Close modal and show success
    closeAddTagsModal();
    showTagsToast(`Додано ${data.added} тегів`, 'success');
    
  } catch (error) {
    console.error(error);
    showTagsToast(error.message || 'Помилка при додаванні тегів', 'error');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Додати вибрані';
    }
  }
}

/**
 * Show a toast notification
 */
function showTagsToast(message, type = 'info') {
  // Remove existing toast
  const existingToast = document.getElementById('tags-toast');
  if (existingToast) {
    existingToast.remove();
  }
  
  const colors = {
    success: 'bg-emerald-500',
    error: 'bg-red-500',
    info: 'bg-indigo-500',
  };
  
  const toastHtml = `
    <div id="tags-toast" class="fixed bottom-4 right-4 z-50 ${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 animate-fade-in">
      ${type === 'success' ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' : ''}
      ${type === 'error' ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>' : ''}
      <span class="font-medium">${html(message)}</span>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', toastHtml);
  
  // Auto-remove after 3 seconds
  setTimeout(() => {
    const toast = document.getElementById('tags-toast');
    if (toast) {
      toast.classList.add('opacity-0', 'transition-opacity');
      setTimeout(() => toast.remove(), 300);
    }
  }, 3000);
}
</script>
