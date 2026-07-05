<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Users\CreateUser;
use App\Actions\Users\DeleteUser;
use App\Actions\Users\UpdateUser;
use App\Enums\Role;
use App\Http\Requests\Users\DestroyUserRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min($request->integer('per_page', 15), 100));

        // Admin herhangi bir rolü filtreleyebilir; yönetici (admin olmayan)
        // yalnız personel listesini görür (atama ekranı için).
        $restrictToStaff = ! $this->authUser($request)->hasRole(Role::Admin->value);

        $users = User::query()
            ->with('roles')
            ->when(
                $restrictToStaff,
                fn (Builder $query) => $query->whereHas(
                    'roles',
                    fn (Builder $roles) => $roles->where('name', Role::Staff->value),
                ),
            )
            ->when(
                ! $restrictToStaff && $request->filled('role'),
                fn (Builder $query) => $query->whereHas(
                    'roles',
                    fn (Builder $roles) => $roles->where('name', $request->string('role')->value()),
                ),
            )
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = $request->string('search')->value();
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate($perPage);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request, CreateUser $action): JsonResponse
    {
        $user = $action->handle(
            $request->string('name')->value(),
            $request->string('email')->value(),
            $request->filled('phone') ? $request->string('phone')->value() : null,
            $request->string('password')->value(),
            Role::from($request->string('role')->value()),
            $request->boolean('is_active', true),
        );

        return UserResource::make($user->load('roles'))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load('roles'));
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUser $action): UserResource
    {
        $action->handle($user, $request->validated());

        return new UserResource($user->refresh()->load('roles'));
    }

    public function destroy(DestroyUserRequest $request, User $user, DeleteUser $action): Response
    {
        $action->handle($user);

        return response()->noContent();
    }
}
