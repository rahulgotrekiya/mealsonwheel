<script src="{{ asset('assets/panel/libs/ckeditor/ckeditor.js') }}"></script>
<script>
    ['#editor-description', '#editor-additional'].forEach(function (selector) {
        ClassicEditor.create(document.querySelector(selector)).catch(function (error) {
            console.error(error);
        });
    });

    /*
     * The file input is a staging area: files chosen across several goes are
     * collected here, individually removable, and only the survivors are put
     * into the input that actually submits.
     */
    (function () {
        var picker = document.getElementById('product-images');
        var preview = document.getElementById('image-preview');
        var holder = document.getElementById('image-input-holder');
        var chosen = new DataTransfer();

        function syncInput() {
            holder.innerHTML = '';

            var input = document.createElement('input');
            input.type = 'file';
            input.name = 'images[]';
            input.multiple = true;
            input.hidden = true;
            input.files = chosen.files;

            holder.appendChild(input);
        }

        function render() {
            preview.innerHTML = '';

            Array.from(chosen.files).forEach(function (file, index) {
                var col = document.createElement('div');
                col.className = 'col-6 mb-2';

                var card = document.createElement('div');
                card.className = 'card mb-0';

                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'btn btn-sm btn-danger';
                remove.innerHTML = '<i class="mdi mdi-close"></i>';
                remove.addEventListener('click', function () {
                    var kept = new DataTransfer();

                    Array.from(chosen.files).forEach(function (f, i) {
                        if (i !== index) kept.items.add(f);
                    });

                    chosen = kept;
                    render();
                    syncInput();
                });

                var header = document.createElement('div');
                header.className = 'card-header p-1 text-end';
                header.appendChild(remove);

                var img = document.createElement('img');
                img.className = 'card-img-top';
                img.style.height = '110px';
                img.style.objectFit = 'cover';

                var reader = new FileReader();
                reader.onload = function (e) { img.src = e.target.result; };
                reader.readAsDataURL(file);

                var body = document.createElement('div');
                body.className = 'card-body p-2 text-center';
                var name = document.createElement('small');
                name.className = 'text-muted';
                name.textContent = file.name.length > 16 ? file.name.slice(0, 13) + '...' : file.name;
                body.appendChild(name);

                card.appendChild(header);
                card.appendChild(img);
                card.appendChild(body);
                col.appendChild(card);
                preview.appendChild(col);
            });
        }

        picker.addEventListener('change', function () {
            Array.from(picker.files).forEach(function (file) { chosen.items.add(file); });
            picker.value = '';
            render();
            syncInput();
        });

        syncInput();
    })();
</script>
