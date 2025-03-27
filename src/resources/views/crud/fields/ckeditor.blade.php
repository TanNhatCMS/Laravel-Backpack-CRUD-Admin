<!-- CKeditor -->
@php
$field['extra_plugins'] = isset($field['extra_plugins']) ? implode(',', $field['extra_plugins']) : "embed,widget";

$defaultOptions = [
"language" => "en",
"toolbar" => [
    "undo",
    "redo",
    "|",
    "heading",
    "outdent",
    "indent",
    "|",
    "bold",
    "italic",
    "|",
    "blockQuote",
    "insertTable",
    "bulletedList",
    "numberedList",
    "|",
    "link",
    "mediaEmbed"
],
"filebrowserBrowseUrl" => backpack_url('elfinder/ckeditor'),
"extraPlugins" => $field['extra_plugins'],
"embed_provider" => "//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}",
];

$field['options'] = array_merge($defaultOptions, $field['options'] ?? []);
@endphp

@include('crud::fields.inc.wrapper_start')
<div
	class="form-group col-sm-12 mb-3 required"
	element="div"
	bp-field-wrapper="true"
	bp-field-name="content"
	bp-field-type="ckeditor"
	bp-section="crud-field"
>
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')
<textarea
    name="{{ $field['name'] }}"
    data-init-function="bpFieldInitCKEditorElement"
    data-options="{{ trim(json_encode($field['options'])) }}"
    data-elfinder="false"
    bp-field-main-input
    @include('crud::fields.inc.attributes', ['default_class' => 'form-control'])
>{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}</textarea>
</div>
{{-- HINT --}}
@if (isset($field['hint']))
<p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
@php
$crud->markFieldTypeAsLoaded($field);
@endphp

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
@basset('https://cdn.ckeditor.com/ckeditor5/37.1.0/classic/ckeditor.js')
@bassetBlock('ProFieldsCkeditor.js')
<script type="text/javascript">
// global variable to store elfinder element while waiting for colorbox to close
var elfinderTarget = false;

// if processSelectedMultipleFiles is not defined, define it
if (typeof window.processSelectedMultipleFiles !== 'function') {
    function processSelectedMultipleFiles(files, input) {
        elfinderTarget.trigger('createInputsForItemsSelectedWithElfinder', [files]);
        elfinderTarget = false;
    }
}

async function bpFieldInitCKEditorElement(element) {
    let hasElfinder = element.data('elfinder');
    let elfinderTriggerUrl = element.data('elfinder-trigger-url');
    // To create CKEditor 5 classic editor
    let ckeditorInstance = await ClassicEditor.create(element[0], element.data('options')).catch(error => {
        console.error( error );
    });

    if (!ckeditorInstance) return;
    if(hasElfinder) {
        let ckf = ckeditorInstance.commands.get('ckfinder');
        if (ckf) {
            // Take over ckfinder execute()
            ckf.execute = () => {
                window.ckeditorInstance = ckeditorInstance;
                window.elfinderOptions = element.data('elfinder-options') ?? {};

                // remember which element the elFinder was triggered by
                elfinderTarget = element;
                
                // trigger the reveal modal with elfinder inside
                $.colorbox({
                    href: elfinderTriggerUrl,
                    fastIframe: false,
                    iframe: true,
                    width: '80%',
                    height: '80%',
                    onClosed: function () {
                        elfinderTarget = false;
                        window.ckeditorInstance = null;
                        window.elfinderOptions = {};
                    },
                });
            };
        }

        element.on('createInputsForItemsSelectedWithElfinder', function (e, files) {
            let imgs = [];
            
            $.each(files, function(i, f) {
                if (f && f.mime.match(/^image\//i)) {
                    imgs.push(f.url);
                } else {
                    ckeditorInstance.execute('link', f.url);
                }
            });
            if (imgs.length) {
                const ntf = ckeditorInstance.plugins.get('Notification');
                const i18 = ckeditorInstance.locale.t;
                const imgCmd = ckeditorInstance.commands.get('imageUpload');
                if (!imgCmd.isEnabled) {
                    ntf.showWarning(i18('Could not insert image at the current position.'), {
                        title: i18('Inserting image failed'),
                        namespace: 'ckfinder'
                    });
                    return;
                }
                ckeditorInstance.execute('imageInsert', { source: imgs });
            }
        });
    }

    element.on('CrudField:delete', function (e) {
        ckeditorInstance.destroy();
    });

    // trigger the change event on textarea when ckeditor changes
    ckeditorInstance.editing.view.document.on('layoutChanged', function (e) {
        element.trigger('change');
    });

    element.on('CrudField:disable', function (e) {
        ckeditorInstance.enableReadOnlyMode('CrudField');
    });

    element.on('CrudField:enable', function (e) {
        ckeditorInstance.disableReadOnlyMode('CrudField');
    });
}
</script>
@endBassetBlock
@endpush

@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
