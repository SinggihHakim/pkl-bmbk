<!-- Modal Upload & Manajemen Foto Lapangan per STA -->
<div x-data="{ openUploadFoto: false, fotoTab: 'upload', lightboxUrl: null, lightboxTitle: '' }"
     @open-foto-modal.window="openUploadFoto = true"
     @open-lightbox.window="lightboxUrl = $event.detail.url; lightboxTitle = $event.detail.title"
     @keydown.escape.window="lightboxUrl = null; openUploadFoto = false">

    <!-- Modal Dialog Upload Foto -->
    <div x-show="openUploadFoto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="openUploadFoto = false"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden border border-gray-100" @click.stop>
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-gray-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/30 flex items-center justify-center text-indigo-400 border border-indigo-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Foto Kondisi Real Lapangan</h3>
                            <p class="text-xs text-gray-400">Ruas: <?= e($ruas['nama_ruas']) ?> (<?= e($ruas['kode_ruas']) ?>)</p>
                        </div>
                    </div>
                    <button @click="openUploadFoto = false" class="text-gray-400 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Tabs Header -->
                <div class="flex border-b border-gray-200 bg-gray-50 px-6 pt-3 gap-4 text-sm font-medium">
                    <button @click="fotoTab = 'upload'"
                            :class="fotoTab === 'upload' ? 'text-indigo-600 border-indigo-600 bg-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-2.5 border-b-2 font-semibold rounded-t-lg transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload Foto / ZIP
                    </button>
                    <button @click="fotoTab = 'list'"
                            :class="fotoTab === 'list' ? 'text-indigo-600 border-indigo-600 bg-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-2.5 border-b-2 font-semibold rounded-t-lg transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        Daftar Foto (<?= count($fotoLapangans ?? []) ?>)
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <!-- TAB 1: Upload Form -->
                    <div x-show="fotoTab === 'upload'" class="space-y-6">
                        <!-- Info Format Penamaan File -->
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 space-y-1">
                            <div class="font-bold flex items-center gap-1.5 text-blue-800">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Aturan Penamaan File Foto:
                            </div>
                            <p>Penamaan file harus mengandung STA titik ruas jalan, contoh: <code class="font-mono bg-blue-100 px-1 py-0.5 rounded text-blue-900 font-bold">0+100.jpg</code>, <code class="font-mono bg-blue-100 px-1 py-0.5 rounded text-blue-900 font-bold">0+200.png</code>, atau <code class="font-mono bg-blue-100 px-1 py-0.5 rounded text-blue-900 font-bold">1+250.jpg</code>.</p>
                            <p class="text-[11px] text-blue-700">Sistem akan otomatis mengekstrak posisi STA dan menampilkan titik foto di lokasi line peta & visual strip map.</p>
                        </div>

                        <form action="<?= base_url('foto-lapangan/upload/' . $ruas['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <!-- Drag & Drop Multi-file -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Unggah Multiple Foto (.jpg, .png, .webp):</label>
                                <div class="border-2 border-dashed border-gray-300 hover:border-indigo-500 bg-gray-50/50 rounded-xl p-6 text-center cursor-pointer transition-colors relative">
                                    <input type="file" name="foto_files[]" multiple accept=".jpg,.jpeg,.png,.webp" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                                    <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-gray-700">Pilih atau Seret Foto ke Sini</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Bisa pilih banyak file sekaligus (contoh: 0+100.jpg, 0+200.jpg)</p>
                                </div>
                            </div>

                            <!-- Upload ZIP -->
                            <div class="pt-2 border-t border-gray-200">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Atau Unggah via File Arsip ZIP (.zip):</label>
                                <input type="file" name="zip_file" accept=".zip" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                                <p class="text-[11px] text-gray-400 mt-1">Gunakan file ZIP jika Anda memiliki puluhan/ratusan foto dalam 1 folder.</p>
                            </div>

                            <div class="pt-4 flex justify-end gap-2">
                                <button type="button" @click="openUploadFoto = false" class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Simpan & Proses Foto
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: Daftar Foto -->
                    <div x-show="fotoTab === 'list'">
                        <?php if (empty($fotoLapangans)): ?>
                            <div class="py-12 text-center text-gray-400 space-y-2">
                                <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                                <p class="text-xs font-semibold text-gray-600">Belum ada foto lapangan tersimpan</p>
                                <p class="text-[11px] text-gray-400">Silakan gunakan tab "Upload Foto / ZIP" untuk mengunggah dokumentasi kondisi jalan.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-96 overflow-y-auto pr-1">
                                <?php foreach ($fotoLapangans as $f): ?>
                                    <div class="group relative bg-gray-50 border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all">
                                        <img src="<?= e($f['url']) ?>" alt="STA <?= e($f['sta_titik']) ?>" class="w-full h-28 object-cover cursor-pointer hover:scale-105 transition-transform duration-200"
                                             @click="lightboxUrl = '<?= e($f['url']) ?>'; lightboxTitle = 'Foto STA <?= e($f['sta_titik']) ?>'">
                                        
                                        <div class="p-2 flex items-center justify-between bg-white border-t border-gray-100">
                                            <div>
                                                <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[11px] font-bold rounded">STA <?= e($f['sta_titik']) ?></span>
                                                <p class="text-[10px] text-gray-400 font-mono mt-0.5 truncate max-w-[100px]"><?= e($f['file_name']) ?></p>
                                            </div>

                                            <!-- Form Delete Foto -->
                                            <form action="<?= base_url('foto-lapangan/delete/' . $f['id']) ?>" method="POST" onsubmit="return confirm('Hapus foto STA <?= e($f['sta_titik']) ?>?')">
                                                <button type="submit" class="text-gray-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition-colors" title="Hapus Foto">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox Fullscreen Modal -->
    <!-- x-init: saat lightboxUrl berubah, inject/remove style untuk menekan z-index Leaflet -->
    <div x-show="lightboxUrl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-effect="
            let styleEl = document.getElementById('leaflet-zindex-suppress');
            if (lightboxUrl) {
                if (!styleEl) {
                    styleEl = document.createElement('style');
                    styleEl.id = 'leaflet-zindex-suppress';
                    styleEl.textContent = '.leaflet-pane, .leaflet-top, .leaflet-bottom, .leaflet-control { z-index: 1 !important; }';
                    document.head.appendChild(styleEl);
                }
            } else {
                if (styleEl) styleEl.remove();
            }
         "
         class="fixed inset-0 flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
         style="z-index: 99999; display: none;" @click="lightboxUrl = null">
        
        <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center justify-center" @click.stop>
            <button @click="lightboxUrl = null" class="absolute -top-10 right-0 text-white/80 hover:text-white p-2 text-sm font-semibold flex items-center gap-1">
                <span>Tutup (ESC)</span>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img :src="lightboxUrl" class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl border border-white/10" alt="Full Preview">
            <p x-text="lightboxTitle" class="mt-3 text-sm font-bold text-white tracking-wide bg-gray-900/80 px-4 py-1.5 rounded-full border border-white/20"></p>
        </div>
    </div>
</div>

<script>
window.openLightbox = function(url, title) {
    window.dispatchEvent(new CustomEvent('open-lightbox', { detail: { url: url, title: title } }));
};
</script>
