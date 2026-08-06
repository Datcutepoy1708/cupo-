<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreAddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $validated['is_default'] = $request->has('is_default');

        // Nếu đặt làm mặc định hoặc đây là địa chỉ đầu tiên -> bỏ mặc định của các địa chỉ khác
        if ($validated['is_default'] || $user->addresses()->count() === 0) {
            $validated['is_default'] = true;
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($validated);

        return back()
            ->with('status', 'address-created')
            ->with('active_tab', 'addressBook');
    }

    public function update(StoreAddressRequest $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validated();
        $validated['is_default'] = $request->has('is_default');

        if ($validated['is_default']) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()
            ->with('status', 'address-updated')
            ->with('active_tab', 'addressBook');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // Nếu xóa địa chỉ mặc định, tự động gán địa chỉ còn lại làm mặc định
        if ($wasDefault) {
            $firstAddress = $request->user()->addresses()->first();
            if ($firstAddress) {
                $firstAddress->update(['is_default' => true]);
            }
        }

        return back()
            ->with('status', 'address-deleted')
            ->with('active_tab', 'addressBook');
    }

    public function setDefault(Request $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()
            ->with('status', 'address-default-updated')
            ->with('active_tab', 'addressBook');
    }
}
