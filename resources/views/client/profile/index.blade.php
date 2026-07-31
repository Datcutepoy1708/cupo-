@extends('layouts.client.app')

@section('content')
    @php
        $activeTab = old('active_tab', session('active_tab', 'personal'));
    @endphp
    <div class="container-fluid">
        <div class="row">

            @include('client.profile.partials.sidebar')

            <div class="col-md-9 col-lg-10">
                <div class="content-area">
                    <div class="tab-content">
                        @include('client.profile.partials.personal-info')
                        @include('client.profile.partials.change-password')
                        @include('client.profile.partials.address-book')
                        @include('client.profile.partials.order-history')
                        @include('client.profile.partials.wishlist')
                        @include('client.profile.partials.seller-channel')
                    </div>
                </div>
            </div>

        </div>
    </div>

    @include('client.profile.modals.order-detail')
    @include('client.profile.modals.add-address')
    @include('client.profile.modals.edit-address')
@endsection
