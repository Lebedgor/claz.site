<x-filament-panels::page>
    <style>
        [x-cloak] { display: none !important; }
        body.fm-noscroll { overflow: hidden; }

        .fm-top { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; margin-bottom: .8rem; }
        .fm-crumbs { display: flex; align-items: center; gap: .25rem; font-size: .85rem; color: #6b7280; min-width: 0; flex-wrap: wrap; }
        .fm-crumb { color: #6b7280; cursor: pointer; background: none; border: none; padding: .1rem .2rem; font-size: .85rem; }
        .fm-crumb:hover { color: #b45309; text-decoration: underline; }
        .fm-crumb.current { color: #111827; font-weight: 600; cursor: default; }
        .fm-crumb.current:hover { text-decoration: none; }
        .fm-crumb-sep { color: #d1d5db; }

        .fm-actions { display: flex; gap: .4rem; margin-left: auto; }
        .fm-btn {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .42rem .75rem; border-radius: .5rem; font-size: .8rem; font-weight: 600;
            border: 1px solid #d1d5db; background: #fff; color: #374151; cursor: pointer; white-space: nowrap;
        }
        .fm-btn:hover { background: #f9fafb; }
        .fm-btn.primary { background: #b45309; border-color: #b45309; color: #fff; }
        .fm-btn.primary:hover { background: #92400e; }
        .fm-btn svg { width: .9rem; height: .9rem; }
        .fm-icon-btn { padding: .42rem .5rem; }

        .fm-tools { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-bottom: .9rem; }
        .fm-search {
            flex: 1 1 200px; max-width: 300px;
            border: 1px solid #d1d5db; border-radius: .5rem;
            padding: .4rem .7rem; font-size: .82rem; outline: none; background: #fff; color: #111827;
        }
        .fm-search:focus { border-color: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,.18); }
        .fm-tabs { display: flex; gap: .3rem; flex-wrap: wrap; }
        .fm-tab {
            border: 1px solid #e5e7eb; background: #fff; color: #4b5563;
            border-radius: 999px; padding: .3rem .7rem; font-size: .76rem; font-weight: 500; cursor: pointer;
        }
        .fm-tab:hover { background: #f9fafb; }
        .fm-tab.active { background: #b45309; border-color: #b45309; color: #fff; }
        .fm-stats { font-size: .72rem; color: #9ca3af; margin-left: auto; white-space: nowrap; }

        .fm-tray {
            display: flex; flex-wrap: wrap; gap: .6rem;
            margin-bottom: 1rem; padding: .7rem; border: 1px solid #e5e7eb;
            border-radius: .75rem; background: #fafafa;
        }
        .fm-chip { width: 84px; position: relative; }
        .fm-chip-thumb {
            width: 84px; height: 84px; border-radius: .6rem; overflow: hidden;
            background: #f3f4f6; display: flex; align-items: center; justify-content: center;
            border: 1px solid #e5e7eb; position: relative;
        }
        .fm-chip-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .fm-chip-thumb svg { width: 1.4rem; height: 1.4rem; color: #9ca3af; }
        .fm-chip-bar-wrap { height: .25rem; border-radius: 999px; background: #e5e7eb; margin-top: .3rem; overflow: hidden; }
        .fm-chip-bar { height: 100%; background: #f59e0b; transition: width .2s; }
        .fm-chip-bar.green { background: #10b981; }
        .fm-chip-bar.red { background: #ef4444; }
        .fm-chip.done .fm-chip-badge { background: #10b981; }
        .fm-chip.error .fm-chip-badge { background: #ef4444; }
        .fm-chip.done .fm-chip-bar { background: #10b981; }
        .fm-chip.error .fm-chip-bar { background: #ef4444; }
        .fm-chip-name { font-size: .6rem; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: .2rem; }
        .fm-chip-badge {
            position: absolute; top: .25rem; right: .25rem; width: 1.1rem; height: 1.1rem; border-radius: 999px;
            display: flex; align-items: center; justify-content: center; font-size: .6rem; color: #fff;
            background: rgba(0,0,0,.55); font-weight: 700;
        }
        .fm-chip.done .fm-chip-badge { background: #10b981; }
        .fm-chip.error .fm-chip-badge { background: #ef4444; }

        .fm-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; }
        @media (min-width: 640px)  { .fm-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 768px)  { .fm-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (min-width: 1024px) { .fm-grid { grid-template-columns: repeat(5, 1fr); } }
        @media (min-width: 1280px) { .fm-grid { grid-template-columns: repeat(6, 1fr); } }

        .fm-card {
            position: relative; overflow: hidden; border-radius: .75rem;
            border: 1px solid #e5e7eb; background: #fff; cursor: pointer;
            transition: box-shadow .15s, border-color .15s;
        }
        .fm-card:hover { box-shadow: 0 6px 16px rgba(0,0,0,.1); border-color: #f59e0b; }

        .fm-folder-thumb {
            aspect-ratio: 1; width: 100%; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
        }
        .fm-folder-thumb svg { width: 2.6rem; height: 2.6rem; color: #d97706; }

        .fm-thumb { position: relative; aspect-ratio: 1; width: 100%; overflow: hidden; background: #f3f4f6; }
        .fm-thumb img, .fm-thumb video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .fm-doc {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100%; gap: .4rem; color: #9ca3af; font-size: .62rem; text-transform: uppercase; letter-spacing: .06em;
        }
        .fm-doc svg { width: 2.2rem; height: 2.2rem; }
        .fm-doc.is-pdf { color: #dc2626; }
        .fm-play {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
            width: 2.4rem; height: 2.4rem; border-radius: 999px; background: rgba(0,0,0,.55);
            display: flex; align-items: center; justify-content: center; pointer-events: none;
        }
        .fm-play svg { width: 1.1rem; height: 1.1rem; color: #fff; }
        .fm-badge {
            position: absolute; bottom: .4rem; left: .4rem; padding: .1rem .4rem; border-radius: .3rem;
            font-size: .58rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
            background: rgba(0,0,0,.7); color: #fff; pointer-events: none;
        }
        .fm-badge.is-pdf { background: rgba(220,38,38,.92); }
        .fm-info { padding: .45rem .6rem; }
        .fm-name { font-size: .74rem; font-weight: 500; color: #374151; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .fm-meta { font-size: .64rem; color: #9ca3af; }
        .fm-card-actions {
            position: absolute; top: .4rem; right: .4rem; display: flex; gap: .25rem;
            opacity: 0; transition: opacity .15s;
        }
        .fm-card:hover .fm-card-actions { opacity: 1; }
        .fm-act {
            padding: .22rem .45rem; border-radius: .45rem; font-size: .62rem; font-weight: 600;
            cursor: pointer; border: none; backdrop-filter: blur(4px); line-height: 1.2;
        }
        .fm-act.copy { background: rgba(255,255,255,.92); color: #6b7280; }
        .fm-act.copy:hover { background: #fff; color: #111827; }
        .fm-act.del { background: rgba(239,68,68,.92); color: #fff; }
        .fm-act.del:hover { background: #dc2626; }

        .fm-state { padding: 3.5rem 1rem; text-align: center; color: #6b7280; font-size: .85rem; }
        .fm-state.boxed { border: 2px dashed #d1d5db; border-radius: .75rem; }
        .fm-spinner { width: 2rem; height: 2rem; border: 3px solid #e5e7eb; border-top-color: #f59e0b; border-radius: 50%; animation: fm-spin .6s linear infinite; margin: 0 auto 1rem; }
        @keyframes fm-spin { to { transform: rotate(360deg); } }

        .fm-viewer { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem; animation: fm-fade .15s ease; }
        .fm-viewer-backdrop { position: absolute; inset: 0; background: rgba(15,23,42,.8); backdrop-filter: blur(3px); }
        .fm-viewer-panel {
            position: relative; z-index: 1; width: min(1040px, 96vw); max-height: 92vh;
            display: flex; flex-direction: column; background: #fff; border-radius: 1rem;
            overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,.4); animation: fm-pop .15s ease;
        }
        @keyframes fm-fade { from { opacity: 0; } }
        @keyframes fm-pop { from { opacity: 0; transform: scale(.96); } }
        .fm-viewer-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .6rem 1rem; border-bottom: 1px solid #e5e7eb; }
        .fm-viewer-name { font-size: .85rem; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .fm-viewer-sub { font-size: .7rem; color: #9ca3af; }
        .fm-viewer-actions { display: flex; align-items: center; gap: .35rem; flex-shrink: 0; }
        .fm-vbtn {
            padding: .3rem .6rem; border-radius: .5rem; font-size: .72rem; font-weight: 600; cursor: pointer;
            border: 1px solid #e5e7eb; background: #fff; color: #4b5563; text-decoration: none;
            display: inline-flex; align-items: center; gap: .25rem; white-space: nowrap;
        }
        .fm-vbtn:hover { background: #f9fafb; }
        .fm-vbtn.danger { border-color: #fecaca; color: #dc2626; }
        .fm-vbtn.danger:hover { background: #fef2f2; }
        .fm-stage { position: relative; flex: 1; min-height: 0; display: flex; align-items: center; justify-content: center; background: #0f172a; overflow: hidden; }
        .fm-stage img, .fm-stage video { max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; }
        .fm-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            width: 2.5rem; height: 2.5rem; border-radius: 999px; border: none;
            background: rgba(255,255,255,.15); color: #fff; font-size: 1.35rem; line-height: 1;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
        }
        .fm-nav:hover { background: rgba(255,255,255,.32); }
        .fm-nav.prev { left: .6rem; }
        .fm-nav.next { right: .6rem; }
        .fm-viewer-bottom { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .5rem 1rem; border-top: 1px solid #e5e7eb; background: #f9fafb; }
        .fm-viewer-path { font-size: .7rem; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: ui-monospace, monospace; }
        .fm-viewer-count { font-size: .7rem; color: #9ca3af; flex-shrink: 0; }
    </style>

    <script>
        function fileManager() {
            return {
                dir: 'uploads',
                folders: [],
                files: [],
                loading: true,
                loadError: false,
                search: '',
                filter: 'all',
                viewIndex: null,
                uploadQueue: [],
                uploading: false,
                csrf: document.head.querySelector('meta[name=csrf-token]') ? document.head.querySelector('meta[name=csrf-token]').content : '',

                listUrl(dir) {
                    return '{{ route('admin.media.api.index') }}?dir=' + encodeURIComponent(dir);
                },

                initDir() {
                    const fromUrl = new URLSearchParams(window.location.search).get('dir');
                    if (fromUrl !== null && fromUrl.startsWith('uploads') && !fromUrl.includes('..')) {
                        this.dir = fromUrl;
                    } else {
                        this.dir = this.storedDir() || 'uploads';
                    }
                    this.loadDir();
                },

                storedDir() {
                    try {
                        const dir = window.localStorage.getItem('claz.media.dir');
                        return dir !== null && dir.startsWith('uploads') && !dir.includes('..') ? dir : '';
                    } catch (e) {
                        return '';
                    }
                },

                rememberDir(dir) {
                    try {
                        window.localStorage.setItem('claz.media.dir', dir);
                    } catch (e) {}
                },

                async loadDir() {
                    this.loading = true;
                    this.loadError = false;
                    try {
                        const res = await fetch(this.listUrl(this.dir), { headers: { Accept: 'application/json' } });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const data = await res.json();
                        this.folders = data.folders || [];
                        this.files = data.files || [];
                    } catch (e) {
                        this.loadError = true;
                    }
                    this.loading = false;
                },

                get crumbs() {
                    const parts = this.dir === 'uploads' ? [] : this.dir.replace(/^uploads\/?/, '').split('/').filter(Boolean);
                    const crumbs = [{ label: 'uploads', dir: 'uploads' }];
                    for (const part of parts) {
                        crumbs.push({ label: part, dir: crumbs[crumbs.length - 1].dir + '/' + part });
                    }
                    return crumbs;
                },

                navigate(dir) {
                    this.dir = dir;
                    this.viewIndex = null;
                    this.rememberDir(dir);
                    const url = new URL(window.location.href);
                    if (dir === 'uploads') {
                        url.searchParams.delete('dir');
                    } else {
                        url.searchParams.set('dir', dir);
                    }
                    window.history.replaceState({}, '', url);
                    this.loadDir();
                },

                openFolder(name) {
                    this.navigate(this.dir + '/' + name);
                },

                countFor(filter) {
                    if (filter === 'all') return this.files.length;
                    if (filter === 'image') return this.files.filter((f) => f.type === 'image').length;
                    if (filter === 'video') return this.files.filter((f) => f.type === 'video').length;
                    return this.files.filter((f) => f.type === 'pdf' || f.type === 'file').length;
                },

                get visibleFolders() {
                    const q = this.search.trim().toLowerCase();
                    return q ? this.folders.filter((f) => f.toLowerCase().includes(q)) : this.folders;
                },

                get visibleFiles() {
                    let list = this.files;
                    if (this.filter === 'image') list = list.filter((f) => f.type === 'image');
                    else if (this.filter === 'video') list = list.filter((f) => f.type === 'video');
                    else if (this.filter === 'doc') list = list.filter((f) => f.type === 'pdf' || f.type === 'file');
                    const q = this.search.trim().toLowerCase();
                    if (q) list = list.filter((f) => f.name.toLowerCase().includes(q));
                    return list;
                },

                totalSize() {
                    return this.fmt(this.files.reduce((s, f) => s + (f.bytes || 0), 0));
                },

                fmt(bytes) {
                    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
                    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
                    return (bytes / 1024).toFixed(1) + ' KB';
                },

                async newFolder() {
                    const name = prompt('New folder name:');
                    if (name === null) return;
                    const trimmed = name.trim();
                    if (trimmed === '') return;
                    try {
                        const res = await fetch('{{ route('admin.media.api.folder') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                            body: JSON.stringify({ dir: this.dir, name: trimmed }),
                        });
                        if (!res.ok) {
                            let msg = 'Failed to create folder.';
                            try { msg = (await res.json()).message || msg; } catch (e) {}
                            alert(msg);
                            return;
                        }
                        await this.loadDir();
                    } catch (e) {
                        alert('Failed to create folder.');
                    }
                },

                addFiles(fileList) {
                    const list = Array.from(fileList || []);
                    if (!list.length) return;
                    for (const file of list) {
                        const type = /\.(mp4|m4v|mov|webm|ogv|ogg|avi|mkv)$/i.test(file.name) ? 'video'
                            : /\.(jpe?g|png|gif|webp|avif)$/i.test(file.name) ? 'image' : 'file';
                        this.uploadQueue.push({
                            id: 'q' + Date.now() + '-' + Math.random().toString(36).slice(2),
                            name: file.name,
                            file: file,
                            dir: this.dir,
                            type: type,
                            progress: 0,
                            status: 'queued',
                            error: '',
                            preview: type === 'image' ? URL.createObjectURL(file) : '',
                        });
                    }
                    this.processQueue();
                },

                async processQueue() {
                    if (this.uploading) return;
                    this.uploading = true;
                    while (this.uploadQueue.some((c) => c.status === 'queued')) {
                        const chip = this.uploadQueue.find((c) => c.status === 'queued');
                        await this.uploadChip(chip);
                    }
                    this.uploading = false;
                    setTimeout(() => { this.uploadQueue = this.uploadQueue.filter((c) => c.status !== 'done'); }, 1500);
                },

                uploadChip(chip) {
                    chip.status = 'uploading';

                    return new Promise((resolve) => {
                        const xhr = new XMLHttpRequest();
                        const fd = new FormData();
                        fd.append('file', chip.file);
                        fd.append('dir', chip.dir);
                        xhr.open('POST', '{{ route('admin.media.api.upload') }}');
                        xhr.setRequestHeader('X-CSRF-TOKEN', this.csrf);
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.upload.onprogress = (e) => {
                            if (e.lengthComputable) chip.progress = Math.round((e.loaded / e.total) * 100);
                        };
                        xhr.onload = () => {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                const media = JSON.parse(xhr.responseText);
                                if (media.dir === this.dir) {
                                    this.files.unshift(media);
                                }
                                chip.status = 'done';
                            } else {
                                let msg = 'Upload failed (HTTP ' + xhr.status + ')';
                                try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
                                chip.error = msg;
                                chip.status = 'error';
                            }
                            resolve();
                        };
                        xhr.onerror = () => {
                            chip.error = 'Network error';
                            chip.status = 'error';
                            resolve();
                        };
                        xhr.send(fd);
                    });
                },

                get viewFile() {
                    if (this.viewIndex === null) return null;
                    return this.visibleFiles[this.viewIndex] || null;
                },

                openViewer(file) {
                    const i = this.visibleFiles.indexOf(file);
                    if (i !== -1) this.viewIndex = i;
                },
                closeViewer() { this.viewIndex = null; },
                next() {
                    if (this.viewIndex === null || this.visibleFiles.length < 2) return;
                    this.viewIndex = (this.viewIndex + 1) % this.visibleFiles.length;
                },
                prev() {
                    if (this.viewIndex === null || this.visibleFiles.length < 2) return;
                    this.viewIndex = (this.viewIndex - 1 + this.visibleFiles.length) % this.visibleFiles.length;
                },
                onKey(e) {
                    if (this.viewIndex === null) return;
                    const tag = (document.activeElement && document.activeElement.tagName) || '';
                    if (tag === 'INPUT' || tag === 'TEXTAREA') return;
                    if (e.key === 'Escape') this.closeViewer();
                    else if (e.key === 'ArrowRight') this.next();
                    else if (e.key === 'ArrowLeft') this.prev();
                },

                async removePath(path) {
                    if (!confirm('Delete "' + path.split('/').pop() + '"?')) return false;
                    try {
                        const res = await fetch('{{ route('admin.media.api.delete') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                            body: JSON.stringify({ path: path }),
                        });
                        if (!res.ok) {
                            let msg = 'Failed to delete.';
                            try { msg = (await res.json()).message || msg; } catch (e) {}
                            alert(msg);
                            return false;
                        }
                        this.closeViewer();
                        await this.loadDir();
                        return true;
                    } catch (e) {
                        alert('Failed to delete.');
                        return false;
                    }
                },

                async copy(text, event) {
                    const btn = event.currentTarget;
                    const label = btn ? btn.textContent : null;
                    try { await navigator.clipboard.writeText(text); } catch (e) {}
                    if (!btn) return;
                    btn.textContent = 'Copied!';
                    setTimeout(() => { btn.textContent = label; }, 1200);
                },
            };
        }
    </script>

    <div wire:ignore x-data="fileManager()" x-init="loadDir()" x-on:keydown.window="onKey($event)" x-effect="document.body.classList.toggle('fm-noscroll', viewFile !== null)">
        <div class="fm-top">
            <div class="fm-top" style="margin:0; flex:1; min-width:0;">
                <template x-for="(crumb, i) in crumbs" :key="crumb.dir">
                    <span style="display:inline-flex; align-items:center;">
                        <button type="button" class="fm-crumb" :class="{current: i === crumbs.length - 1}" @click="navigate(crumb.dir)" x-text="crumb.label"></button>
                        <template x-if="i < crumbs.length - 1"><span class="fm-crumb-sep">/</span></template>
                    </span>
                </template>
            </div>
            <div class="fm-actions">
                <button type="button" class="fm-btn" @click="newFolder()">
                    <x-heroicon-o-folder-plus /> New folder
                </button>
                <button type="button" class="fm-btn primary" @click="$refs.fileInput.click()">
                    <x-heroicon-o-arrow-up-tray /> Upload here
                </button>
                <button type="button" class="fm-btn fm-icon-btn" title="Refresh" @click="loadDir()">
                    <x-heroicon-o-arrow-path />
                </button>
            </div>
        </div>
        <input type="file" multiple x-ref="fileInput" style="display:none;" @change="addFiles($event.target.files); $event.target.value = ''">

        <div class="fm-tools">
            <input type="text" class="fm-search" x-model.debounce.200ms="search" placeholder="Search in this folder…">
            <div class="fm-tabs">
                <button type="button" class="fm-tab" :class="{active: filter === 'all'}" @click="filter = 'all'" x-text="'All ' + countFor('all')"></button>
                <button type="button" class="fm-tab" :class="{active: filter === 'image'}" @click="filter = 'image'" x-text="'Images ' + countFor('image')"></button>
                <button type="button" class="fm-tab" :class="{active: filter === 'video'}" @click="filter = 'video'" x-text="'Videos ' + countFor('video')"></button>
                <button type="button" class="fm-tab" :class="{active: filter === 'doc'}" @click="filter = 'doc'" x-text="'Docs ' + countFor('doc')"></button>
            </div>
            <span class="fm-stats" x-show="!loading" x-text="files.length + ' files · ' + folders.length + ' folders'"></span>
        </div>

        <template x-if="uploadQueue.length > 0" x-cloak>
            <div class="fm-tray">
                <template x-for="chip in uploadQueue" :key="chip.id">
                    <div class="fm-chip" :class="{done: chip.status === 'done', error: chip.status === 'error'}" :title="chip.error || chip.name">
                        <div class="fm-chip-thumb">
                            <template x-if="chip.type === 'image' && chip.preview">
                                <img :src="chip.preview" alt="">
                            </template>
                            <template x-if="chip.type === 'video'">
                                <x-heroicon-o-film />
                            </template>
                            <template x-if="chip.type === 'file'">
                                <x-heroicon-o-document />
                            </template>
                            <div class="fm-chip-badge" x-text="chip.status === 'error' ? '✕' : (chip.status === 'done' ? '✓' : (chip.status === 'uploading' ? chip.progress + '%' : '…'))"></div>
                        </div>
                        <div class="fm-chip-bar-wrap"><div class="fm-chip-bar" :class="{green: chip.status === 'done', red: chip.status === 'error'}" :style="'width:' + (chip.status === 'queued' ? 0 : (chip.status === 'done' || chip.status === 'error' ? 100 : chip.progress)) + '%'"></div></div>
                        <div class="fm-chip-name" x-text="chip.name" :title="chip.error || chip.name"></div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="loading">
            <div class="fm-state"><div class="fm-spinner"></div>Loading…</div>
        </template>
        <template x-if="!loading && loadError">
            <div class="fm-state boxed">Failed to load. <button type="button" class="fm-tab" @click="loadDir()">Retry</button></div>
        </template>
        <template x-if="!loading && !loadError && folders.length === 0 && files.length === 0">
            <div class="fm-state boxed">
                <x-heroicon-o-folder-open style="width:3rem; height:3rem; color:#d1d5db; margin:0 auto .75rem;" />
                This folder is empty.<br>
                <span style="font-size:.75rem; color:#9ca3af;">Use "Upload here" to add files, or "New folder" to organize them.</span>
            </div>
        </template>
        <template x-if="!loading && !loadError && (folders.length > 0 || files.length > 0)">
            <div class="fm-grid">
                <template x-for="folder in visibleFolders" :key="folder">
                    <div class="fm-card" @click="openFolder(folder)">
                        <div class="fm-folder-thumb"><x-heroicon-o-folder /></div>
                        <div class="fm-info">
                            <p class="fm-name" x-text="folder" :title="folder"></p>
                            <p class="fm-meta">folder</p>
                        </div>
                        <div class="fm-card-actions" @click.stop>
                            <button type="button" class="fm-act del" title="Delete empty folder" @click="removePath(dir + '/' + folder)">✕</button>
                        </div>
                    </div>
                </template>
                <template x-for="file in visibleFiles" :key="file.path">
                    <div class="fm-card" @click="openViewer(file)">
                        <div class="fm-thumb">
                            <template x-if="file.type === 'image'">
                                <img :src="file.url" :alt="file.name" loading="lazy">
                            </template>
                            <template x-if="file.type === 'video'">
                                <div style="width:100%; height:100%;">
                                    <video :src="file.url + '#t=0.1'" muted preload="metadata" playsinline style="width:100%; height:100%; object-fit:cover;"></video>
                                    <div class="fm-play"><x-heroicon-o-play /></div>
                                </div>
                            </template>
                            <template x-if="file.type === 'pdf' || file.type === 'file'">
                                <div class="fm-doc" :class="{ 'is-pdf': file.type === 'pdf' }">
                                    <x-heroicon-o-document style="width:2.2rem; height:2.2rem;" />
                                    <span x-text="file.type"></span>
                                </div>
                            </template>
                            <template x-if="file.type === 'video'">
                                <span class="fm-badge">Video</span>
                            </template>
                            <template x-if="file.type === 'pdf'">
                                <span class="fm-badge is-pdf">PDF</span>
                            </template>
                        </div>
                        <div class="fm-info">
                            <p class="fm-name" x-text="file.name" :title="file.name"></p>
                            <p class="fm-meta" x-text="file.size + (file.modified ? ' · ' + file.modified : '')"></p>
                        </div>
                        <div class="fm-card-actions" @click.stop>
                            <button type="button" class="fm-act copy" title="Copy URL" @click="copy(file.rel, $event)">URL</button>
                            <button type="button" class="fm-act copy" title="Copy path" @click="copy(file.path, $event)">Path</button>
                            <button type="button" class="fm-act del" title="Delete" @click="removePath(file.path)">✕</button>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="viewFile" x-cloak>
            <div class="fm-viewer">
                <div class="fm-viewer-backdrop" @click="closeViewer()"></div>
                <div class="fm-viewer-panel" @click.stop>
                    <div class="fm-viewer-top">
                        <div style="min-width:0;">
                            <div class="fm-viewer-name" x-text="viewFile.name"></div>
                            <div class="fm-viewer-sub" x-text="viewFile.size + (viewFile.modified ? ' · ' + viewFile.modified : '')"></div>
                        </div>
                        <div class="fm-viewer-actions">
                            <button type="button" class="fm-vbtn" @click="copy(viewFile.rel, $event)">Copy URL</button>
                            <button type="button" class="fm-vbtn" @click="copy(viewFile.path, $event)">Copy path</button>
                            <a class="fm-vbtn" :href="viewFile.url" target="_blank" rel="noopener">Open ↗</a>
                            <button type="button" class="fm-vbtn danger" @click="removePath(viewFile.path)">Delete</button>
                            <button type="button" class="fm-vbtn" @click="closeViewer()">✕</button>
                        </div>
                    </div>
                    <div class="fm-stage">
                        <template x-if="viewFile.type === 'image'">
                            <img :src="viewFile.url" :alt="viewFile.name">
                        </template>
                        <template x-if="viewFile.type === 'video'">
                            <video :src="viewFile.url" controls playsinline></video>
                        </template>
                        <template x-if="viewFile.type !== 'image' && viewFile.type !== 'video'">
                            <div style="text-align:center; color:#9ca3af;">
                                <x-heroicon-o-document style="width:4rem; height:4rem; margin:0 auto 1rem; color:#475569;" />
                                <div><a class="fm-vbtn" :href="viewFile.url" target="_blank" rel="noopener" style="background:#fff;">Open file ↗</a></div>
                            </div>
                        </template>
                        <template x-if="visibleFiles.length > 1">
                            <div style="position:absolute; inset:0; pointer-events:none;">
                                <button type="button" class="fm-nav prev" @click="prev()" style="pointer-events:auto;">‹</button>
                                <button type="button" class="fm-nav next" @click="next()" style="pointer-events:auto;">›</button>
                            </div>
                        </template>
                    </div>
                    <div class="fm-viewer-bottom">
                        <span class="fm-viewer-path" x-text="viewFile.path"></span>
                        <span class="fm-viewer-count" x-text="(viewIndex + 1) + ' / ' + visibleFiles.length"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-filament-panels::page>