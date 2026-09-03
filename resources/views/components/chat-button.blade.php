{{--
  Nút mở chat với người bán.
  Guest: chuyển tới đăng nhập.
  Customer: mở widget và tạo/chọn phòng.
  @var int $sellerId
--}}
@props(['sellerId'])

@php
    $user = auth()->user();
@endphp

@if (! $user)
    <a href="{{ route('login') }}" {{ $attributes->merge(['class' => 'js-open-chat']) }}>
        {{ $slot }}
    </a>
@elseif (in_array($user->role, ['customer', 'seller'], true) && $user->id !== (int) $sellerId)
    <button type="button" {{ $attributes->merge(['class' => 'js-open-chat']) }} data-seller-id="{{ $sellerId }}">
        {{ $slot }}
    </button>
@endif
