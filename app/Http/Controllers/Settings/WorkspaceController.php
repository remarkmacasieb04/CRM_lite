<?php

namespace App\Http\Controllers\Settings;

use App\Enums\WorkspaceMemberRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreWorkspaceMemberRequest;
use App\Http\Requests\Settings\StoreWorkspaceRequest;
use App\Models\Workspace;
use App\Services\Workspaces\WorkspaceMemberManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public function __construct(
        private readonly WorkspaceMemberManager $workspaceMemberManager,
    ) {}

    public function edit(Request $request): Response
    {
        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('currentWorkspace');

        $this->authorize('view', $workspace);

        $workspace->load([
            'members' => fn ($query) => $query->orderBy('name'),
            'owner',
        ]);

        return Inertia::render('settings/Workspace', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'is_personal' => $workspace->is_personal,
                'owner' => [
                    'id' => $workspace->owner?->id,
                    'name' => $workspace->owner?->name,
                    'email' => $workspace->owner?->email,
                ],
                'members' => $workspace->members->map(fn ($member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => is_string($member->pivot?->role) ? $member->pivot->role : null,
                    'role_label' => is_string($member->pivot?->role)
                        ? WorkspaceMemberRole::from($member->pivot->role)->label()
                        : null,
                    'is_current_user' => $request->user()?->id === $member->id,
                ])->all(),
            ],
            'memberRoleOptions' => [
                [
                    'value' => WorkspaceMemberRole::Admin->value,
                    'label' => WorkspaceMemberRole::Admin->label(),
                ],
                [
                    'value' => WorkspaceMemberRole::Member->value,
                    'label' => WorkspaceMemberRole::Member->label(),
                ],
            ],
            'canManageMembers' => $request->user()?->can('manageMembers', $workspace) ?? false,
            'canCreateWorkspace' => $request->user() !== null,
        ]);
    }

    public function store(StoreWorkspaceRequest $request): RedirectResponse
    {
        $workspace = $this->workspaceMemberManager->createWorkspace(
            $request->user(),
            $request->validated('name'),
        );

        return to_route('workspace.edit')->with('success', "Workspace {$workspace->name} created.");
    }

    public function switch(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('switch', $workspace);

        $this->workspaceMemberManager->switchWorkspace(
            $request->user(),
            $workspace,
        );

        return to_route('dashboard')->with('success', "Switched to {$workspace->name}.");
    }

    public function storeMember(StoreWorkspaceMemberRequest $request): RedirectResponse
    {
        /** @var Workspace $workspace */
        $workspace = $request->attributes->get('currentWorkspace');

        $user = $this->workspaceMemberManager->addOrUpdateMember(
            $workspace,
            $request->validated('email'),
            WorkspaceMemberRole::from($request->validated('role')),
        );

        return back()->with('success', "{$user->email} can now access {$workspace->name}.");
    }
}
