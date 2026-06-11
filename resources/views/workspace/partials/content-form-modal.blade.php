<div class="overlay {{ $errors->any() ? 'open' : '' }}" id="content-modal" data-modal>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="content-modal-title">
        <form method="POST" action="{{ route('contents.store', $brand) }}" enctype="multipart/form-data" id="content-form">
            @csrf
            <input type="hidden" name="_method" value="POST" data-method-input>
            <div class="modal-head">
                <div>
                    <h2 class="modal-title" id="content-modal-title">Content Planner</h2>
                    <p class="modal-caption">Konten disimpan ke workspace {{ $brand->name }}.</p>
                    <p class="modal-draft-note">Draft tetap tersimpan saat form ditutup selama halaman belum dimuat ulang.</p>
                </div>
                <button class="btn icon-only" type="button" data-close-modal aria-label="Tutup"><span class="icon"><svg><use href="#i-close"/></svg></span></button>
            </div>
            <div class="modal-body">
                @php
                    $postingTime = old('posting_time', '18:30');
                    [$postingHour, $postingMinute] = str_contains($postingTime, ':')
                        ? explode(':', $postingTime, 2)
                        : ['', ''];
                @endphp
                <div class="field-row">
                    <div class="field"><label for="posting-date">Tanggal</label><input id="posting-date" name="posting_date" type="date" value="{{ old('posting_date', $selectedMonth->format('Y-m-d')) }}" required></div>
                    <div class="field">
                        <label for="posting-hour">Jam posting <span class="field-label-hint">(24 jam)</span></label>
                        <div class="time-picker" data-time-picker>
                            <select id="posting-hour" data-time-hour aria-label="Jam posting">
                                <option value="">Jam</option>
                                @foreach (range(0, 23) as $hour)
                                    @php($hourValue = sprintf('%02d', $hour))
                                    <option value="{{ $hourValue }}" @selected($postingHour === $hourValue)>{{ $hourValue }}</option>
                                @endforeach
                            </select>
                            <span class="time-separator" aria-hidden="true">:</span>
                            <select data-time-minute aria-label="Menit posting">
                                <option value="">Menit</option>
                                @foreach (range(0, 59) as $minute)
                                    @php($minuteValue = sprintf('%02d', $minute))
                                    <option value="{{ $minuteValue }}" @selected(substr($postingMinute, 0, 2) === $minuteValue)>{{ $minuteValue }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input id="posting-time" name="posting_time" type="hidden" value="{{ $postingTime }}" data-time-value>
                        <p class="field-help">Contoh: pilih 15 : 00 untuk pukul 3 sore.</p>
                    </div>
                </div>
                <div class="field">
                    <label for="content-type">Tipe konten</label>
                    <select id="content-type" name="type" data-content-type-select required>
                        @foreach ($contentTypes as $contentType)
                            <option value="{{ $contentType->slug }}" @selected(old('type') === $contentType->slug)>{{ $contentType->name }}</option>
                        @endforeach
                        <option value="__new" @selected(old('type') === '__new')>+ Tambah tipe baru</option>
                    </select>
                    <div class="new-content-type" data-new-content-type @if (old('type') !== '__new') hidden @endif>
                        <label for="new-content-type">Nama tipe baru</label>
                        <input id="new-content-type" name="new_type" type="text" maxlength="60" value="{{ old('new_type') }}" placeholder="Contoh: Story, Live, UGC Video">
                        <small>Tipe ini akan disimpan dan tersedia kembali untuk brand {{ $brand->name }}.</small>
                    </div>
                </div>
                <div class="field">
                    <label>Platform</label>
                    <div class="check-grid">
                        <label class="check-pill"><input name="platforms[instagram]" type="hidden" value="0"><input name="platforms[instagram]" type="checkbox" value="1" checked>Instagram</label>
                        <label class="check-pill"><input name="platforms[tiktok]" type="hidden" value="0"><input name="platforms[tiktok]" type="checkbox" value="1">TikTok</label>
                    </div>
                </div>
                <div class="field"><label for="headline">Headline</label><input id="headline" name="headline" type="text" maxlength="255" value="{{ old('headline') }}" required></div>
                @foreach ([['detail_html','Detail / script','Tulis angle, script, atau caption draft'], ['note_html','Catatan','Talent, props, lokasi, status, dll']] as [$name, $label, $placeholder])
                    <div class="field">
                        <label>{{ $label }}</label>
                        <div class="rich-editor">
                            <div class="editor-toolbar">
                                <button type="button" class="editor-tool" data-editor-command="bold">B</button>
                                <button type="button" class="editor-tool" data-editor-command="italic"><em>I</em></button>
                                <button type="button" class="editor-tool" data-editor-command="underline"><u>U</u></button>
                                <button type="button" class="editor-tool" data-editor-command="insertUnorderedList">•</button>
                                <button type="button" class="editor-tool" data-editor-command="insertOrderedList">1.</button>
                                <button type="button" class="editor-tool" data-editor-command="createLink">Link</button>
                            </div>
                            <div class="editor-area {{ $name === 'note_html' ? 'note-editor' : '' }}" contenteditable="true" data-editor-for="{{ $name }}" data-placeholder="{{ $placeholder }}">{{ old($name) }}</div>
                            <input type="hidden" name="{{ $name }}" value="{{ old($name) }}">
                        </div>
                    </div>
                @endforeach
                <div class="field media-field">
                    <div class="media-field-heading">
                        <div>
                            <label for="content-images">Media konten</label>
                            <p class="field-help">Maksimal 12 gambar. JPG, PNG, atau WebP, masing-masing maksimal 5 MB.</p>
                        </div>
                        <span class="media-count" data-media-count>0 / 12</span>
                    </div>
                    <label class="media-picker" for="content-images">
                        <span class="icon"><svg><use href="#i-plus"/></svg></span>
                        <span><strong>Pilih gambar baru</strong><small>Bisa memilih beberapa file sekaligus atau menambahnya bertahap.</small></span>
                    </label>
                    <input class="hidden-input" id="content-images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple data-multiple-preview>
                    <p class="media-edit-help" data-media-edit-help hidden>
                        Gambar baru akan <strong>ditambahkan</strong>. Gambar tersimpan hanya dihapus jika Anda memilih tombol Hapus.
                    </p>
                    <section class="media-section" data-existing-media-section hidden>
                        <div class="media-section-title"><strong>Media tersimpan</strong><span>Pilih Hapus untuk mengeluarkannya saat disimpan.</span></div>
                        <div class="existing-images" data-existing-images></div>
                    </section>
                    <section class="media-section" data-new-media-section hidden>
                        <div class="media-section-title"><strong>Media baru</strong><span>File berikut akan diunggah setelah Anda menekan Simpan.</span></div>
                        <div class="image-preview-grid" data-image-preview-list></div>
                    </section>
                </div>
                <div class="field"><label for="document-link">Link dokumen</label><input id="document-link" name="document_link" type="url" maxlength="2048" value="{{ old('document_link') }}" placeholder="https://..."></div>
            </div>
            <div class="modal-foot">
                <button class="btn" type="button" data-close-modal>Tutup</button>
                <button class="btn primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
