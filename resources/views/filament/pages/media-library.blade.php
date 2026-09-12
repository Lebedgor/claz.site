<x-filament-panels::page>
    <form wire:submit="upload" class="fi-form-component">
        {{ $this->form }}
        <div class="mt-3">
            <button type="submit" class="fi-btn fi-btn-color-primary">
                Upload
            </button>
        </div>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white fi-section-content-shadow">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                    <th class="px-4 py-3">Preview</th>
                    <th class="px-4 py-3">Path</th>
                    <th class="px-4 py-3">Size</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getFiles() as $file)
                    <tr class="border-b border-gray-100">
                        <td class="px-4 py-2">
                            <img src="{{ $file['url'] }}" alt="" class="h-14 w-24 rounded-lg object-cover">
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2">
                                <code class="text-xs">{{ $file['path'] }}</code>
                                <button type="button" title="Copy URL"
                                        class="rounded-lg border border-gray-200 px-2 py-1 text-xs text-gray-500 hover:bg-gray-50"
                                        x-data
                                        @click="navigator.clipboard.write('{{ $file['url'] }}'); $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy URL', 1200)">
                                    Copy URL
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $file['size'] }}</td>
                        <td class="px-4 py-2 text-right">
                            <button type="button"
                                    wire:click="deleteFile('{{ $file['path'] }}')"
                                    wire:confirm="Delete this file? Articles that embed it will lose the image."
                                    class="rounded-lg border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No files uploaded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
