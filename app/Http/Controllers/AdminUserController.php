<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->orderBy('created_at')
            ->withCount(['lessonProgresses', 'blockAttempts'])
            ->get([
                'id',
                'uuid',
                'name',
                'email',
                'is_admin',
                'is_approved',
                'email_verified_at',
                'created_at',
            ]);

        return Inertia::render('admin/users/Index', [
            'users' => $users,
        ]);
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user->loadCount(['lessonProgresses', 'blockAttempts']);

        return Inertia::render('admin/users/Edit', [
            'user' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    public function resetProgress(User $user): RedirectResponse
    {
        $this->authorize('resetProgress', $user);

        $user->lessonProgresses()->delete();
        $user->blockAttempts()->delete();
        $user->courses()->detach();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Progress user berhasil direset.');
    }

    public function approve(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update(['is_approved' => true]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil disetujui.');
    }

    public function reject(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->update(['is_approved' => false]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditolak.');
    }
}
