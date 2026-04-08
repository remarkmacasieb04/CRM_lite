<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\Admin\UserRoleManager;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly UserRoleManager $userRoleManager,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->withCount('clients')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value,
                'role_label' => $user->role?->label(),
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'receives_follow_up_reminders' => $user->receives_follow_up_reminders,
                'last_follow_up_digest_sent_at' => $user->last_follow_up_digest_sent_at?->toDateString(),
                'clients_count' => $user->clients_count,
                'created_at' => $user->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/Users', [
            'users' => $users,
            'roleOptions' => UserRole::options(),
        ]);
    }

    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $this->userRoleManager->updateRole(
            $user,
            UserRole::from($request->validated('role')),
        );

        return back()->with('success', "{$user->name}'s role was updated successfully.");
    }
}
