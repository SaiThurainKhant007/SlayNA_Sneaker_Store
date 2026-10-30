{{--
    Image cropper for <input type="file" data-crop>. Include once per page; loads only where used.
      data-crop="4/3"      starting aspect ratio ("free" or empty = any shape); the viewer can switch to Free
      data-crop-max="1600" longest side of the saved image in px (default 1600)
      data-crop-limit="8"  (multiple inputs) most files that can be chosen
      data-crop-main="x"   (multiple inputs) adds a "Main" radio named x with value "new:{index}" to each chosen image
    Picking a file opens the cropper; "Crop & use" swaps the cropped image into the input, "Use original"
    keeps the file as it was, Cancel skips it. Multiple files are cropped one after another, and on a
    `multiple` input each new pick is added to the earlier ones. Every chosen image has a × to remove it.
--}}
@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
    @endpush
    @push('scripts')
        <div class="modal fade sl-crop-modal" id="slCropModal" tabindex="-1" aria-labelledby="slCropTitle" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="slCropTitle">Crop image <small class="text-muted fw-normal" data-crop-count></small></h2>
                        <button type="button" class="btn-close" data-crop-action="cancel" aria-label="Cancel"></button>
                    </div>
                    <div class="modal-body">
                        <div class="sl-crop-stage"><img alt="Image being cropped" data-crop-image></div>
                        <div class="sl-crop-tools" role="toolbar" aria-label="Crop tools">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Shape" data-crop-ratios></div>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Rotate and flip">
                                <button type="button" class="btn btn-outline-secondary" data-crop-action="rotate-left" title="Rotate left" aria-label="Rotate left"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></button>
                                <button type="button" class="btn btn-outline-secondary" data-crop-action="rotate-right" title="Rotate right" aria-label="Rotate right"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i></button>
                                <button type="button" class="btn btn-outline-secondary" data-crop-action="flip" title="Flip horizontally" aria-label="Flip horizontally"><i class="bi bi-symmetry-vertical" aria-hidden="true"></i></button>
                                <button type="button" class="btn btn-outline-secondary" data-crop-action="reset" title="Reset" aria-label="Reset"><i class="bi bi-arrow-repeat" aria-hidden="true"></i></button>
                            </div>
                            <span class="small text-muted d-none d-md-inline">Drag to move · scroll to zoom</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary me-auto" data-crop-action="cancel">Cancel</button>
                        <button type="button" class="btn btn-outline-dark" data-crop-action="original">Use original</button>
                        <button type="button" class="btn btn-dark" data-crop-action="crop"><i class="bi bi-crop me-1" aria-hidden="true"></i>Crop &amp; use</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
        <script>
        (() => {
            const modalEl = document.getElementById('slCropModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const img = modalEl.querySelector('[data-crop-image]');
            const ratiosEl = modalEl.querySelector('[data-crop-ratios]');
            const countEl = modalEl.querySelector('[data-crop-count]');
            const CROPPABLE = ['image/jpeg', 'image/png', 'image/webp'];
            const EXT = { 'image/jpeg': 'jpg', 'image/png': 'png', 'image/webp': 'webp' };

            let cropper = null, current = null, flipped = false;

            const parseRatio = (value) => {
                if (!value || value === 'free') return NaN;
                const [w, h] = value.split('/').map(Number);
                return h ? w / h : Number(w) || NaN;
            };

            // One file through the modal; resolves with a File, or null when cancelled
            function cropOne(file, input, index, total) {
                return new Promise((resolve) => {
                    const preset = (input.dataset.crop || '').trim();
                    const presetRatio = parseRatio(preset);
                    const url = URL.createObjectURL(file);
                    current = { file, input, resolve, url };
                    flipped = false;
                    countEl.textContent = total > 1 ? `(${index + 1} of ${total})` : '';

                    ratiosEl.innerHTML = '';
                    const options = [];
                    if (!isNaN(presetRatio)) options.push([preset.replace('/', ':'), presetRatio]);
                    options.push(['Free', NaN]);
                    if (isNaN(presetRatio) || presetRatio !== 1) options.push(['1:1', 1]);
                    options.forEach(([label, ratio], i) => {
                        const b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'btn btn-outline-secondary' + (i === 0 ? ' active' : '');
                        b.textContent = label;
                        b.setAttribute('aria-pressed', i === 0 ? 'true' : 'false');
                        b.addEventListener('click', () => {
                            ratiosEl.querySelectorAll('button').forEach((x) => { x.classList.remove('active'); x.setAttribute('aria-pressed', 'false'); });
                            b.classList.add('active'); b.setAttribute('aria-pressed', 'true');
                            cropper?.setAspectRatio(ratio);
                        });
                        ratiosEl.appendChild(b);
                    });

                    img.src = url;
                    modal.show();
                    modalEl.addEventListener('shown.bs.modal', () => {
                        cropper = new Cropper(img, {
                            aspectRatio: presetRatio,
                            viewMode: 1,
                            autoCropArea: 1,
                            responsive: true,
                            background: false,
                        });
                    }, { once: true });
                });
            }

            function finish(result) {
                const { resolve, url } = current;
                current = null;
                modalEl.addEventListener('hidden.bs.modal', () => {
                    cropper?.destroy(); cropper = null;
                    URL.revokeObjectURL(url);
                    img.removeAttribute('src');
                    resolve(result);
                }, { once: true });
                modal.hide();
            }

            modalEl.addEventListener('click', (e) => {
                const action = e.target.closest('[data-crop-action]')?.dataset.cropAction;
                if (!action || !current) return;
                if (action === 'rotate-left') cropper?.rotate(-90);
                if (action === 'rotate-right') cropper?.rotate(90);
                if (action === 'flip') { flipped = !flipped; cropper?.scaleX(flipped ? -1 : 1); }
                if (action === 'reset') { flipped = false; cropper?.reset(); }
                if (action === 'cancel') finish(null);
                if (action === 'original') finish(current.file);
                if (action === 'crop' && cropper) {
                    const { file, input } = current;
                    const max = parseInt(input.dataset.cropMax || '1600', 10);
                    const type = CROPPABLE.includes(file.type) ? file.type : 'image/jpeg';
                    const canvas = cropper.getCroppedCanvas({
                        maxWidth: max, maxHeight: max,
                        imageSmoothingQuality: 'high',
                        fillColor: type === 'image/jpeg' ? '#fff' : 'transparent',
                    });
                    canvas.toBlob((blob) => {
                        if (!blob) { finish(file); return; }
                        const outType = blob.type || type;   // some browsers save WebP as PNG
                        const name = file.name.replace(/\.[^.]+$/, '') + '.' + (EXT[outType] || 'jpg');
                        finish(new File([blob], name, { type: outType, lastModified: Date.now() }));
                    }, type, 0.9);
                }
            });

            // Put the chosen files back into the input and redraw the previews
            function setFiles(input, files, removedIndex) {
                input._slFiles = files;
                const dt = new DataTransfer();
                files.forEach((f) => dt.items.add(f));
                input.files = dt.files;
                showPreview(input, removedIndex);
            }

            // Preview of each chosen image under the input, each with a × to take it out again.
            // With data-crop-main="field", each also gets a "Main" radio (value "new:{index}").
            function showPreview(input, removedIndex) {
                const files = input._slFiles || [];
                const mainName = input.dataset.cropMain;
                let box = input.parentElement.querySelector(':scope > .sl-crop-preview');
                if (!box) {
                    box = document.createElement('div');
                    box.className = 'sl-crop-preview';
                    input.insertAdjacentElement('afterend', box);
                }

                // Keep the Main choice on the same photo when another one is removed
                let mainIndex = null;
                const checked = box.querySelector('input[type=radio]:checked');
                if (checked) {
                    mainIndex = parseInt(checked.value.split(':')[1], 10);
                    if (removedIndex !== undefined) {
                        if (mainIndex === removedIndex) mainIndex = null;
                        else if (mainIndex > removedIndex) mainIndex--;
                    }
                }

                box.querySelectorAll('img').forEach((i) => URL.revokeObjectURL(i.src));
                box.innerHTML = '';
                files.forEach((f, index) => {
                    const item = document.createElement('div');
                    item.className = 'sl-crop-preview-item';
                    const i = document.createElement('img');
                    i.src = URL.createObjectURL(f);
                    i.alt = 'Chosen image ' + (index + 1);
                    const x = document.createElement('button');
                    x.type = 'button';
                    x.className = 'sl-crop-preview-remove';
                    x.title = 'Remove this image';
                    x.setAttribute('aria-label', 'Remove chosen image ' + (index + 1) + ' (' + f.name + ')');
                    x.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
                    x.addEventListener('click', () => {
                        setFiles(input, (input._slFiles || []).filter((_, n) => n !== index), index);
                        input.focus();
                    });
                    item.append(i, x);
                    if (mainName) {
                        const pick = document.createElement('label');
                        pick.className = 'sl-photo-main-pick';
                        const r = document.createElement('input');
                        r.type = 'radio';
                        r.name = mainName;
                        r.value = 'new:' + index;
                        r.className = 'form-check-input mt-0';
                        r.checked = index === mainIndex;
                        pick.append(r, ' Main');
                        item.appendChild(pick);
                    }
                    box.appendChild(item);
                });

                // No current photos to choose from: the first new photo is the main one until another is picked
                if (mainName && files.length && input.form) {
                    const anyOutside = Array.from(input.form.querySelectorAll(`input[type=radio][name="${mainName}"]`)).some((r) => !box.contains(r));
                    if (!anyOutside && !box.querySelector('input[type=radio]:checked')) box.querySelector('input[type=radio]').checked = true;
                }
                const limit = parseInt(input.dataset.cropLimit || '', 10);
                if (input.multiple && files.length && !isNaN(limit)) {
                    const note = document.createElement('small');
                    note.className = 'sl-crop-preview-note text-muted';
                    note.textContent = `${files.length} of ${limit} chosen`;
                    box.appendChild(note);
                }
                box.hidden = files.length === 0;
            }

            document.addEventListener('change', async (e) => {
                const input = e.target;
                if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.hasAttribute('data-crop')) return;
                if (input._slCropping) return;
                // Multi-file fields add to what was already chosen; single-file fields replace it
                const before = input.multiple ? (input._slFiles || []) : [];
                let picked = Array.from(input.files || []);
                if (!picked.length) { setFiles(input, before); return; }

                const limit = parseInt(input.dataset.cropLimit || '', 10);
                if (!isNaN(limit) && before.length + picked.length > limit) {
                    const room = Math.max(0, limit - before.length);
                    alert(room
                        ? `Only ${limit} photos can be added here, so just the first ${room} of these will be used.`
                        : `You've already chosen the maximum of ${limit} photos. Remove one to add another.`);
                    picked = picked.slice(0, room);
                }

                input._slCropping = true;
                const kept = [];
                for (let i = 0; i < picked.length; i++) {
                    const f = picked[i];
                    if (!CROPPABLE.includes(f.type)) { kept.push(f); continue; }  // GIFs etc. go up untouched
                    const out = await cropOne(f, input, i, picked.length);
                    if (out) kept.push(out);
                }
                setFiles(input, before.concat(kept));
                input._slCropping = false;
            });

            // The page can ask for the previews to be redrawn (e.g. after the limit changed)
            document.addEventListener('sl-crop-refresh', (e) => showPreview(e.target), true);

            // A form reset clears the chosen images too
            document.addEventListener('reset', (e) => {
                e.target.querySelectorAll?.('input[type=file][data-crop]').forEach((input) => setTimeout(() => setFiles(input, []), 0));
            });
        })();
        </script>
    @endpush
@endonce
