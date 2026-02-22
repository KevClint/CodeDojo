/**
 * CodeDojo - Live Playground
 * Vanilla JS playground with CodeMirror + Split.js + sandboxed iframe
 */

(function () {
    const root = document.getElementById('playgroundRoot');
    if (!root) {
        return;
    }

    const runBtn = document.getElementById('pgRunBtn');
    const saveBtn = document.getElementById('pgSaveBtn');
    const resetBtn = document.getElementById('pgResetBtn');
    const layoutBtn = document.getElementById('pgLayoutBtn');
    const focusBtn = document.getElementById('pgFocusBtn');
    const previewFrame = document.getElementById('pgPreviewFrame');

    const htmlTA = document.getElementById('pgHtmlCode');
    const cssTA = document.getElementById('pgCssCode');
    const jsTA = document.getElementById('pgJsCode');

    const storageKey = 'codedojo_live_playground_v1';
    const ctx = window.PLAYGROUND_CONTEXT || {};
    const currentTaskId = Number.isInteger(ctx.taskId) ? ctx.taskId : (ctx.taskId ? parseInt(ctx.taskId, 10) : null);
    const disableRestore = !!ctx.disableRestore;
    let currentLayout = 'bottom';
    let outerSplit = null;
    let editorsSplit = null;

    const htmlEditor = CodeMirror.fromTextArea(htmlTA, {
        mode: 'htmlmixed',
        lineNumbers: true,
        theme: 'material-darker',
        tabSize: 2,
        lineWrapping: true
    });

    const cssEditor = CodeMirror.fromTextArea(cssTA, {
        mode: 'css',
        lineNumbers: true,
        theme: 'material-darker',
        tabSize: 2,
        lineWrapping: true
    });

    const jsEditor = CodeMirror.fromTextArea(jsTA, {
        mode: 'javascript',
        lineNumbers: true,
        theme: 'material-darker',
        tabSize: 2,
        lineWrapping: true
    });

    function debounce(fn, wait) {
        let timer = null;
        return function debounced(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), wait);
        };
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

    function renderPreview() {
        const html = htmlEditor.getValue();
        const css = cssEditor.getValue();
        const js = jsEditor.getValue();
        previewFrame.srcdoc = createSrcDoc(html, css, js);
        saveWorkspace();
    }

    const debouncedRender = debounce(renderPreview, 300);

    function buildCombinedHtmlDocument() {
        const html = htmlEditor.getValue();
        const css = cssEditor.getValue();
        const js = jsEditor.getValue();
        return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>${css}</style>
</head>
<body>
${html}
<script>${js.replace(/<\/script>/gi, '<\\/script>')}<\/script>
</body>
</html>`;
    }

    function setupSplit(layout) {
        if (outerSplit) {
            outerSplit.destroy();
            outerSplit = null;
        }
        if (editorsSplit) {
            editorsSplit.destroy();
            editorsSplit = null;
        }

        root.classList.toggle('layout-right', layout === 'right');
        root.classList.toggle('layout-bottom', layout === 'bottom');

        if (typeof Split === 'function') {
            if (layout === 'bottom') {
                outerSplit = Split(['#pgEditorsWrap', '#pgPreviewWrap'], {
                    direction: 'vertical',
                    sizes: [68, 32],
                    minSize: [280, 160],
                    gutterSize: 8,
                    cursor: 'row-resize'
                });
            } else {
                outerSplit = Split(['#pgEditorsWrap', '#pgPreviewWrap'], {
                    direction: 'horizontal',
                    sizes: [66, 34],
                    minSize: [420, 260],
                    gutterSize: 8,
                    cursor: 'col-resize'
                });
            }

            editorsSplit = Split(['#pgHtmlPane', '#pgCssPane', '#pgJsPane'], {
                direction: 'horizontal',
                sizes: [34, 33, 33],
                minSize: [220, 220, 220],
                gutterSize: 8,
                cursor: 'col-resize'
            });
        }

        setTimeout(() => {
            htmlEditor.refresh();
            cssEditor.refresh();
            jsEditor.refresh();
        }, 0);
    }

    function saveWorkspace() {
        if (disableRestore) {
            return;
        }
        const payload = {
            html: htmlEditor.getValue(),
            css: cssEditor.getValue(),
            js: jsEditor.getValue(),
            layout: currentLayout
        };
        localStorage.setItem(storageKey, JSON.stringify(payload));
    }

    function loadWorkspace() {
        if (disableRestore) {
            return;
        }
        const raw = localStorage.getItem(storageKey);
        if (!raw) {
            return;
        }
        try {
            const saved = JSON.parse(raw);
            if (typeof saved.html === 'string') htmlEditor.setValue(saved.html);
            if (typeof saved.css === 'string') cssEditor.setValue(saved.css);
            if (typeof saved.js === 'string') jsEditor.setValue(saved.js);
            if (saved.layout === 'right' || saved.layout === 'bottom') {
                currentLayout = saved.layout;
            }
        } catch (e) {
            console.warn('Failed to load playground workspace');
        }
    }

    function toggleLayout() {
        currentLayout = currentLayout === 'bottom' ? 'right' : 'bottom';
        setupSplit(currentLayout);
        updateLayoutButton();
        saveWorkspace();
    }

    function updateLayoutButton() {
        layoutBtn.dataset.layout = currentLayout;
        layoutBtn.innerHTML = currentLayout === 'bottom'
            ? '<span class="material-icons">view_week</span> Output Right'
            : '<span class="material-icons">vertical_split</span> Output Bottom';
    }

    function toggleFocusMode() {
        document.body.classList.toggle('playground-focus-mode');
        const inFocus = document.body.classList.contains('playground-focus-mode');
        focusBtn.innerHTML = inFocus
            ? '<span class="material-icons">fullscreen_exit</span> Exit Focus'
            : '<span class="material-icons">fullscreen</span> Focus Mode';

        setTimeout(() => {
            htmlEditor.refresh();
            cssEditor.refresh();
            jsEditor.refresh();
        }, 50);
    }

    function resetWorkspace() {
        if (!confirm('Reset HTML, CSS, and JavaScript to defaults?')) {
            return;
        }
        localStorage.removeItem(storageKey);
        window.location.reload();
    }

    async function savePractice() {
        const title = prompt('Give this playground a title:', 'Playground ' + new Date().toLocaleDateString());
        if (!title) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('html_code', buildCombinedHtmlDocument());
            formData.append('task_id', currentTaskId || '');

            const response = await fetch('api/save_practice.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                alert('Saved successfully.');
                return;
            }
            alert(result.message || 'Failed to save.');
        } catch (error) {
            console.error(error);
            alert('Save request failed.');
        }
    }

    loadWorkspace();
    setupSplit(currentLayout);
    updateLayoutButton();
    renderPreview();

    [htmlEditor, cssEditor, jsEditor].forEach((editor) => {
        editor.on('change', debouncedRender);
    });

    runBtn.addEventListener('click', renderPreview);
    if (saveBtn) {
        saveBtn.addEventListener('click', savePractice);
    }
    layoutBtn.addEventListener('click', toggleLayout);
    focusBtn.addEventListener('click', toggleFocusMode);
    resetBtn.addEventListener('click', resetWorkspace);

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            renderPreview();
        }
        if (e.key === 'Escape' && document.body.classList.contains('playground-focus-mode')) {
            toggleFocusMode();
        }
    });
})();
