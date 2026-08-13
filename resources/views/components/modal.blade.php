@props(['name', 'title' => null, 'maxWidth' => 'md', 'static' => false])

@php
    $maxWidthClass =
        [
            'sm' => 'modal-sm',
            'md' => '',
            'lg' => 'modal-lg',
            'xl' => 'modal-xl',
        ][$maxWidth] ?? '';
@endphp

<div class="modal fade" id="{{ $name }}" tabindex="-1" aria-labelledby="{{ $name }}-label"
    aria-hidden="true"
    @if ($static) data-bs-backdrop="static"
        data-bs-keyboard="false" @endif>
    <div class="modal-dialog modal-dialog-centered {{ $maxWidthClass }}">
        <div class="modal-content">

            @if ($title || isset($header))
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $name }}-label">
                        {{ $header ?? $title }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset

        </div>
    </div>
</div>
