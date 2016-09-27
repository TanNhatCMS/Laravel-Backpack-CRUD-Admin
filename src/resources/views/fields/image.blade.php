  <div class="form-group col-md-12 image" data-preview="#{{ $field['name'] }}" data-aspectRatio="{{ isset($field['aspect_ratio'])?$field['aspect_ratio']:0 }}" data-crop="{{ $field['crop'] }}">
    <div>
        <label>{!! $field['label'] !!}</label>
    </div>
    <!-- Wrap the image or canvas element with a block element (container) -->
    <div class="row">
        <div class="col-sm-6" style="margin-bottom: 20px;">
            <img id="mainImage" src="{{ (isset($field['value']))?asset($field['value']):'' }}">
        </div>
        @if($field['crop'])
        <div class="col-sm-3">
            <div class="docs-preview clearfix">
                <div id="{{ $field['name'] }}" class="img-preview preview-lg">
                    <img src="" style="display: block; min-width: 0px !important; min-height: 0px !important; max-width: none !important; max-height: none !important; margin-left: -32.875px; margin-top: -18.4922px; transform: none;">
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="btn-group">
        <label class="btn btn-primary btn-file">
            {{ trans('backpack::crud.choose_file') }} <input type="file" accept="image/*" id="uploadImage" class="hide">
            <input type="hidden" id="hiddenImage" name="{{ $field['name'] }}">
        </label>
        @if($field['crop'])
        <button class="btn btn-default" id="rotateLeft" type="button" style="display: none;"><i class="fa fa-rotate-left"></i></button>
        <button class="btn btn-default" id="rotateRight" type="button" style="display: none;"><i class="fa fa-rotate-right"></i></button>
        <button class="btn btn-default" id="zoomIn" type="button" style="display: none;"><i class="fa fa-search-plus"></i></button>
        <button class="btn btn-default" id="zoomOut" type="button" style="display: none;"><i class="fa fa-search-minus"></i></button>
        <button class="btn btn-warning" id="reset" type="button" style="display: none;"><i class="fa fa-times"></i></button>
        @endif
        <button class="btn btn-danger" id="remove" type="button"><i class="fa fa-trash"></i></button>
    </div>
  </div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        {{-- YOUR CSS HERE --}}
        <link href="{{ asset('vendor/backpack/cropper/dist/cropper.min.css') }}" rel="stylesheet" type="text/css" />
        <style>
            .hide {
                display: none;
            }
            .btn-group {
                margin-top: 10px;
            }
            img {
                max-width: 100%; /* This rule is very important, please do not ignore this! */
            }
            .img-container, .img-preview {
                width: 100%;
                text-align: center;
            }
            .img-preview {
                float: left;
                margin-right: 10px;
                margin-bottom: 10px;
                overflow: hidden;
            }
            .preview-lg {
                width: 263px;
                height: 148px;
            }

            .btn-file {
                position: relative;
                overflow: hidden;
            }
            .btn-file input[type=file] {
                position: absolute;
                top: 0;
                right: 0;
                min-width: 100%;
                min-height: 100%;
                font-size: 100px;
                text-align: right;
                filter: alpha(opacity=0);
                opacity: 0;
                outline: none;
                background: white;
                cursor: inherit;
                display: block;
            }
        </style>
    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
