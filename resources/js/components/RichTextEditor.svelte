<script lang="ts">
    import Bold from '@lucide/svelte/icons/bold';
    import Italic from '@lucide/svelte/icons/italic';
    import Underline from '@lucide/svelte/icons/underline';
    import Strikethrough from '@lucide/svelte/icons/strikethrough';
    import Heading1 from '@lucide/svelte/icons/heading-1';
    import Heading2 from '@lucide/svelte/icons/heading-2';
    import Heading3 from '@lucide/svelte/icons/heading-3';
    import List from '@lucide/svelte/icons/list';
    import ListOrdered from '@lucide/svelte/icons/list-ordered';
    import Quote from '@lucide/svelte/icons/quote';
    import Code from '@lucide/svelte/icons/code';
    import LinkIcon from '@lucide/svelte/icons/link';
    import Unlink from '@lucide/svelte/icons/unlink';
    import AlignLeft from '@lucide/svelte/icons/align-left';
    import AlignCenter from '@lucide/svelte/icons/align-center';
    import AlignRight from '@lucide/svelte/icons/align-right';
    import AlignJustify from '@lucide/svelte/icons/align-justify';
    import Undo from '@lucide/svelte/icons/undo';
    import Redo from '@lucide/svelte/icons/redo';
    import Eraser from '@lucide/svelte/icons/eraser';
    import CodeXml from '@lucide/svelte/icons/code-xml';
    import Minus from '@lucide/svelte/icons/minus';

    interface Props {
        value?: string;
        placeholder?: string;
        minHeight?: string;
        label?: string;
        id?: string;
    }

    let {
        value = $bindable(''),
        placeholder = 'Tuliskan materi pembelajaran di sini...',
        minHeight = '240px',
        label = '',
        id = 'rich-editor',
    }: Props = $props();

    let editorEl: HTMLDivElement | null = $state(null);
    let isHtmlMode = $state(false);

    // Active formatting states for toolbar button highlights
    let isBold = $state(false);
    let isItalic = $state(false);
    let isUnderline = $state(false);
    let isStrike = $state(false);
    let isUl = $state(false);
    let isOl = $state(false);
    let alignState = $state<'left' | 'center' | 'right' | 'justify'>('left');

    function normalizeInitialContent(raw: string): string {
        if (!raw) return '';
        // If it already has HTML tags, return as-is
        if (/<[a-z][\s\S]*>/i.test(raw)) {
            return raw;
        }
        // Convert legacy markdown headings and plain newlines
        const lines = raw.split('\n');
        return lines
            .map((line) => {
                const trimmed = line.trim();
                if (trimmed.startsWith('### ')) return `<h3>${trimmed.slice(4)}</h3>`;
                if (trimmed.startsWith('## ')) return `<h2>${trimmed.slice(3)}</h2>`;
                if (trimmed.startsWith('# ')) return `<h1>${trimmed.slice(2)}</h1>`;
                if (trimmed === '') return '<p><br></p>';
                return `<p>${line}</p>`;
            })
            .join('');
    }

    // Keep visual editor in sync with external value changes
    $effect(() => {
        const normalized = normalizeInitialContent(value || '');
        if (editorEl && !isHtmlMode) {
            if (editorEl.innerHTML !== normalized) {
                editorEl.innerHTML = normalized;
            }
        }
    });

    function updateValue() {
        if (editorEl && !isHtmlMode) {
            value = editorEl.innerHTML;
            checkToolbarState();
        }
    }

    function checkToolbarState() {
        if (isHtmlMode || typeof document === 'undefined') return;
        try {
            isBold = document.queryCommandState('bold');
            isItalic = document.queryCommandState('italic');
            isUnderline = document.queryCommandState('underline');
            isStrike = document.queryCommandState('strikeThrough');
            isUl = document.queryCommandState('insertUnorderedList');
            isOl = document.queryCommandState('insertOrderedList');

            if (document.queryCommandState('justifyCenter')) alignState = 'center';
            else if (document.queryCommandState('justifyRight')) alignState = 'right';
            else if (document.queryCommandState('justifyFull')) alignState = 'justify';
            else alignState = 'left';
        } catch {
            // ignore
        }
    }

    function exec(cmd: string, val: string | undefined = undefined) {
        if (isHtmlMode) return;
        editorEl?.focus();
        document.execCommand(cmd, false, val);
        updateValue();
    }

    function formatHeading(tag: 'h1' | 'h2' | 'h3' | 'p') {
        if (isHtmlMode) return;
        editorEl?.focus();
        try {
            document.execCommand('formatBlock', false, `<${tag}>`);
        } catch {
            document.execCommand('formatBlock', false, tag);
        }
        updateValue();
    }

    function insertQuote() {
        if (isHtmlMode) return;
        editorEl?.focus();
        try {
            document.execCommand('formatBlock', false, '<blockquote>');
        } catch {
            document.execCommand('formatBlock', false, 'blockquote');
        }
        updateValue();
    }

    function insertCode() {
        if (isHtmlMode) return;
        editorEl?.focus();
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            const text = range.toString();
            if (text) {
                const codeNode = document.createElement('code');
                codeNode.className = 'bg-muted px-1.5 py-0.5 rounded text-xs font-mono text-indigo-600 dark:text-indigo-400';
                codeNode.textContent = text;
                range.deleteContents();
                range.insertNode(codeNode);
                updateValue();
                return;
            }
        }
        document.execCommand('insertHTML', false, '<code class="bg-muted px-1.5 py-0.5 rounded text-xs font-mono text-indigo-600 dark:text-indigo-400">kode</code>');
        updateValue();
    }

    function insertLink() {
        if (isHtmlMode) return;
        editorEl?.focus();
        const url = prompt('Masukkan tautan URL (contoh: https://example.com):');
        if (url) {
            exec('createLink', url);
        }
    }

    function removeLink() {
        if (isHtmlMode) return;
        exec('unlink');
    }

    function toggleHtmlMode() {
        if (!isHtmlMode) {
            if (editorEl) {
                value = editorEl.innerHTML;
            }
            isHtmlMode = true;
        } else {
            isHtmlMode = false;
        }
    }

    function getWordCount(html: string): { words: number; chars: number } {
        if (!html) return { words: 0, chars: 0 };
        const text = html.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
        const chars = text.length;
        const words = text ? text.split(/\s+/).length : 0;
        return { words, chars };
    }

    let stats = $derived(getWordCount(value || ''));
</script>

<div class="space-y-1.5">
    {#if label}
        <label for={id} class="block font-semibold text-foreground text-xs">
            {label}
        </label>
    {/if}

    <div class="rounded-xl border border-border bg-card overflow-hidden shadow-xs focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-1 p-2 bg-muted/40 border-b border-border/80 text-xs">
            <!-- Headings group -->
            <div class="flex items-center bg-card border border-border/60 rounded-lg p-0.5 shadow-2xs">
                <button
                    type="button"
                    title="Normal Paragraph"
                    onclick={() => formatHeading('p')}
                    class="px-2 py-1 rounded text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    P
                </button>
                <button
                    type="button"
                    title="Judul Besar (Heading 1)"
                    onclick={() => formatHeading('h1')}
                    class="p-1 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Heading1 class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Judul Sedang (Heading 2)"
                    onclick={() => formatHeading('h2')}
                    class="p-1 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Heading2 class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Sub-Judul (Heading 3)"
                    onclick={() => formatHeading('h3')}
                    class="p-1 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Heading3 class="size-3.5" />
                </button>
            </div>

            <div class="h-4 w-px bg-border/60 mx-0.5"></div>

            <!-- Formatting group -->
            <div class="flex items-center bg-card border border-border/60 rounded-lg p-0.5 shadow-2xs">
                <button
                    type="button"
                    title="Tebal (Bold)"
                    onclick={() => exec('bold')}
                    class="p-1.5 rounded transition-colors {isBold ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-bold' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <Bold class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Miring (Italic)"
                    onclick={() => exec('italic')}
                    class="p-1.5 rounded transition-colors {isItalic ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <Italic class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Garis Bawah (Underline)"
                    onclick={() => exec('underline')}
                    class="p-1.5 rounded transition-colors {isUnderline ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <Underline class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Coret (Strikethrough)"
                    onclick={() => exec('strikeThrough')}
                    class="p-1.5 rounded transition-colors {isStrike ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <Strikethrough class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Kode Inline"
                    onclick={insertCode}
                    class="p-1.5 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Code class="size-3.5" />
                </button>
            </div>

            <div class="h-4 w-px bg-border/60 mx-0.5"></div>

            <!-- Alignment group -->
            <div class="flex items-center bg-card border border-border/60 rounded-lg p-0.5 shadow-2xs">
                <button
                    type="button"
                    title="Rata Kiri"
                    onclick={() => exec('justifyLeft')}
                    class="p-1.5 rounded transition-colors {alignState === 'left' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <AlignLeft class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Rata Tengah"
                    onclick={() => exec('justifyCenter')}
                    class="p-1.5 rounded transition-colors {alignState === 'center' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <AlignCenter class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Rata Kanan"
                    onclick={() => exec('justifyRight')}
                    class="p-1.5 rounded transition-colors {alignState === 'right' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <AlignRight class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Rata Kanan-Kiri (Justify)"
                    onclick={() => exec('justifyFull')}
                    class="p-1.5 rounded transition-colors {alignState === 'justify' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <AlignJustify class="size-3.5" />
                </button>
            </div>

            <div class="h-4 w-px bg-border/60 mx-0.5"></div>

            <!-- Lists & Blocks group -->
            <div class="flex items-center bg-card border border-border/60 rounded-lg p-0.5 shadow-2xs">
                <button
                    type="button"
                    title="Daftar Poin (Bullet List)"
                    onclick={() => exec('insertUnorderedList')}
                    class="p-1.5 rounded transition-colors {isUl ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <List class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Daftar Angka (Numbered List)"
                    onclick={() => exec('insertOrderedList')}
                    class="p-1.5 rounded transition-colors {isOl ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <ListOrdered class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Kutipan (Blockquote)"
                    onclick={insertQuote}
                    class="p-1.5 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Quote class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Garis Pemisah (Horizontal Rule)"
                    onclick={() => exec('insertHorizontalRule')}
                    class="p-1.5 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Minus class="size-3.5" />
                </button>
            </div>

            <div class="h-4 w-px bg-border/60 mx-0.5"></div>

            <!-- Links group -->
            <div class="flex items-center bg-card border border-border/60 rounded-lg p-0.5 shadow-2xs">
                <button
                    type="button"
                    title="Tambah Tautan Link"
                    onclick={insertLink}
                    class="p-1.5 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <LinkIcon class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Hapus Tautan Link"
                    onclick={removeLink}
                    class="p-1.5 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Unlink class="size-3.5" />
                </button>
            </div>

            <div class="h-4 w-px bg-border/60 mx-0.5"></div>

            <!-- History & Cleanup -->
            <div class="flex items-center bg-card border border-border/60 rounded-lg p-0.5 shadow-2xs">
                <button
                    type="button"
                    title="Undo (Ctrl+Z)"
                    onclick={() => exec('undo')}
                    class="p-1.5 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Undo class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Redo (Ctrl+Y)"
                    onclick={() => exec('redo')}
                    class="p-1.5 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Redo class="size-3.5" />
                </button>
                <button
                    type="button"
                    title="Bersihkan Format"
                    onclick={() => exec('removeFormat')}
                    class="p-1.5 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                >
                    <Eraser class="size-3.5" />
                </button>
            </div>

            <!-- Source toggle button -->
            <div class="ml-auto">
                <button
                    type="button"
                    title={isHtmlMode ? 'Kembali ke Editor Visual' : 'Beralih ke Mode Kode HTML'}
                    onclick={toggleHtmlMode}
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all {isHtmlMode ? 'bg-indigo-600 text-white shadow-xs' : 'bg-card border border-border/60 text-muted-foreground hover:text-foreground hover:bg-muted'}"
                >
                    <CodeXml class="size-3.5" />
                    <span>{isHtmlMode ? 'Visual Editor' : 'HTML Source'}</span>
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="relative bg-card">
            {#if isHtmlMode}
                <textarea
                    bind:value
                    placeholder="Tuliskan kode HTML langsung di sini..."
                    class="w-full p-4 font-mono text-xs text-foreground bg-muted/10 border-0 focus:outline-hidden resize-y leading-relaxed"
                    style="min-height: {minHeight};"
                ></textarea>
            {:else}
                <div
                    bind:this={editorEl}
                    id={id}
                    contenteditable="true"
                    oninput={updateValue}
                    onkeyup={checkToolbarState}
                    onmouseup={checkToolbarState}
                    role="textbox"
                    tabindex="0"
                    aria-multiline="true"
                    data-placeholder={placeholder}
                    class="editor-content prose prose-slate dark:prose-invert max-w-none p-4 text-xs md:text-sm text-foreground focus:outline-hidden overflow-y-auto leading-relaxed focus:ring-0 [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:my-3 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:my-2 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:my-2 [&_p]:my-2 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-2 [&_blockquote]:border-l-4 [&_blockquote]:border-indigo-500 [&_blockquote]:pl-3 [&_blockquote]:italic [&_blockquote]:my-2 [&_blockquote]:text-muted-foreground [&_hr]:my-4 [&_hr]:border-border [&_a]:text-indigo-600 [&_a]:underline"
                    style="min-height: {minHeight};"
                ></div>
            {/if}
        </div>

        <!-- Footer / Status Bar -->
        <div class="flex items-center justify-between px-3 py-1.5 bg-muted/30 border-t border-border/60 text-[11px] text-muted-foreground">
            <div class="flex items-center gap-3">
                <span>{stats.words} kata</span>
                <span>•</span>
                <span>{stats.chars} karakter</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1">
                    <span class="size-1.5 rounded-full {isHtmlMode ? 'bg-amber-500' : 'bg-emerald-500'}"></span>
                    <span>{isHtmlMode ? 'Mode HTML' : 'Mode Visual'}</span>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
    .editor-content:empty:before {
        content: attr(data-placeholder);
        color: var(--color-muted-foreground, #94a3b8);
        pointer-events: none;
        display: block;
    }
</style>
