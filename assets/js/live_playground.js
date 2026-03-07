/**
 * CodeDojo - Live Playground
 * Reliable textarea-based editor with live preview and project saves.
 */

(function () {
    const root = document.getElementById('playgroundRoot');
    if (!root) return;

    const runBtn = document.getElementById('pgRunBtn');
    const saveBtn = document.getElementById('pgSaveBtn');
    const resetBtn = document.getElementById('pgResetBtn');
    const layoutBtn = document.getElementById('pgLayoutBtn');
    const focusBtn = document.getElementById('pgFocusBtn');
    const previewFrame = document.getElementById('pgPreviewFrame');
    const editorsWrap = document.getElementById('pgEditorsWrap');
    const previewWrap = document.getElementById('pgPreviewWrap');
    const mainSplitter = document.getElementById('pgMainSplitter');
    const titleInput = document.getElementById('pgTitleInput');
    const saveStatus = document.getElementById('pgSaveStatus');

    const htmlInput = document.getElementById('pgHtmlCode');
    const cssInput = document.getElementById('pgCssCode');
    const jsInput = document.getElementById('pgJsCode');
    const editorCards = [
        document.getElementById('pgHtmlPane'),
        document.getElementById('pgCssPane'),
        document.getElementById('pgJsPane')
    ];
    const editorSplitters = Array.from(root.querySelectorAll('.pg-splitter-editor'));

    if (!htmlInput || !cssInput || !jsInput || !previewFrame || !editorsWrap || !previewWrap || !mainSplitter || editorCards.some((card) => !card)) {
        return;
    }

    const storageKey = 'codedojo_live_playground_v3';
    const uiStorageKey = 'codedojo_live_playground_ui_v1';
    const ctx = window.PLAYGROUND_CONTEXT || {};
    const currentTaskId = Number.isInteger(ctx.taskId) ? ctx.taskId : (ctx.taskId ? parseInt(ctx.taskId, 10) : null);
    const disableRestore = !!ctx.disableRestore;
    const minMainPanePx = 220;
    const minEditorPanePx = 120;
    const editorPaneCount = editorCards.length;

    let currentPracticeId = Number.isInteger(ctx.practiceId) ? ctx.practiceId : (ctx.practiceId ? parseInt(ctx.practiceId, 10) : null);
    let currentLayout = 'bottom';
    let saveState = 'new';
    let splitState = {
        main: 58,
        editors: [33.34, 33.33, 33.33]
    };

    [htmlInput, cssInput, jsInput].forEach((el) => {
        el.spellcheck = false;
        el.removeAttribute('readonly');
        el.removeAttribute('disabled');
    });

    function debounce(fn, wait) {
        let timer = null;
        return function debounced(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function roundPct(value) {
        return Math.round(value * 100) / 100;
    }

    function isCompactMode() {
        return window.matchMedia('(max-width: 980px)').matches;
    }

    function normalizeEditorSplit(values) {
        const source = Array.isArray(values) ? values.slice(0, editorPaneCount) : [];

        while (source.length < editorPaneCount) {
            source.push(100 / editorPaneCount);
        }

        let sum = source.reduce((acc, value) => acc + (Number.isFinite(value) ? value : 0), 0);
        if (sum <= 0) {
            return [33.34, 33.33, 33.33];
        }

        const normalized = source.map((value) => roundPct(((Number.isFinite(value) ? value : 0) / sum) * 100));
        const normalizedSum = normalized.reduce((acc, value) => acc + value, 0);
        normalized[normalized.length - 1] = roundPct(normalized[normalized.length - 1] + (100 - normalizedSum));

        return normalized;
    }

    function saveUiState() {
        const payload = {
            layout: currentLayout,
            split: {
                main: splitState.main,
                editors: splitState.editors
            }
        };
        localStorage.setItem(uiStorageKey, JSON.stringify(payload));
    }

    function loadUiState() {
        const raw = localStorage.getItem(uiStorageKey);
        if (!raw) return;

        try {
            const saved = JSON.parse(raw);
            if (saved.layout === 'right' || saved.layout === 'bottom') {
                currentLayout = saved.layout;
            }
            if (saved.split && typeof saved.split === 'object') {
                if (Number.isFinite(saved.split.main)) {
                    splitState.main = clamp(saved.split.main, 30, 75);
                }
                if (Array.isArray(saved.split.editors)) {
                    splitState.editors = normalizeEditorSplit(saved.split.editors);
                }
            }
        } catch (_e) {
            console.warn('Failed to load playground UI state');
        }
    }

    function createSrcDoc(html, css, js) {
        const safeJs = js.replace(/<\/script>/gi, '<\\/script>');
        return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>${css}</style>
</head>
<body>
${html}
<script>
try {
${safeJs}
} catch (error) {
  document.body.insertAdjacentHTML('beforeend', '<pre style="color:#b91c1c;background:#fee2e2;padding:12px;border-radius:8px;">JS Error: ' + error.message + '</pre>');
}
</script>
</body>
</html>`;
    }

    function buildSaveDocument(html, css, js) {
        const payload = btoa(unescape(encodeURIComponent(JSON.stringify({ html, css, js }))));
        return `<!-- CODEDOJO_PEN_V1:${payload} -->`;
    }

    function updateSaveStatus(state, overrideText) {
        if (!saveStatus) return;
        saveState = state;
        saveStatus.setAttribute('data-state', state);

        if (overrideText) {
            saveStatus.textContent = overrideText;
            return;
        }
        if (state === 'saved') {
            saveStatus.textContent = 'Saved';
            return;
        }
        if (state === 'saving') {
            saveStatus.textContent = 'Saving...';
            return;
        }
        saveStatus.textContent = 'Unsaved changes';
    }

    function markDirty() {
        if (saveState !== 'saving') {
            updateSaveStatus('dirty');
        }
    }

    function getEditorState() {
        return {
            html: htmlInput.value,
            css: cssInput.value,
            js: jsInput.value
        };
    }

    function renderPreview() {
        const { html, css, js } = getEditorState();
        previewFrame.srcdoc = createSrcDoc(html, css, js);
        saveWorkspace();
    }

    const debouncedRender = debounce(renderPreview, 250);

    function applySplitState() {
        if (isCompactMode()) {
            editorsWrap.style.flex = '';
            previewWrap.style.flex = '';
            editorCards.forEach((card) => {
                card.style.flex = '';
            });
            return;
        }

        splitState.main = clamp(splitState.main, 30, 75);
        splitState.editors = normalizeEditorSplit(splitState.editors);

        editorsWrap.style.flex = `0 0 ${roundPct(splitState.main)}%`;
        previewWrap.style.flex = '1 1 0';

        editorCards.forEach((card, index) => {
            card.style.flex = `0 0 ${splitState.editors[index]}%`;
        });
    }

    function updateSplitterOrientation() {
        const mainOrientation = currentLayout === 'right' ? 'vertical' : 'horizontal';
        mainSplitter.setAttribute('aria-orientation', mainOrientation);
        mainSplitter.classList.toggle('is-vertical', mainOrientation === 'vertical');
        mainSplitter.classList.toggle('is-horizontal', mainOrientation === 'horizontal');

        const editorOrientation = currentLayout === 'right' ? 'horizontal' : 'vertical';
        editorSplitters.forEach((splitter) => {
            splitter.setAttribute('aria-orientation', editorOrientation);
            splitter.classList.toggle('is-vertical', editorOrientation === 'vertical');
            splitter.classList.toggle('is-horizontal', editorOrientation === 'horizontal');
        });
    }

    function withDragSession(event, onMove, onEnd) {
        event.preventDefault();
        root.classList.add('is-resizing');
        document.body.classList.add('pg-dragging');

        const handleMove = (moveEvent) => {
            onMove(moveEvent);
        };

        const handleUp = () => {
            window.removeEventListener('pointermove', handleMove);
            window.removeEventListener('pointerup', handleUp);
            root.classList.remove('is-resizing');
            document.body.classList.remove('pg-dragging');
            if (typeof onEnd === 'function') {
                onEnd();
            }
        };

        window.addEventListener('pointermove', handleMove);
        window.addEventListener('pointerup', handleUp, { once: true });
    }

    function adjustMainSplitByStep(stepPct) {
        if (isCompactMode()) return;
        splitState.main = clamp(splitState.main + stepPct, 30, 75);
        applySplitState();
        saveUiState();
    }

    function startMainResize(event) {
        if (isCompactMode()) return;

        const useX = currentLayout === 'right';
        const rootRect = root.getBoundingClientRect();
        const startCoord = useX ? event.clientX : event.clientY;
        const startMainPx = useX
            ? editorsWrap.getBoundingClientRect().width
            : editorsWrap.getBoundingClientRect().height;
        const totalPx = useX ? rootRect.width : rootRect.height;

        withDragSession(event, (moveEvent) => {
            const coord = useX ? moveEvent.clientX : moveEvent.clientY;
            const delta = coord - startCoord;
            const maxMainPx = Math.max(minMainPanePx + 1, totalPx - minMainPanePx);
            const nextMainPx = clamp(startMainPx + delta, minMainPanePx, maxMainPx);
            splitState.main = (nextMainPx / totalPx) * 100;
            applySplitState();
        }, saveUiState);
    }

    function adjustEditorSplit(index, stepPct) {
        if (isCompactMode()) return;
        if (index < 0 || index >= editorPaneCount - 1) return;

        const totalPairPct = splitState.editors[index] + splitState.editors[index + 1];
        const totalDimension = currentLayout === 'right' ? editorsWrap.clientHeight : editorsWrap.clientWidth;
        const minPct = (minEditorPanePx / Math.max(1, totalDimension)) * 100;
        if ((minPct * 2) >= totalPairPct) return;
        const nextA = clamp(splitState.editors[index] + stepPct, minPct, totalPairPct - minPct);

        splitState.editors[index] = nextA;
        splitState.editors[index + 1] = totalPairPct - nextA;
        splitState.editors = normalizeEditorSplit(splitState.editors);
        applySplitState();
        saveUiState();
    }

    function startEditorResize(index, event) {
        if (isCompactMode()) return;
        if (index < 0 || index >= editorPaneCount - 1) return;

        const useX = currentLayout !== 'right';
        const paneA = editorCards[index];
        const paneB = editorCards[index + 1];
        const rectA = paneA.getBoundingClientRect();
        const rectB = paneB.getBoundingClientRect();

        const startCoord = useX ? event.clientX : event.clientY;
        const startA = useX ? rectA.width : rectA.height;
        const startB = useX ? rectB.width : rectB.height;
        const pairPx = startA + startB;
        const pairPct = splitState.editors[index] + splitState.editors[index + 1];
        const maxA = pairPx - minEditorPanePx;
        if (maxA <= minEditorPanePx) return;

        withDragSession(event, (moveEvent) => {
            const coord = useX ? moveEvent.clientX : moveEvent.clientY;
            const delta = coord - startCoord;
            const nextA = clamp(startA + delta, minEditorPanePx, maxA);
            const nextAPct = (nextA / pairPx) * pairPct;
            splitState.editors[index] = nextAPct;
            splitState.editors[index + 1] = pairPct - nextAPct;
            splitState.editors = normalizeEditorSplit(splitState.editors);
            applySplitState();
        }, saveUiState);
    }

    function setLayout(layout) {
        currentLayout = layout === 'right' ? 'right' : 'bottom';
        root.classList.toggle('layout-right', currentLayout === 'right');
        root.classList.toggle('layout-bottom', currentLayout === 'bottom');
        updateSplitterOrientation();
        applySplitState();
        updateLayoutButton();
        saveUiState();
    }

    function toggleLayout() {
        setLayout(currentLayout === 'bottom' ? 'right' : 'bottom');
        saveWorkspace();
    }

    function updateLayoutButton() {
        if (!layoutBtn) return;
        layoutBtn.dataset.layout = currentLayout;
        layoutBtn.setAttribute('aria-pressed', currentLayout === 'right' ? 'true' : 'false');
        layoutBtn.innerHTML = currentLayout === 'bottom'
            ? '<span class="material-icons">view_week</span> Output Right'
            : '<span class="material-icons">vertical_split</span> Output Bottom';
    }

    function toggleFocusMode() {
        document.body.classList.toggle('playground-focus-mode');
        const inFocus = document.body.classList.contains('playground-focus-mode');
        focusBtn.setAttribute('aria-pressed', inFocus ? 'true' : 'false');
        focusBtn.innerHTML = inFocus
            ? '<span class="material-icons">fullscreen_exit</span> Exit Focus'
            : '<span class="material-icons">fullscreen</span> Focus Mode';
    }

    function resetWorkspace() {
        if (!confirm('Reset HTML, CSS, and JavaScript to defaults?')) {
            return;
        }
        localStorage.removeItem(storageKey);
        window.location.reload();
    }

    function saveWorkspace() {
        if (disableRestore) return;

        const { html, css, js } = getEditorState();
        const payload = {
            title: titleInput ? titleInput.value.trim() : '',
            html,
            css,
            js,
            layout: currentLayout
        };
        localStorage.setItem(storageKey, JSON.stringify(payload));
    }

    function loadWorkspace() {
        if (disableRestore) return;

        const raw = localStorage.getItem(storageKey);
        if (!raw) return;

        try {
            const saved = JSON.parse(raw);
            if (titleInput && typeof saved.title === 'string' && !titleInput.value.trim()) {
                titleInput.value = saved.title;
            }
            if (typeof saved.html === 'string') htmlInput.value = saved.html;
            if (typeof saved.css === 'string') cssInput.value = saved.css;
            if (typeof saved.js === 'string') jsInput.value = saved.js;
            if (saved.layout === 'right' || saved.layout === 'bottom') {
                currentLayout = saved.layout;
            }
        } catch (_e) {
            console.warn('Failed to load playground workspace');
        }
    }

    async function savePractice() {
        const titleValue = titleInput ? titleInput.value.trim() : '';
        const title = titleValue || 'Untitled Project';
        updateSaveStatus('saving');

        try {
            const { html, css, js } = getEditorState();
            const formData = new FormData();
            formData.append('title', title);
            formData.append('html_code', buildSaveDocument(html, css, js));
            formData.append('source_html', html);
            formData.append('source_css', css);
            formData.append('source_js', js);
            formData.append('task_id', currentTaskId || '');

            if (currentPracticeId) {
                formData.append('practice_id', String(currentPracticeId));
            }

            const response = await fetch('api/save_practice.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                if (titleInput && !titleInput.value.trim()) {
                    titleInput.value = title;
                }
                if (result.id) {
                    currentPracticeId = parseInt(result.id, 10);
                }
                updateSaveStatus('saved');
                return;
            }

            updateSaveStatus('dirty', result.message || 'Failed to save');
        } catch (error) {
            console.error(error);
            updateSaveStatus('dirty', 'Save failed');
        }
    }

    loadWorkspace();
    loadUiState();
    updateSaveStatus('new', saveStatus ? saveStatus.textContent : 'Not saved');
    setLayout(currentLayout);
    renderPreview();

    mainSplitter.addEventListener('pointerdown', startMainResize);
    mainSplitter.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            e.preventDefault();
            adjustMainSplitByStep(-2);
        }
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            e.preventDefault();
            adjustMainSplitByStep(2);
        }
    });

    editorSplitters.forEach((splitter, index) => {
        splitter.addEventListener('pointerdown', (event) => startEditorResize(index, event));
        splitter.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                adjustEditorSplit(index, -2);
            }
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                adjustEditorSplit(index, 2);
            }
        });
    });

    window.addEventListener('resize', debounce(() => {
        updateSplitterOrientation();
        applySplitState();
    }, 100));

    [htmlInput, cssInput, jsInput].forEach((el) => {
        el.addEventListener('input', () => {
            markDirty();
            debouncedRender();
        });
    });

    if (titleInput) {
        titleInput.addEventListener('input', () => {
            markDirty();
            saveWorkspace();
        });
    }

    if (runBtn) runBtn.addEventListener('click', renderPreview);
    if (saveBtn) saveBtn.addEventListener('click', savePractice);
    if (layoutBtn) layoutBtn.addEventListener('click', toggleLayout);
    if (focusBtn) focusBtn.addEventListener('click', toggleFocusMode);
    if (resetBtn) resetBtn.addEventListener('click', resetWorkspace);

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            renderPreview();
        }

        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            savePractice();
        }

        if (e.key === 'Escape' && document.body.classList.contains('playground-focus-mode')) {
            toggleFocusMode();
        }
    });
})();
