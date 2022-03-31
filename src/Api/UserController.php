<?php

namespace App\Api;

use App\Entity\User;
use App\Exception\ConflictException;
use App\Exception\NotFoundException;
use App\Form\EditUserType;
use App\Repository\UserRepository;
use App\Responses\ApiResponse;
use App\Service\UserService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class UserController extends ApiController
{
    private const BATCH_SIZE = 20;

    /**
     * @Route ("/users", name="api_user_list",  methods={"GET"})
     *
     * @SWG\Get(description="", tags={"Get user list"})
     * @SWG\Parameter(name="offset", in="query", description="Items offset", type="integer")
     * @SWG\Parameter(name="count", in="query", description="Items count", type="integer")
     * @SWG\Response(response=200, description="User list",
     *     examples= {
     *      "application/json":{"data":{"id":1,"email":"test@test.com","name":"test test"}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     *
     */
    public function getUsers(Request $request, UserRepository $repository): ApiResponse
    {
        $offset = (int)$request->get('offset', 0);
        $count = (int)$request->get('count', self::BATCH_SIZE);
        return new ApiResponse($repository->getUserList($offset, $count), Response::HTTP_OK);
    }

    /**
     * @Route ("/users/{id}/groups", name="api_user_groups_list",  methods={"GET"})
     *
     * @SWG\Get(description="", tags={"Get user groups list"})
     * @SWG\Response(response=200, description="User groups list"
     * )
     * @SWG\Response(response=403, description="Forbidden")
     * @SWG\Response(response=404, description="Not found")
     *
     */
    public function getUserGroups(int $id, UserRepository $repository): ApiResponse
    {
        $user = $repository->find($id);
        if (!$user) {
            throw new NotFoundException('User not found!');
        }
        return new ApiResponse($user->getGroups(), Response::HTTP_OK);
    }

    /**
     * @Route ("/me", name="api_user_me",  methods={"GET"})
     *
     * @SWG\Get(description="", tags={"Get info about current user"})
     * @SWG\Response(response=200, description="Info about myself",
     *     examples= {
     *      "application/json":{"data":{"id":1,"email":"test@test.com","name":"test test"}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     *
     */
    public function getMe(Security $security): ApiResponse
    {
        /** @var User|null $user */
        $user = $security->getUser();
        return new ApiResponse($user, Response::HTTP_OK);
    }

    /**
     * @Route ("/users/{id}", name="api_user_info",  methods={"GET"})
     *
     * @SWG\Get(description="", tags={"Get info about user"})
     * @SWG\Response(response=200, description="Info about myself",
     *     examples= {
     *      "application/json":{"data":{"id":1,"email":"test@test.com","name":"test test"}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     *
     */
    public function getUserInfo(int $id, UserRepository $repository): ApiResponse
    {
        return new ApiResponse($repository->find($id), Response::HTTP_OK);
    }

    /**
     * @Route ("/users/{id}", name="api_user_kill",  methods={"DELETE"})
     *
     * @SWG\Delete(description="Delete user", tags={"Delete user"})
     * @SWG\Response(response=200, description="Killed",
     *     examples= {
     *      "application/json":{"data":{}}
     *     }
     * )
     * @SWG\Response(response=409, description="Forbidden", examples= {
     *      "application/json":{"data": null,"error": {"code": 409, "message": "Can't kill myself!"} } })
     *
     * @param Security $security
     * @param int $id
     * @param UserService $userService
     * @return ApiResponse
     */
    public function killUser(Security $security, int $id, UserService $userService): ApiResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if ($user->getUserIdentifier() === $id) {
            throw new ConflictException("Can't kill myself!");
        }
        $userService->killUser($id);
        return new ApiResponse([], Response::HTTP_OK);
    }

    /**
     * @Route ("/users/{id}", name="api_user_edit",  methods={"PATCH"})
     *
     * @SWG\Patch(description="", tags={"Update user"})
     * @SWG\Parameter(name="body", in="body",description="Body json params", @Model(type=EditUserType::class))
     * @SWG\Response(response=200, description="Info about myself",
     *     examples= {
     *      "application/json":{"data":{"id":1,"email":"test@test.com","name":"test test"}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     */
    public function editUser(Request $request, int $id, UserService $userService): ApiResponse
    {
        $data = $this->validateRequestWithClass($request, EditUserType::class);
        $user = $userService->editUser($id, $data);
        return new ApiResponse($user, Response::HTTP_OK);
    }

    /**
     * @Route ("/users/{userId}/group/{groupId}", name="api_user_add_to_group",  methods={"PUT"})
     *
     * @SWG\Put(description="", tags={"Add user to group"})
     * @SWG\Response(response=200, description="Added",
     *     examples= {
     *      "application/json":{"data":{}}
     *     }
     * )
     *
     * @SWG\Response(response=403, description="Forbidden")
     * @SWG\Response(response=404, description="Not found")
     *
     * @param int $userId
     * @param int $groupId
     * @param UserService $userService
     * @return ApiResponse
     */
    public function addToGroup(int $userId, int $groupId, UserService $userService): ApiResponse
    {
        $userService->addToGroup($userId, $groupId);
        return new ApiResponse([], Response::HTTP_OK);
    }

    /**
     * @Route ("/users/{userId}/group/{groupId}", name="api_user_delete_from_group",  methods={"DELETE"})
     *
     * @SWG\Delete (description="", tags={"Delete user from group"})
     * @SWG\Response(response=200, description="Deleted",
     *     examples= {
     *      "application/json":{"data":{}}
     *     }
     * )
     *
     * @SWG\Response(response=403, description="Forbidden")
     * @SWG\Response(response=404, description="Not found")
     *
     * @param int $userId
     * @param int $groupId
     * @param UserService $userService
     * @return ApiResponse
     */
    public function removeFromGroup(int $userId, int $groupId, UserService $userService): ApiResponse
    {
        $userService->removeFromGroup($userId, $groupId);
        return new ApiResponse([], Response::HTTP_OK);
    }
}