/* The shared snapshot is inert HTML. Only its request-local wrapper owns state. */
(() => {
    const mounted = new WeakSet();
    const searchMounted = new WeakSet();
    const scrolled = new WeakSet();
    const scheduled = new WeakSet();
    const fragmentRequests = new WeakMap();
    const contextSelector = '[data-theory-menu-context]';
    const fragmentSelector = '[data-theory-menu-fragment]';
    const categorySelector = '[data-theory-menu-category]';
    const own = (category, selector) => {
        const element = category.querySelector(selector);
        return element?.closest(categorySelector) === category ? element : null;
    };
    const findId = (root, attribute, id) => /^\d+$/.test(id ?? '')
        ? root.querySelector(`[${attribute}="${id}"]`) : null;

    function setExpanded(category, expanded, animate = false) {
        const children = own(category, '[data-theory-menu-children]');
        const toggle = own(category, '[data-theory-menu-toggle]');
        if (!children || !toggle) return;
        const changed = children.hidden === expanded;
        children.hidden = !expanded;
        toggle.setAttribute('aria-expanded', String(expanded));
        if (changed && animate && expanded && !window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches) {
            children.animate?.([
                {opacity: 0, transform: 'scale(.95)'},
                {opacity: 1, transform: 'scale(1)'},
            ], {duration: 150, easing: 'cubic-bezier(0, 0, .2, 1)'});
        }
    }

    function markActive(context, root) {
        const currentPage = context.dataset.theoryMenuCurrentPage;
        const selectedCategory = context.dataset.theoryMenuSelectedCategory;
        const page = findId(root, 'data-theory-menu-page', currentPage);
        const selected = findId(root, 'data-theory-menu-category', selectedCategory) || page?.closest(categorySelector);

        if (page) {
            page.dataset.theoryNavCurrentPage = 'true';
            page.setAttribute('aria-current', 'page');
        }
        if (selected) {
            const link = own(selected, '[data-theory-menu-category-link]');
            if (link) {
                link.dataset.theoryNavActive = 'true';
                if (!page) link.setAttribute('aria-current', 'page');
            }
        }
        for (const branch of [selected, page?.closest(categorySelector)]) {
            for (let category = branch; category; category = category.parentElement.closest(categorySelector)) {
                if (category.hasAttribute('data-theory-menu-active-branch')) break;
                category.setAttribute('data-theory-menu-active-branch', '');
                own(category, '[data-theory-menu-category-link]')?.setAttribute('data-theory-menu-branch-link', '');
                setExpanded(category, true);
            }
        }
    }

    function mountSearch(searchRoot) {
        if (searchMounted.has(searchRoot) || !window.GramlyzeTheorySearch) return;
        const sidebar = searchRoot.closest('[data-theory-sidebar]');
        const scrollRoot = searchRoot.closest(fragmentSelector)?.querySelector('[data-theory-sidebar-scroll]')
            || sidebar?.querySelector('[data-theory-sidebar-scroll]');
        const input = searchRoot.querySelector('[data-theory-sidebar-search-input]');
        const clearButton = searchRoot.querySelector('[data-theory-sidebar-search-clear]');
        const count = searchRoot.querySelector('[data-theory-sidebar-search-count]');
        const empty = searchRoot.querySelector('[data-theory-sidebar-search-empty]');
        if (!input || !sidebar || !scrollRoot) return;

        searchMounted.add(searchRoot);
        searchRoot.dataset.theorySearchReady = 'true';
        const {normalize, rank, createSorter} = window.GramlyzeTheorySearch;
        let index;
        const searchIndex = () => {
            if (index) return index;
            const topNodes = Array.from(scrollRoot.querySelectorAll('[data-theory-sidebar-top-node]'));
            const allNodes = Array.from(scrollRoot.querySelectorAll('[data-theory-sidebar-node]'));
            const titles = new Map(allNodes.map(node => [node,
                node.querySelector('[data-theory-sidebar-highlight]')?.textContent || '']));
            const parents = new Map(allNodes.map(node => [node,
                node.parentElement.closest('[data-theory-sidebar-node]')]));
            const highlights = new Map(Array.from(scrollRoot.querySelectorAll('[data-theory-sidebar-highlight]'),
                element => [element, element.textContent]));
            return index = {topNodes, allNodes, titles, parents, highlights, sortNodes: createSorter(allNodes)};
        };

        function highlightText(element, original, query) {
            const lowerOriginal = original.toLocaleLowerCase();
            const lowerQuery = query.toLocaleLowerCase();
            let cursor = 0;
            let matchIndex = lowerOriginal.indexOf(lowerQuery);
            if (!query || matchIndex === -1) {
                if (element.querySelector('mark')) element.textContent = original;
                return;
            }
            element.textContent = '';
            while (matchIndex !== -1) {
                if (matchIndex > cursor) element.append(document.createTextNode(original.slice(cursor, matchIndex)));
                const mark = document.createElement('mark');
                mark.className = 'rounded-md bg-amber/30 px-1 py-0.5 text-inherit';
                mark.textContent = original.slice(matchIndex, matchIndex + query.length);
                element.append(mark);
                cursor = matchIndex + query.length;
                matchIndex = lowerOriginal.indexOf(lowerQuery, cursor);
            }
            if (cursor < original.length) element.append(document.createTextNode(original.slice(cursor)));
        }

        function applySearch() {
            const query = normalize(input.value);
            sidebar.dataset.theorySidebarSearching = query ? 'true' : 'false';
            // Counts and order already came from SSR. Build the index only when
            // the learner actually searches or the browser restores a query.
            if (!query && !index) return;
            const {topNodes, allNodes, titles, parents, highlights, sortNodes} = searchIndex();
            const scores = new Map(allNodes.map(node => [node, rank(titles.get(node), query)]));
            // Keep matching lessons' ancestors and rank the branch by its best title.
            [...allNodes].reverse().forEach(node => {
                const parent = parents.get(node);
                if (scores.has(parent)) scores.set(parent, Math.max(scores.get(parent), scores.get(node)));
            });
            allNodes.forEach(node => node.classList.toggle('hidden', Boolean(query) && scores.get(node) <= 0));
            sortNodes(node => scores.get(node));
            highlights.forEach((original, element) => highlightText(element, original, query));
            const visible = topNodes.filter(node => !node.classList.contains('hidden')).length;
            if (count) count.textContent = `${visible} / ${topNodes.length}`;
            clearButton?.classList.toggle('hidden', !query);
            empty?.classList.toggle('hidden', visible !== 0);
        }

        function clearSearch() {
            input.value = '';
            applySearch();
        }
        input.addEventListener('input', applySearch);
        clearButton?.addEventListener('click', () => {
            clearSearch();
            input.focus();
        });
        new MutationObserver(() => {
            if (sidebar.dataset.collapsed === 'true' && input.value) clearSearch();
        }).observe(sidebar, {attributes: true, attributeFilter: ['data-collapsed']});
        applySearch();
    }

    window.initTheorySidebarSearch = (scope = document) => {
        scope.querySelectorAll('[data-theory-sidebar-search]').forEach(mountSearch);
    };

    function scrollToActive(container, force = false) {
        if ((!force && scrolled.has(container)) || !container.getClientRects().length
            || container.closest('[data-theory-sidebar]')?.dataset.theorySidebarSearching === 'true') return;
        let active = container.querySelector('[data-theory-nav-current-page="true"]')
            || container.querySelector('[data-theory-nav-active="true"]');
        if (!active) return;
        // A collapsed sidebar hides lessons. Its visible active ancestor is
        // still useful as a scroll target until the learner expands the rail.
        if (!active.getClientRects().length) {
            for (let branch = active.closest(categorySelector); branch; branch = branch.parentElement.closest(categorySelector)) {
                const link = own(branch, '[data-theory-menu-category-link]');
                if (link?.getClientRects().length) {active = link; break;}
            }
            if (!active.getClientRects().length) return;
        }
        const targetTop = container.scrollTop + active.getBoundingClientRect().top - container.getBoundingClientRect().top - 8;
        container.scrollTo({top: Math.max(0, targetTop), behavior: 'auto'});
        scrolled.add(container);
    }

    window.scrollTheorySidebarToActive = (scope = document) => {
        scope.querySelectorAll('[data-theory-sidebar-scroll]').forEach(container => scrollToActive(container, true));
    };

    function scheduleScroll(scope = document, force = false) {
        scope.querySelectorAll('[data-theory-sidebar-scroll]').forEach(container => {
            if (scheduled.has(container)) return;
            scheduled.add(container);
            window.requestAnimationFrame(() => {
                scheduled.delete(container);
                scrollToActive(container, force);
            });
        });
    }

    window.initTheorySidebarAutoscroll = (scope = document) => scheduleScroll(scope, true);
    window.mountTheoryMenu = context => {
        const root = context?.querySelector(fragmentSelector);
        if (!root || mounted.has(root)) return;
        mounted.add(root);
        markActive(context, root);
        root.addEventListener('click', event => {
            const toggle = event.target.closest?.('[data-theory-menu-toggle]');
            if (!toggle || !root.contains(toggle)) return;
            const category = toggle.closest(categorySelector);
            if (category) setExpanded(category, toggle.getAttribute('aria-expanded') !== 'true', true);
        });
        window.initTheorySidebarSearch(root);
        scheduleScroll(root);
    };
    window.showTheoryMenu = context => {
        window.mountTheoryMenu(context);
        scheduleScroll(context);
    };
    window.loadTheoryMenu = (context, content) => {
        if (fragmentRequests.has(content)) return fragmentRequests.get(content);
        const pending = (async () => {
            const response = await fetch(context.dataset.theoryMenuUrl, {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            content.innerHTML = await response.text();
        })().catch(error => {
            fragmentRequests.delete(content); // A failed request may be retried.
            throw error;
        });
        fragmentRequests.set(content, pending);
        return pending;
    };

    window.animateTheoryMainFlip = (main, before) => {
        if (!main || !before) return;
        const deltaX = before.left - main.getBoundingClientRect().left;
        if (Math.abs(deltaX) < 1) return;
        main.style.transition = 'none';
        main.style.transform = `translate3d(${deltaX}px, 0, 0)`;
        main.getBoundingClientRect();
        window.requestAnimationFrame(() => {
            main.style.transition = 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)';
            main.style.transform = 'translate3d(0, 0, 0)';
            window.setTimeout(() => {
                main.style.transition = '';
                main.style.transform = '';
            }, 240);
        });
    };

    const init = () => {
        document.querySelectorAll(contextSelector).forEach(window.mountTheoryMenu);
        scheduleScroll();
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once: true});
    else init();
    document.addEventListener('alpine:initialized', init, {once: true});
    // Preserve user-closed branches and scroll offsets on BFCache restoration.
    window.addEventListener('pageshow', init);
    window.matchMedia?.('(min-width: 1024px)')?.addEventListener?.('change', () => scheduleScroll());
})();
