@php
    $statePath = $getStatePath();
    $state = $getState();
@endphp

<style>
    .mp-wrap { width: 100%; }
    .mp-row { display: flex; align-items: center; gap: .5rem; }
    .mp-input {
        flex: 1; border-radius: .5rem; border: 1px solid #d1d5db; background: #fff;
        padding: .4rem .7rem; font-size: .85rem; color: #111827; outline: none;
    }
    .mp-input:focus { border-color: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,.18); }
    .mp-thumb {
        width: 2.6rem; height: 2.6rem; border-radius: .5rem; overflow: hidden;
        border: 1px solid #e5e7eb; flex-shrink: 0; background: #f3f4f6;
        display: flex; align-items: center; justify-content: center;
    }
    .mp-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .mp-browse {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .4rem .7rem; border-radius: .5rem; font-size: .82rem; font-weight: 500; cursor: pointer;
        border: 1px solid #d1d5db; background: #fff; color: #374151; white-space: nowrap;
    }
    .mp-browse:hover { background: #f9fafb; }
    .mp-overlay { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem; animation: mp-fade .15s ease; }
    @keyframes mp-fade { from { opacity: 0; } }
    .mp-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.8); backdrop-filter: blur(3px); }
    .mp-modal {
        position: relative; z-index: 1; width: 100%; max-width: 52rem; max-height: 88vh;
        display: flex; flex-direction: column; overflow: hidden; border-radius: 1rem; background: #fff;
        box-shadow: 0 25px 60px rgba(0,0,0,.4);
    }
    .mp-header { display: flex; align-items: center; justify-content: space-between; padding: .7rem 1.2rem; border-bottom: 1px solid #e5e7eb; }
    .mp-header h3 { font-size: .95rem; font-weight: 600; color: #111827; }
    .mp-header-link { font-size: .72rem; color: #b45309; text-decoration: none; font-weight: 500; }
    .mp-header-link:hover { text-decoration: underline; }
    .mp-close { padding: .25rem; border-radius: .5rem; border: none; background: none; color: #9ca3af; cursor: pointer; }
    .mp-close:hover { background: #f3f4f6; color: #374151; }

    .mp-navrow { display: flex; align-items: center; gap: .5rem; padding: .6rem 1.2rem; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap; }
    .mp-crumbs { display: flex; align-items: center; gap: .2rem; font-size: .8rem; min-width: 0; flex-wrap: wrap; flex: 1; }
    .mp-crumb { background: none; border: none; padding: .1rem .2rem; font-size: .8rem; color: #6b7280; cursor: pointer; }
    .mp-crumb:hover { color: #b45309; text-decoration: underline; }
    .mp-crumb.current { color: #111827; font-weight: 600; cursor: default; }
    .mp-crumb.current:hover { text-decoration: none; }
    .mp-crumb-sep { color: #d1d5db; }
    .mp-mini-btn {
        display: inline-flex; align-items: center; gap: .25rem;
        padding: .32rem .6rem; border-radius: .45rem; font-size: .72rem; font-weight: 600;
        border: 1px solid #d1d5db; background: #fff; color: #374151; cursor: pointer; white-space: nowrap;
    }
    .mp-mini-btn:hover { background: #f9fafb; }
    .mp-mini-btn.primary { background: #b45309; border-color: #b45309; color: #fff; }
    .mp-mini-btn.primary:hover { background: #92400e; }
    .mp-mini-btn svg { width: .8rem; height: .8rem; }

    .mp-search { padding: .6rem 1.2rem 0; }
    .mp-search input {
        width: 100%; border-radius: .5rem; border: 1px solid #d1d5db; background: #fff;
        padding: .4rem .7rem; font-size: .85rem; outline: none;
    }
    .mp-search input:focus { border-color: #f59e0b; }

    .mp-drop {
        margin: .6rem 1.2rem 0; padding: .7rem; border: 2px dashed #d1d5db; border-radius: .7rem;
        display: flex; align-items: center; justify-content: center; gap: .4rem;
        color: #6b7280; font-size: .75rem; cursor: pointer; text-align: center; background: #fafafa;
        transition: border-color .15s, background .15s;
    }
    .mp-drop:hover, .mp-drop.over { border-color: #f59e0b; background: #fffbeb; color: #92400e; }
    .mp-drop svg { width: 1.1rem; height: 1.1rem; }

    .mp-tray { display: flex; flex-wrap: wrap; gap: .5rem; margin: .6rem 1.2rem 0; padding: .55rem; border: 1px solid #e5e7eb; border-radius: .7rem; background: #fafafa; }
    .mp-chip { width: 72px; }
    .mp-chip-thumb {
        width: 72px; height: 72px; border-radius: .55rem; overflow: hidden;
        background: #f3f4f6; display: flex; align-items: center; justify-content: center;
        border: 1px solid #e5e7eb; position: relative;
    }
    .mp-chip-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .mp-chip-thumb svg { width: 1.2rem; height: 1.2rem; color: #9ca3af; }
    .mp-chip-badge {
        position: absolute; top: .2rem; right: .2rem; min-width: 1rem; height: 1rem; border-radius: 999px;
        display: flex; align-items: center; justify-content: center; font-size: .55rem; color: #fff;
        background: rgba(0,0,0,.55); font-weight: 700; padding: 0 .15rem;
    }
    .mp-chip.done .mp-chip-badge { background: #10b981; }
    .mp-chip.error .mp-chip-badge { background: #ef4444; }
    .mp-chip-bar-wrap { height: .22rem; border-radius: 999px; background: #e5e7eb; margin-top: .25rem; overflow: hidden; }
    .mp-chip-bar { height: 100%; background: #f59e0b; transition: width .2s; }
    .mp-chip-bar.green { background: #10b981; }
    .mp-chip-bar.red { background: #ef4444; }
    .mp-chip-name { font-size: .58rem; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: .2rem; }
    .mp-error { margin: .4rem 1.2rem 0; padding: .35rem .55rem; border-radius: .5rem; background: #fef2f2; color: #b91c1c; font-size: .7rem; }

    .mp-body { flex: 1; overflow-y: auto; padding: 1rem 1.2rem 1.2rem; }
    .mp-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem; }
    @media (min-width: 640px) { .mp-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (min-width: 768px) { .mp-grid { grid-template-columns: repeat(5, 1fr); } }
    .mp-item { cursor: pointer; overflow: hidden; border-radius: .65rem; border: 2px solid transparent; transition: all .15s; background: #fff; }
    .mp-item:hover { border-color: #f59e0b; box-shadow: 0 4px 12px rgba(0,0,0,.09); }
    .mp-item.active { border-color: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,.25); }
    .mp-item-thumb { aspect-ratio: 1; width: 100%; overflow: hidden; background: #f3f4f6; position: relative; }
    .mp-item-thumb img, .mp-item-thumb video { width: 100%; height: 100%; object-fit: cover; display: block; }
    .mp-folder-thumb {
        aspect-ratio: 1; width: 100%; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
    }
    .mp-folder-thumb svg { width: 2rem; height: 2rem; color: #d97706; }
    .mp-play {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
        width: 1.9rem; height: 1.9rem; border-radius: 999px; background: rgba(0,0,0,.55);
        display: flex; align-items: center; justify-content: center; pointer-events: none;
    }
    .mp-play svg { width: .9rem; height: .9rem; color: #fff; }
    .mp-badge {
        position: absolute; bottom: .3rem; left: .3rem; padding: .05rem .3rem; border-radius: .25rem;
        font-size: .55rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
        background: rgba(0,0,0,.7); color: #fff; pointer-events: none;
    }
    .mp-doc {
        display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;
        color: #9ca3af; font-size: .55rem; text-transform: uppercase; letter-spacing: .06em; gap: .3rem;
    }
    .mp-doc.is-pdf { color: #dc2626; }
    .mp-doc svg { width: 1.8rem; height: 1.8rem; }
    .mp-item-info { padding: .35rem .5rem; }
    .mp-item-name { font-size: .68rem; font-weight: 500; color: #374151; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mp-item-size { font-size: .58rem; color: #9ca3af; }
    .mp-loading { display: flex; align-items: center; justify-content: center; padding: 3rem; }
    .mp-spinner { width: 2rem; height: 2rem; border: 3px solid #e5e7eb; border-top-color: #f59e0b; border-radius: 50%; animation: mp-spin .6s linear infinite; }
    @keyframes mp-spin { to { transform: rotate(360deg); } }
    .mp-empty { text-align: center; padding: 3rem; color: #9ca3af; font-size: .85rem; }
</style>

<script>
    function mediaPicker(config) {
        return {
            open: false,
            dir: 'uploads',
            folders: [],
            files: [],
            loading: false,
            search: '',
            dragOver: false,
            uploading: false,
            queue: [],
            uploadError: '',
            csrf: config.csrf,
            listUrl: config.listUrl,
            uploadUrl: config.uploadUrl,
            selected: config.selected,

            async openPicker() {
                this.open = true;
                await this.loadDir();
            },

            async loadDir() {
                this.loading = true;
                try {
                    const res = await fetch(this.listUrl + '?dir=' + encodeURIComponent(this.dir), { headers: { Accept: 'application/json' } });
                    const data = await res.json();
                    this.folders = data.folders || [];
                    this.files = data.files || [];
                } catch (e) {
                    console.error('Failed to load media files', e);
                }
                this.loading = false;
            },

            navigate(dir) {
                this.dir = dir;
                this.loadDir();
            },

            openFolder(name) {
                this.navigate(this.dir + '/' + name);
            },

            get crumbs() {
                const parts = this.dir === 'uploads' ? [] : this.dir.replace(/^uploads\/?/, '').split('/').filter(Boolean);
                const crumbs = [{ label: 'uploads', dir: 'uploads' }];
                for (const part of parts) {
                    crumbs.push({ label: part, dir: crumbs[crumbs.length - 1].dir + '/' + part });
                }
                return crumbs;
            },

            selectFile(file) {
                this.selected = file.path;
                this.open = false;
            },

            isVideo(name) {
                return /\.(mp4|m4v|mov|webm|ogv|ogg|avi|mkv)$/i.test(name);
            },

            isImage(name) {
                return /\.(jpe?g|png|gif|webp|avif|svg)$/i.test(name);
            },

            get visibleFolders() {
                const q = this.search.trim().toLowerCase();
                return q ? this.folders.filter((f) => f.toLowerCase().includes(q)) : this.folders;
            },

            get filteredFiles() {
                let list = this.files;
                const q = this.search.trim().toLowerCase();
                if (q) list = list.filter((f) => f.name.toLowerCase().includes(q));
                return list;
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
                    this.queue.push({
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
                this.uploadError = '';
                this.processQueue();
            },

            async processQueue() {
                if (this.uploading) return;
                this.uploading = true;
                while (this.queue.some((c) => c.status === 'queued')) {
                    const chip = this.queue.find((c) => c.status === 'queued');
                    await this.uploadChip(chip);
                }
                this.uploading = false;
                setTimeout(() => { this.queue = this.queue.filter((c) => c.status !== 'done'); }, 1500);
            },

            uploadChip(chip) {
                chip.status = 'uploading';

                return new Promise((resolve) => {
                    const xhr = new XMLHttpRequest();
                    const fd = new FormData();
                    fd.append('file', chip.file);
                    fd.append('dir', chip.dir);
                    xhr.open('POST', this.uploadUrl);
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
                            this.uploadError = chip.name + ': ' + msg;
                            chip.status = 'error';
                        }
                        resolve();
                    };
                    xhr.onerror = () => {
                        chip.error = 'Network error';
                        this.uploadError = chip.name + ': network error';
                        chip.status = 'error';
                        resolve();
                    };
                    xhr.send(fd);
                });
            },
        };
    }
</script>

<div
    x-data="mediaPicker({
        csrf: document.head.querySelector('meta[name=csrf-token]') ? document.head.querySelector('meta[name=csrf-token]').content : '',
        listUrl: '{{ route('admin.media.api.index') }}',
        uploadUrl: '{{ route('admin.media.api.upload') }}',
        selected: @entangle($statePath),
    })"
    class="mp-wrap"
>
    <div class="mp-row">
        <input
            type="text"
            wire:model="{{ $statePath }}"
            @if ($state) value="{{ $state }}" @endif
            placeholder="No file selected"
            readonly
            class="mp-input"
        />
        @if ($state)
            <div class="mp-thumb">
                @if (in_array(strtolower(pathinfo($state, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'svg']))
                    <img src="{{ str_starts_with($state, '/storage') || str_starts_with($state, 'http') ? $state : '/storage/'.ltrim($state, '/') }}" alt="">
                @elseif (in_array(strtolower(pathinfo($state, PATHINFO_EXTENSION)), ['mp4', 'm4v', 'mov', 'webm', 'ogv', 'ogg', 'avi', 'mkv']))
                    <x-heroicon-o-film style="width:1rem; height:1rem; color:#9ca3af;" />
                @else
                    <div style="font-size:.55rem; color:#9ca3af;">FILE</div>
                @endif
            </div>
        @endif
        <button type="button" x-on:click="openPicker()" class="mp-browse">
            <x-heroicon-o-photo style="width:1rem; height:1rem;" />
            Browse
        </button>
    </div>

    <template x-if="open" x-cloak>
        <div class="mp-overlay" x-on:keydown.escape.window="open = false">
            <div class="mp-backdrop" x-on:click="open = false"></div>
            <div class="mp-modal" x-on:click.stop>
                <div class="mp-header">
                    <h3>Media library</h3>
                    <div style="display:flex; align-items:center; gap:.8rem;">
                        <a href="{{ \App\Filament\Pages\MediaLibrary::getUrl() }}" target="_blank" class="mp-header-link">Full library ↗</a>
                        <button type="button" x-on:click="open = false" class="mp-close">
                            <x-heroicon-o-x-mark style="width:1.25rem; height:1.25rem;" />
                        </button>
                    </div>
                </div>

                <div class="mp-navrow">
                    <div class="mp-crumbs">
                        <template x-for="(crumb, i) in crumbs" :key="crumb.dir">
                            <span style="display:inline-flex; align-items:center;">
                                <button type="button" class="mp-crumb" :class="{current: i === crumbs.length - 1}" @click="navigate(crumb.dir)" x-text="crumb.label"></button>
                                <template x-if="i < crumbs.length - 1"><span class="mp-crumb-sep">/</span></template>
                            </span>
                        </template>
                    </div>
                    <button type="button" class="mp-mini-btn" @click="newFolder()">
                        <x-heroicon-o-folder-plus /> New folder
                    </button>
                    <button type="button" class="mp-mini-btn primary" @click="$refs.fileInput.click()">
                        <x-heroicon-o-arrow-up-tray /> Upload here
                    </button>
                    <button type="button" class="mp-mini-btn" title="Refresh" @click="loadDir()">
                        <x-heroicon-o-arrow-path />
                    </button>
                </div>

                <div class="mp-search">
                    <input type="text" x-model.debounce.200ms="search" placeholder="Search in this folder…" />
                </div>

                <div class="mp-body">
                    <div
                        class="mp-drop"
                        :class="{ over: dragOver }"
                        @click="$refs.fileInput.click()"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="dragOver = false; addFiles($event.dataTransfer.files)"
                    >
                        <x-heroicon-o-arrow-up-tray />
                        <span>Click to select or drop files here — they upload into the current folder</span>
                        <input type="file" multiple x-ref="fileInput" style="display:none;" @change="addFiles($event.target.files); $event.target.value = ''">
                    </div>

                    <template x-if="queue.length > 0" x-cloak>
                        <div class="mp-tray">
                            <template x-for="chip in queue" :key="chip.id">
                                <div class="mp-chip" :class="{done: chip.status === 'done', error: chip.status === 'error'}" :title="chip.error || chip.name">
                                    <div class="mp-chip-thumb">
                                        <template x-if="chip.type === 'image' && chip.preview">
                                            <img :src="chip.preview" alt="">
                                        </template>
                                        <template x-if="chip.type === 'video'">
                                            <x-heroicon-o-film />
                                        </template>
                                        <template x-if="chip.type === 'file'">
                                            <x-heroicon-o-document />
                                        </template>
                                        <div class="mp-chip-badge" x-text="chip.status === 'error' ? '✕' : (chip.status === 'done' ? '✓' : (chip.status === 'uploading' ? chip.progress + '%' : '…'))"></div>
                                    </div>
                                    <div class="mp-chip-bar-wrap"><div class="mp-chip-bar" :style="'width:' + (chip.status === 'queued' ? 0 : (chip.status === 'done' || chip.status === 'error' ? 100 : chip.progress)) + '%'"></div></div>
                                    <div class="mp-item-name mp-chip-name" x-text="chip.name"></div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="uploadError">
                        <div class="mp-error" x-text="uploadError"></div>
                    </template>

                    <template x-if="loading">
                        <div class="mp-loading"><div class="mp-spinner"></div></div>
                    </template>
                    <template x-if="!loading && visibleFolders.length === 0 && filteredFiles.length === 0">
                        <div class="mp-empty">Nothing here yet. Drop files into the upload zone above.</div>
                    </template>
                    <template x-if="!loading && (visibleFolders.length > 0 || filteredFiles.length > 0)">
                        <div class="mp-grid">
                            <template x-for="folder in visibleFolders" :key="folder">
                                <div class="mp-item" x-on:click="openFolder(folder)">
                                    <div class="mp-folder-thumb">
                                        <x-heroicon-o-folder />
                                    </div>
                                    <div class="mp-item-info">
                                        <p class="mp-item-name" x-text="folder"></p>
                                        <p class="mp-item-size">folder</p>
                                    </div>
                                </div>
                            </template>
                            <template x-for="file in filteredFiles" :key="file.path">
                                <div class="mp-item" x-on:click="selectFile(file)" :class="{ active: selected === file.path }">
                                    <div class="mp-item-thumb">
                                        <template x-if="isImage(file.name)">
                                            <img :src="file.url" :alt="file.name" loading="lazy" />
                                        </template>
                                        <template x-if="isVideo(file.name)">
                                            <div style="width:100%; height:100%;">
                                                <video :src="file.url + '#t=0.1'" muted preload="metadata" playsinline></video>
                                                <div class="mp-play"><x-heroicon-o-play /></div>
                                            </div>
                                        </template>
                                        <template x-if="!isVideo(file.name) && !isImage(file.name)">
                                            <div class="mp-doc" :class="{ 'is-pdf': /\.pdf$/i.test(file.name) }">
                                                <x-heroicon-o-document style="width:1.8rem; height:1.8rem;" />
                                                <span x-text="/\.pdf$/i.test(file.name) ? 'PDF' : 'FILE'"></span>
                                            </div>
                                        </template>
                                        <template x-if="isVideo(file.name)">
                                            <span class="mp-badge">Video</span>
                                        </template>
                                    </div>
                                    <div class="mp-item-info">
                                        <p class="mp-item-name" x-text="file.name"></p>
                                        <p class="mp-item-size" x-text="file.size"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>