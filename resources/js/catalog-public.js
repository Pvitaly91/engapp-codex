// Static public-shell behavior; Livewire is the sole Alpine owner.
function initStickyShellHeader() {
    const shell = document.getElementById('catalog-shell');
    const sentinel = document.getElementById('site-header-sentinel');
    const desktopQuery = window.matchMedia('(min-width: 1024px)');

    if (!shell || !sentinel) {
        return;
    }

    let observer = null;
    let fallbackBound = false;
    let lastState = null;

    const applyStickyState = (isStuck) => {
        const nextState = desktopQuery.matches && Boolean(isStuck);

        if (lastState === nextState) {
            return;
        }

        lastState = nextState;
        shell.classList.toggle('is-header-stuck', nextState);
    };

    const syncStickyFallback = () => {
        applyStickyState(Math.round(sentinel.getBoundingClientRect().top) < 0);
    };

    const bindFallback = () => {
        if (fallbackBound) {
            return;
        }

        fallbackBound = true;

        const requestSync = () => window.requestAnimationFrame(syncStickyFallback);
        requestSync();
        window.addEventListener('scroll', requestSync, { passive: true });
        window.addEventListener('resize', requestSync, { passive: true });
    };

    const connectObserver = () => {
        if (observer) {
            observer.disconnect();
            observer = null;
        }

        applyStickyState(false);

        if (!desktopQuery.matches) {
            return;
        }

        if (typeof IntersectionObserver !== 'function') {
            bindFallback();
            return;
        }

        observer = new IntersectionObserver(([entry]) => {
            applyStickyState(!entry.isIntersecting && entry.boundingClientRect.top < 0);
        }, {
            threshold: [0, 1],
        });

        observer.observe(sentinel);
        window.requestAnimationFrame(syncStickyFallback);
    };

    connectObserver();

    if (typeof desktopQuery.addEventListener === 'function') {
        desktopQuery.addEventListener('change', connectObserver);
    } else if (typeof desktopQuery.addListener === 'function') {
        desktopQuery.addListener(connectObserver);
    }
}

function buildShellRandomShapes() {
    const layer = document.getElementById('shell-random-shapes');

    if (!layer) {
        return;
    }

    const width = layer.offsetWidth;
    const height = layer.offsetHeight;

    if (!width || !height) {
        return;
    }

    const randomBetween = (min, max) => min + (Math.random() * (max - min));
    const pick = (items) => items[Math.floor(Math.random() * items.length)];
    const shuffle = (items) => {
        const copy = [...items];

        for (let index = copy.length - 1; index > 0; index -= 1) {
            const swapIndex = Math.floor(Math.random() * (index + 1));
            [copy[index], copy[swapIndex]] = [copy[swapIndex], copy[index]];
        }

        return copy;
    };
    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

    const compact = width < 640;
    const medium = width >= 640 && width < 1024;
    const sizeScale = compact ? 0.68 : (medium ? 0.82 : 1);
    const columns = compact ? 3 : (medium ? 4 : (width >= 1280 ? 6 : 5));
    const rows = compact
        ? Math.max(7, Math.min(13, Math.round(height / 150)))
        : Math.max(6, Math.min(10, Math.round(height / 175)));
    const cellWidth = width / columns;
    const cellHeight = height / rows;
    const shapes = [];
    const scaledBetween = (min, max) => randomBetween(min * sizeScale, max * sizeScale);
    const accentBetween = (min, max) => randomBetween(min * sizeScale * 0.58, max * sizeScale * 0.58);

    const shapeFactories = [
        () => {
            const size = scaledBetween(58, 124);
            const stroke = scaledBetween(8, 18);
            return {
                width: size,
                height: size,
                borderRadius: '999px',
                border: `${stroke}px solid ${pick([
                    'rgba(47, 103, 177, 0.24)',
                    'rgba(116, 169, 240, 0.30)',
                    'rgba(156, 163, 175, 0.24)',
                    'rgba(125, 211, 252, 0.28)',
                ])}`,
                background: 'transparent',
            };
        },
        () => {
            const width = scaledBetween(72, 152);
            const height = scaledBetween(14, 34);
            return {
                width,
                height,
                borderRadius: '999px',
                background: pick([
                    'rgba(47, 103, 177, 0.18)',
                    'rgba(245, 155, 47, 0.18)',
                    'rgba(16, 185, 129, 0.18)',
                    'rgba(244, 114, 182, 0.14)',
                    'rgba(56, 189, 248, 0.16)',
                ]),
            };
        },
        () => {
            const width = scaledBetween(46, 92);
            const height = scaledBetween(46, 92);
            return {
                width,
                height,
                borderRadius: `${scaledBetween(18, 34)}px`,
                border: `${scaledBetween(6, 12)}px solid ${pick([
                    'rgba(16, 185, 129, 0.24)',
                    'rgba(125, 211, 252, 0.24)',
                    'rgba(217, 70, 239, 0.18)',
                    'rgba(251, 191, 36, 0.22)',
                ])}`,
                background: 'transparent',
            };
        },
        () => {
            const width = scaledBetween(38, 84);
            const height = scaledBetween(38, 84);
            return {
                width,
                height,
                borderRadius: `${scaledBetween(16, 28)}px`,
                background: pick([
                    'rgba(245, 155, 47, 0.12)',
                    'rgba(16, 185, 129, 0.12)',
                    'rgba(47, 103, 177, 0.10)',
                    'rgba(251, 113, 133, 0.10)',
                ]),
            };
        },
        () => {
            const width = scaledBetween(18, 30);
            const height = scaledBetween(110, 190);
            return {
                width,
                height,
                borderRadius: '999px',
                background: pick([
                    'rgba(245, 155, 47, 0.14)',
                    'rgba(47, 103, 177, 0.14)',
                    'rgba(16, 185, 129, 0.12)',
                ]),
            };
        },
        () => {
            const width = scaledBetween(86, 156);
            const height = scaledBetween(42, 74);
            return {
                width,
                height,
                borderRadius: `${scaledBetween(20, 30)}px`,
                border: `${scaledBetween(8, 12)}px solid ${pick([
                    'rgba(167, 139, 250, 0.18)',
                    'rgba(125, 211, 252, 0.18)',
                    'rgba(47, 103, 177, 0.16)',
                ])}`,
                background: 'transparent',
            };
        },
        () => {
            const size = scaledBetween(72, 150);
            return {
                width: size,
                height: size,
                borderRadius: '999px',
                background: pick([
                    'rgba(47, 103, 177, 0.08)',
                    'rgba(245, 155, 47, 0.08)',
                    'rgba(16, 185, 129, 0.08)',
                ]),
                filter: `blur(${scaledBetween(1, 3)}px)`,
            };
        },
        () => {
            const width = scaledBetween(62, 118);
            const height = scaledBetween(54, 104);
            return {
                width,
                height,
                background: pick([
                    'rgba(245, 155, 47, 0.14)',
                    'rgba(47, 103, 177, 0.14)',
                    'rgba(16, 185, 129, 0.14)',
                    'rgba(244, 114, 182, 0.12)',
                ]),
                clipPath: 'polygon(50% 0%, 0% 100%, 100% 100%)',
            };
        },
        () => {
            const size = scaledBetween(54, 96);
            return {
                width: size,
                height: size,
                background: pick([
                    'rgba(125, 211, 252, 0.16)',
                    'rgba(167, 139, 250, 0.14)',
                    'rgba(251, 191, 36, 0.14)',
                    'rgba(16, 185, 129, 0.14)',
                ]),
                clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)',
            };
        },
        () => {
            const width = scaledBetween(72, 124);
            const height = scaledBetween(62, 108);
            return {
                width,
                height,
                background: pick([
                    'rgba(47, 103, 177, 0.12)',
                    'rgba(245, 155, 47, 0.12)',
                    'rgba(16, 185, 129, 0.12)',
                    'rgba(217, 70, 239, 0.10)',
                ]),
                clipPath: 'polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%)',
            };
        },
        () => {
            const size = scaledBetween(52, 92);
            return {
                width: size,
                height: size,
                background: pick([
                    'rgba(245, 155, 47, 0.16)',
                    'rgba(125, 211, 252, 0.16)',
                    'rgba(16, 185, 129, 0.14)',
                    'rgba(217, 70, 239, 0.12)',
                ]),
                clipPath: 'polygon(35% 0%, 65% 0%, 65% 35%, 100% 35%, 100% 65%, 65% 65%, 65% 100%, 35% 100%, 35% 65%, 0% 65%, 0% 35%, 35% 35%)',
            };
        },
        () => {
            const width = scaledBetween(64, 120);
            const height = scaledBetween(58, 110);
            return {
                width,
                height,
                background: pick([
                    'rgba(47, 103, 177, 0.12)',
                    'rgba(245, 155, 47, 0.12)',
                    'rgba(16, 185, 129, 0.12)',
                    'rgba(251, 113, 133, 0.10)',
                ]),
                clipPath: 'polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%)',
            };
        },
        () => {
            const size = scaledBetween(44, 82);
            return {
                width: size,
                height: size,
                borderRadius: `${scaledBetween(14, 22)}px`,
                border: `${scaledBetween(5, 9)}px solid ${pick([
                    'rgba(125, 211, 252, 0.24)',
                    'rgba(251, 191, 36, 0.24)',
                    'rgba(16, 185, 129, 0.22)',
                    'rgba(217, 70, 239, 0.18)',
                ])}`,
                background: 'transparent',
                transformOverride: `rotate(${randomBetween(38, 52)}deg)`,
            };
        }
    ];

    const accentFactories = [
        () => {
            const size = accentBetween(18, 42);
            return {
                width: size,
                height: size,
                borderRadius: '999px',
                background: pick([
                    'rgba(47, 103, 177, 0.14)',
                    'rgba(245, 155, 47, 0.14)',
                    'rgba(16, 185, 129, 0.12)',
                    'rgba(217, 70, 239, 0.10)',
                ]),
            };
        },
        () => {
            const width = accentBetween(28, 72);
            const height = accentBetween(8, 20);
            return {
                width,
                height,
                borderRadius: '999px',
                background: pick([
                    'rgba(47, 103, 177, 0.16)',
                    'rgba(245, 155, 47, 0.16)',
                    'rgba(16, 185, 129, 0.14)',
                    'rgba(56, 189, 248, 0.14)',
                ]),
            };
        },
        () => {
            const size = accentBetween(24, 58);
            const stroke = accentBetween(4, 9);
            return {
                width: size,
                height: size,
                borderRadius: '999px',
                border: `${stroke}px solid ${pick([
                    'rgba(125, 211, 252, 0.22)',
                    'rgba(251, 191, 36, 0.22)',
                    'rgba(16, 185, 129, 0.18)',
                    'rgba(217, 70, 239, 0.16)',
                ])}`,
                background: 'transparent',
            };
        },
        () => {
            const size = accentBetween(20, 46);
            return {
                width: size,
                height: size,
                background: pick([
                    'rgba(245, 155, 47, 0.12)',
                    'rgba(125, 211, 252, 0.12)',
                    'rgba(16, 185, 129, 0.10)',
                ]),
                clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)',
            };
        }
    ];

    for (let row = 0; row < rows; row += 1) {
        for (let col = 0; col < columns; col += 1) {
            const shape = shapeFactories[Math.floor(Math.random() * shapeFactories.length)]();
            const offsetX = randomBetween(cellWidth * 0.16, cellWidth * 0.84);
            const offsetY = randomBetween(cellHeight * 0.14, cellHeight * 0.86);
            const left = clamp((col * cellWidth) + offsetX - (shape.width / 2), 0, width - shape.width);
            const top = clamp((row * cellHeight) + offsetY - (shape.height / 2), 0, height - shape.height);

            shapes.push({
                left,
                top,
                rotate: randomBetween(-32, 32),
                opacity: randomBetween(0.72, 1),
                ...shape,
            });
        }
    }

    const accentShapesCount = compact ? 18 : (medium ? 28 : 42);

    for (let index = 0; index < accentShapesCount; index += 1) {
        const shape = accentFactories[Math.floor(Math.random() * accentFactories.length)]();
        const left = clamp(randomBetween(width * 0.03, width * 0.97) - (shape.width / 2), 0, width - shape.width);
        const top = clamp(randomBetween(height * 0.03, height * 0.97) - (shape.height / 2), 0, height - shape.height);

        shapes.push({
            left,
            top,
            rotate: randomBetween(-40, 40),
            opacity: randomBetween(0.5, 0.88),
            ...shape,
        });
    }

    layer.replaceChildren();

    for (const shape of shuffle(shapes)) {
        const node = document.createElement('span');
        node.style.position = 'absolute';
        node.style.left = `${shape.left}px`;
        node.style.top = `${shape.top}px`;
        node.style.width = `${shape.width}px`;
        node.style.height = `${shape.height}px`;
        node.style.transform = `rotate(${shape.rotate}deg)`;
        node.style.borderRadius = shape.borderRadius;
        node.style.opacity = shape.opacity;
        node.style.background = shape.background || 'transparent';
        node.style.border = shape.border || 'none';
        node.style.filter = shape.filter || 'none';
        node.style.clipPath = shape.clipPath || 'none';
        if (shape.transformOverride) {
            node.style.transform = shape.transformOverride;
        }
        layer.appendChild(node);
    }
}

function randomizeAppBackgroundIcons() {
    const random = (min, max) => min + Math.random() * (max - min);

    const shuffle = (items) => {
        const copy = [...items];
        for (let index = copy.length - 1; index > 0; index -= 1) {
            const swapIndex = Math.floor(Math.random() * (index + 1));
            [copy[index], copy[swapIndex]] = [copy[swapIndex], copy[index]];
        }

        return copy;
    };

    const slots = {
        left: [
            [6, 12],
            [23, 30],
            [38, 45],
            [54, 61],
            [69, 76],
            [83, 88],
        ],
        right: [
            [4, 10],
            [15, 21],
            [28, 35],
            [42, 49],
            [56, 63],
            [70, 77],
            [84, 89],
        ],
    };

    const horizontal = {
        left: {
            chat: [3.2, 6.4],
            aa: [4, 9],
            comma: [5, 11],
            dots: [8.5, 13.5],
            ring: [14, 22],
        },
        right: {
            quote: [9, 14],
            dots: [9, 14],
            ring: [12, 18],
            document: [3.6, 6.8],
            braces: [8, 13],
            'dots-low': [10, 15],
            check: [5, 8.5],
        },
    };

    const placeIcons = (side) => {
        const icons = [...document.querySelectorAll(`[data-app-bg-side="${side}"]`)]
            .map((icon) => ({
                icon,
                width: icon.offsetWidth || icon.getBoundingClientRect().width || 1,
                height: icon.offsetHeight || icon.getBoundingClientRect().height || icon.offsetWidth || 1,
            }))
            .filter((item) => item.width > 1 && item.height > 1)
            .sort((first, second) => (second.width * second.height) - (first.width * first.height));

        const viewport = {
            width: window.innerWidth || document.documentElement.clientWidth || 1,
            height: window.innerHeight || document.documentElement.clientHeight || 1,
        };
        const margin = 8;
        const occupied = [];

        const rectFromBase = (baseLeft, baseTop, width, height, scale) => {
            const scaledWidth = width * scale;
            const scaledHeight = height * scale;

            return {
                left: baseLeft - ((scaledWidth - width) / 2),
                top: baseTop - ((scaledHeight - height) / 2),
                right: baseLeft - ((scaledWidth - width) / 2) + scaledWidth,
                bottom: baseTop - ((scaledHeight - height) / 2) + scaledHeight,
                width: scaledWidth,
                height: scaledHeight,
            };
        };

        const clampCandidate = (candidate, width, height, scale) => {
            let rect = rectFromBase(candidate.left, candidate.top, width, height, scale);

            if (rect.left < margin) {
                candidate.left += margin - rect.left;
            }
            if (rect.right > viewport.width - margin) {
                candidate.left -= rect.right - (viewport.width - margin);
            }
            if (rect.top < margin) {
                candidate.top += margin - rect.top;
            }
            if (rect.bottom > viewport.height - margin) {
                candidate.top -= rect.bottom - (viewport.height - margin);
            }

            candidate.left = Math.max(margin, Math.min(candidate.left, viewport.width - width - margin));
            candidate.top = Math.max(margin, Math.min(candidate.top, viewport.height - height - margin));
            rect = rectFromBase(candidate.left, candidate.top, width, height, scale);

            return {
                ...candidate,
                rect,
            };
        };

        const overlapRatio = (first, second) => {
            const width = Math.max(0, Math.min(first.right, second.right) - Math.max(first.left, second.left));
            const height = Math.max(0, Math.min(first.bottom, second.bottom) - Math.max(first.top, second.top));
            const area = width * height;

            if (!area) {
                return 0;
            }

            const smallerArea = Math.max(1, Math.min(first.width * first.height, second.width * second.height));

            return area / smallerArea;
        };

        const buildCandidate = (item, slot) => {
            const name = item.icon.dataset.appBgIcon;
            const xRange = horizontal[side][name] || [4, 10];
            const scale = random(0.92, 1.08);
            const top = (random(slot[0], slot[1]) / 100) * viewport.height;
            const sideOffset = (random(xRange[0], xRange[1]) / 100) * viewport.width;
            const left = side === 'left'
                ? sideOffset
                : viewport.width - sideOffset - item.width;

            return clampCandidate({ left, top, scale }, item.width, item.height, scale);
        };

        icons.forEach((item, index) => {
            const sideSlots = shuffle(slots[side]);
            let best = null;

            for (let attempt = 0; attempt < 180; attempt += 1) {
                const slot = sideSlots[(index + attempt) % sideSlots.length];
                const candidate = buildCandidate(item, slot);
                const overlaps = occupied.map((rect) => overlapRatio(candidate.rect, rect));
                const maxOverlap = overlaps.length ? Math.max(...overlaps) : 0;
                const totalOverlap = overlaps.reduce((sum, value) => sum + value, 0);
                const score = (maxOverlap * 1000) + (totalOverlap * 100) + random(0, 1);

                if (!best || score < best.score) {
                    best = {
                        ...candidate,
                        maxOverlap,
                        totalOverlap,
                        score,
                    };
                }

                if (maxOverlap <= 0.08 && totalOverlap <= 0.14) {
                    break;
                }
            }

            const chosen = best || buildCandidate(item, slots[side][index % slots[side].length]);
            item.icon.style.top = `${chosen.top.toFixed(0)}px`;
            item.icon.style.setProperty('--random-scale', chosen.scale.toFixed(2));

            if (side === 'left') {
                item.icon.style.left = `${chosen.left.toFixed(0)}px`;
                item.icon.style.right = 'auto';
            } else {
                item.icon.style.right = `${(viewport.width - chosen.left - item.width).toFixed(0)}px`;
                item.icon.style.left = 'auto';
            }

            const inner = item.icon.querySelector('.app-bg-icon-inner');
            if (inner) {
                inner.style.animationDelay = `-${random(0, 6).toFixed(2)}s`;
            }

            occupied.push(chosen.rect);
        });
    };

    placeIcons('left');
    placeIcons('right');
}

document.addEventListener('DOMContentLoaded', () => {
    initStickyShellHeader();
    randomizeAppBackgroundIcons();
    window.requestAnimationFrame(buildShellRandomShapes);

    let shellShapesResizeTimeout = null;
    const scheduleShellShapes = () => {
        window.clearTimeout(shellShapesResizeTimeout);
        shellShapesResizeTimeout = window.setTimeout(buildShellRandomShapes, 180);
    };

    let appBackgroundResizeTimeout = null;
    const scheduleAppBackgroundIcons = () => {
        window.clearTimeout(appBackgroundResizeTimeout);
        appBackgroundResizeTimeout = window.setTimeout(randomizeAppBackgroundIcons, 180);
    };

    window.addEventListener('load', scheduleShellShapes, { once: true });
    window.addEventListener('resize', scheduleShellShapes);
    window.addEventListener('resize', scheduleAppBackgroundIcons);
});

// Used by the global theme controller and local visual diagnostics.
window.randomizeAppBackgroundIcons = randomizeAppBackgroundIcons;
window.buildShellRandomShapes = buildShellRandomShapes;
