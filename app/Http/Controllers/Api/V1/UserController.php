<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\UpdateMeRequest;
use App\Http\Requests\Api\V1\UpdateProfilePictureRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class UserController
{
    public function updateMe(UpdateMeRequest $request): UserResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user->fresh());
    }

    public function updateProfilePicture(UpdateProfilePictureRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('profile_picture')->storePublicly('profile-pictures', 'public');

        $user->forceFill([
            'profile_picture' => $path,
        ])->save();

        return (new UserResource($user->fresh()))
            ->response()
            ->setStatusCode(201);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $needle = $data['username'];

        $users = User::query()
            ->whereKeyNot($request->user()->id)
            ->where('username', 'like', $needle.'%')
            ->orderBy('username')
            ->limit(10)
            ->get();

        return UserResource::collection($users);
    }

    public function updatePushToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:2048'],
        ]);

        $user = $request->user();
        $user->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return response()->json([
            'message' => 'Push token updated.',
        ]);
    }
}
