<div class="overlay {{ $errors->any() ? 'open' : '' }}" id="brand-modal" data-modal>
    <div class="modal modal-small" role="dialog" aria-modal="true" aria-labelledby="brand-modal-title">
        <form method="POST" action="{{ route('brands.store') }}" enctype="multipart/form-data" id="brand-form">
            @csrf
            <input type="hidden" name="_method" value="POST" data-method-input>
            <div class="modal-head">
                <div>
                    <h2 class="modal-title" id="brand-modal-title">Tambah brand</h2>
                    <p class="modal-caption">Nama dan logo untuk workspace konten.</p>
                </div>
                <button class="btn icon-only" type="button" data-close-modal aria-label="Tutup">
                    <span class="icon"><svg><use href="#i-close"/></svg></span>
                </button>
            </div>
            <div class="modal-body">
                <label class="upload" for="brand-logo">
                    <span class="upload-empty"><span class="icon"><svg><use href="#i-camera"/></svg></span>Logo</span>
                    <img data-upload-preview alt="">
                </label>
                <input class="hidden-input" id="brand-logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp" data-preview-input>
                <div class="field">
                    <label for="brand-name">Nama brand</label>
                    <input id="brand-name" name="name" type="text" maxlength="120" value="{{ old('name') }}" required>
                </div>
            </div>
            <div class="modal-foot">
                <button class="btn" type="button" data-close-modal>Batal</button>
                <button class="btn primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
