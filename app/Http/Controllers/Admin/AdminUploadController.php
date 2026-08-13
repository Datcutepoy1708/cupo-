<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUploadController extends Controller
{
    /**
     * POST /admin/upload
     * Upload ảnh từ máy tính cho Admin (Banner, Product, Category, Seller, Avatar...)
     * Trả về đường dẫn tương đối root (/storage/folder/file.ext) tương thích mọi domain / port / localhost.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // Max 5MB
            'folder' => ['nullable', 'string', 'in:banners,products,categories,sellers,avatars,uploads'],
        ], [
            'file.required' => 'Vui lòng chọn tệp hình ảnh để tải lên.',
            'file.image' => 'Tệp được chọn phải là định dạng hình ảnh.',
            'file.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'file.max' => 'Dung lượng ảnh tối đa là 5MB.',
        ]);

        $folder = $request->input('folder', 'uploads');
        $file = $request->file('file');

        // Lưu tệp vào storage/app/public/{folder}
        $path = $file->store($folder, 'public');

        // Tạo đường dẫn tương đối gốc /storage/... để luôn hoạt động chính xác trên mọi tên miền/port
        $url = '/storage/'.ltrim($path, '/');

        return response()->json([
            'status' => 'success',
            'message' => 'Tải hình ảnh lên thành công!',
            'url' => $url,
            'path' => $path,
        ], 200);
    }
}
