<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkspaceController extends Controller
{
    use AuthorizesRequests;
    // public function index()
    // {
    //     $user = Auth::user();
    //     $workspaces = $user->workspaces()->with('users')->get();
        
    //     return response()->json($workspaces);
    // }

    public function index()
    {
        $user = Auth::user();

        // Workspace do user tạo
        $owned = $user->workspaces;

        // Workspace được chia sẻ
        $shared = $user->sharedWorkspaces;

        // Gộp cả 2 lại
        $workspaces = $owned->merge($shared)->load('users');

        return response()->json($workspaces);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workspace = Workspace::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
        ]);

        return response()->json($workspace, 201);
    }

    public function show(Workspace $workspace)
    {
        $this->authorize('view', $workspace);
        
        return response()->json($workspace->load('users', 'sites'));
    }

    public function update(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workspace->update($request->only('name'));

        $workspace->load('users');

        return response()->json($workspace);
    }

    public function destroy(Workspace $workspace)
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return response()->json(['message' => 'Workspace deleted successfully']);
    }

    public function addUser(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();
        
        if (!$workspace->users()->where('user_id', $user->id)->exists()) {
            $workspace->users()->attach($user->id);
        }

        return response()->json(['message' => 'User added to workspace']);
    }

    public function removeUser(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $workspace->users()->detach($request->user_id);

        return response()->json(['message' => 'User removed from workspace']);
    }
}
