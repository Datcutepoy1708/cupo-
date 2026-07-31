@once
    @push('styles')
        <style>
            .modal-content {
                border: none;
                border-radius: 14px;
                overflow: hidden;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.18);
            }

            .modal-header {
                background: var(--primary-red, #c62828);
                color: #fff;
                border-bottom: none;
                padding: 1.25rem 1.5rem;
            }

            .modal-header .modal-title {
                font-weight: 600;
                font-size: 1.15rem;
            }

            .modal-header .btn-close {
                filter: brightness(0) invert(1);
                opacity: 0.9;
            }

            .modal-header .btn-close:hover {
                opacity: 1;
            }

            .modal-body {
                padding: 1.75rem;
                background: #fff;
            }

            .modal-footer {
                background: #f8f9fa;
                border-top: 1px solid #eee;
                padding: 1rem 1.5rem;
            }

            .modal-footer .btn {
                padding: 0.5rem 1.4rem;
            }

            .modal-footer .btn-danger,
            .modal-footer .btn-primary {
                background: var(--primary-red, #c62828);
                border-color: var(--primary-red, #c62828);
            }

            .modal-footer .btn-danger:hover,
            .modal-footer .btn-primary:hover {
                background: var(--primary-red-dark, #b71c1c);
                border-color: var(--primary-red-dark, #b71c1c);
            }
        </style>
    @endpush
@endonce

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
