<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerRegistrationRequest;
use App\Models\SellerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SellerRegistrationController extends Controller
{
    public function create(): View
    {
        return view('seller.register');
    }

    public function store(SellerRegistrationRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $request) {
            $user->update(['role' => 'seller']);

            SellerProfile::create([
                'user_id' => $user->id,
                'shop_name' => $request->shop_name,
                'slug' => Str::slug($request->shop_name).'-'.Str::random(5),
                'address' => $request->address,
                'description' => $request->description,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('seller.pending-approval');
    }

    public function pendingApproval(): View
    {
        return view('seller.pending-approval');
    }
}
