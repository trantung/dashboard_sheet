<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            // 'website' => $user->website,
            // 'location' => $user->location,
            // 'company' => $user->company,
            'bio' => $user->bio,
            'avatar' => $user->avatar,
            'google_id' => $user->google_id,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            // 'website' => 'nullable|url|max:255',
            // 'location' => 'nullable|string|max:255',
            // 'company' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            // 'website' => $request->website,
            // 'location' => $request->location,
            // 'company' => $request->company,
            'bio' => $request->bio,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ]);

        $user = Auth::user();

        try {
            // Xoá avatar cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $file = $request->file('avatar');
            $filename = 'avatars/' . $user->id . '_' . time() . '.jpg';

            // ✅ Khởi tạo ImageManager với GdDriver
            $manager = new ImageManager(new GdDriver());

            // Resize, encode
            $image = $manager->read($file)
                            ->cover(300, 300)
                            ->toJpeg(85);

            // Lưu ảnh vào disk public
            Storage::disk('public')->put($filename, $image->toString());

            // Cập nhật DB
            $user->update(['avatar' => $filename]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'avatar_url' => Storage::disk('public')->url($filename)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeAvatar()
    {
        $user = Auth::user();

        try {
            // Delete avatar file if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Update user avatar
            $user->update([
                'avatar' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Check if user has Google ID (Google users can't change password)
        if ($user->google_id) {
            return response()->json([
                'success' => false,
                'message' => 'Password change not available for Google accounts'
            ], 400);
        }

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['Current password is incorrect']
                ]
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    public function destroy()
    {
        $user = Auth::user();

        try {
            // Delete avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Delete user (this will cascade delete related data)
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage()
            ], 500);
        }
    }
}
