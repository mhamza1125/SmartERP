{{--
  Delete button with a dependency check (custom.js: .js-delete-record).
  Params: url (DELETE route), check (delete-check route), label (record name shown in the dialog),
          class (optional button classes), text (optional button text).
  Wrap the include in @can('delete', ...) so it only shows to users with the delete permission.
--}}
<button type="button"
        class="{{ $class ?? 'btn btn-danger btn-sm' }} js-delete-record"
        data-url="{{ $url }}"
        data-check="{{ $check ?? '' }}"
        data-label="{{ $label ?? 'this record' }}"
        title="Delete">{!! $text ?? 'Delete' !!}</button>
