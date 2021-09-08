<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUser;
use App\Http\Requests\UpdateUser;
use App\Http\Requests\UpdateUserSpaces;
use App\Http\Resources\User as UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<UserResource>
     */
    public function index()
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters(
                [
                    AllowedFilter::exact('id'),
                    'username',
                    'first_name',
                    'last_name',
                    'email',
                    AllowedFilter::exact('gtid'),
                ]
            )
            ->allowedSorts('username', 'first_name', 'last_name', 'gtid')
            ->allowedIncludes(['visits', 'spaces'])
            ->get();

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreUser  $request
     *
     * @return \App\Http\Resources\User
     */
    public function store(StoreUser $request)
    {
        $user = User::create($request->all());

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \App\Http\Resources\User
     */
    public function showSelf(Request $request)
    {
        $q_user = QueryBuilder::for(User::class)
            ->where('id', $request->user()->id)
            ->allowedIncludes(['visits', 'spaces'])
            ->first();

        return new UserResource($q_user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     *
     * @return \App\Http\Resources\User
     */
    public function show(User $user)
    {
        $q_user = QueryBuilder::for(User::class)
            ->where('id', $user->id)
            ->allowedIncludes(['visits', 'spaces'])
            ->first();

        return new UserResource($q_user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateUser  $request
     * @param  User  $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUser $request, User $user)
    {
        try {
            $user->update($request->all());
            $updatedUser = new UserResource($user);

            return response()->json($updatedUser);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateUserSpaces  $request
     * @param  User  $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSpaces(UpdateUserSpaces $request, User $user)
    {
        try {
            $user->spaces()->sync($request->input('spaces'));
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $dbUser = User::where('id', $user->id)->with('spaces', 'visits')->first();
        $dbUserResource = new UserResource($dbUser);

        return response()->json(['status' => 'success', 'user' => $dbUserResource]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();

            return response()->json('success');
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
