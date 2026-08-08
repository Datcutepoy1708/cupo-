<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerRegistrationRequest;
use App\Models\SellerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
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
            $user->update([
                'role' => 'seller',
                'date_of_birth' => Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d'),
            ]);

            $sellerProfile = SellerProfile::create([
                'user_id' => $user->id,
                'shop_name' => $request->shop_name,
                'business_type' => $request->input('business_type', 'personal'),
                'slug' => Str::slug($request->shop_name).'-'.Str::random(5),
                'address' => $request->address,
                'description' => $request->description,
                'national_id' => $request->national_id,
                'status' => 'pending',
            ]);

            if ($request->filled('category_ids')) {
                $sellerProfile->categories()->sync($request->category_ids);
            }
        });

        return redirect()->route('seller.pending-approval');
    }

    public function pendingApproval(): View
    {
        return view('seller.pending-approval');
    }
}
