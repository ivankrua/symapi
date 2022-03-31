<?php

namespace App\Api;

use App\Entity\Group;
use App\Exception\NotFoundException;
use App\Form\AddGroupType;
use App\Form\EditGroupType;
use App\Repository\GroupRepository;
use App\Responses\ApiResponse;
use App\Service\GroupService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends ApiController
{
    private const BATCH_SIZE = 20;

    /**
     * @Route ("/groups", name="api_groups_list",  methods={"GET"})
     *
     * @SWG\Get(description="", tags={"Get groups list"})
     * @SWG\Parameter(name="offset", in="query", description="Items offset", type="integer")
     * @SWG\Parameter(name="count", in="query", description="Items count", type="integer")
     * @SWG\Response(response=200, description="Info about myself", @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Group::class, groups={"api"}))
     *     )
     * )
     * @SWG\Response(response=403, description="Forbidden")
     *
     */
    public function getGroups(Request $request, GroupRepository $groupRepository): ApiResponse
    {
        $offset = (int)$request->get('offset', 0);
        $count = (int)$request->get('count', self::BATCH_SIZE);
        return new ApiResponse($groupRepository->getGroupList($offset, $count), Response::HTTP_OK);
    }

    /**
     * @Route ("/groups/{id}", name="api_group_info",  methods={"GET"})
     *
     * @SWG\Get(description="", tags={"Get group info"})
     *
     * @SWG\Response(response=200, description="Info about group",
     *     examples= {
     *      "application/json":{"data": {"id": 1,"name": "test group"}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     * @SWG\Response(response=404, description="Not found")
     *
     */
    public function getGroupInfo(int $id, GroupRepository $groupRepository): ApiResponse
    {
        $group = $groupRepository->find($id);
        if (!$group) {
            throw new NotFoundException('Group not found');
        }
        return new ApiResponse($group, Response::HTTP_OK);
    }

    /**
     * @Route ("/groups", name="api_auth",  methods={"POST"})
     *
     * @SWG\Post(description="", tags={"Add group"})
     * @SWG\Parameter(name="body", in="body",description="Body json params", @Model(type=AddGroupType::class))
     * @SWG\Response(response=201, description="Group",
     *     examples= {
     *      "application/json":{"data": {"id": 1,"name": "test group"}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     *
     */
    public function addGroup(
        Request $request,
        GroupService $groupService
    ): ApiResponse {
        $data = $this->validateRequestWithClass($request, AddGroupType::class);
        return new ApiResponse($groupService->addGroup($data), Response::HTTP_OK);
    }

    /**
     * @Route ("/groups/{id}", name="api_group_edit",  methods={"PATCH"})
     *
     * @SWG\Patch (description="", tags={"Edit group"})
     * @SWG\Parameter(name="body", in="body",description="Body json params", @Model(type=EditGroupType::class))
     * @SWG\Response(response=200, description="Group",
     *     examples= {
     *      "application/json":{"data": {"id": 1,"name": "test group"}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     *
     */
    public function editGroup(
        Request $request,
        int $id,
        GroupService $groupService
    ): ApiResponse {
        $data = $this->validateRequestWithClass($request, EditGroupType::class);
        return new ApiResponse($groupService->editGroup($id, $data), Response::HTTP_OK);
    }

    /**
     * @Route ("/groups/{id}", name="api_group_kill",  methods={"DELETE"})
     *
     * @SWG\Delete(description="Delete group", tags={"Delete group"})
     * @SWG\Response(response=200, description="Killed",
     *     examples= {
     *      "application/json":{"data":{}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     * @SWG\Response(response=404, description="Not found")
     *
     * @param int $id
     * @param GroupService $groupService
     * @return ApiResponse
     */
    public function deleteGroup(int $id, GroupService $groupService): ApiResponse
    {
        $groupService->deleteGroup($id);
        return new ApiResponse([], Response::HTTP_OK);
    }

    /**
     * @Route ("/groups/{id}/users", name="api_group_users_list",  methods={"GET"})
     *
     * @SWG\Get(description="", tags={"Get group users list"})
     * @SWG\Response(response=200, description="Group users list"
     * )
     * @SWG\Response(response=403, description="Forbidden")
     * @SWG\Response(response=404, description="Not found")
     *
     */
    public function getGroupUsers(int $id, GroupRepository $repository): ApiResponse
    {
        $group = $repository->find($id);
        if (!$group) {
            throw new NotFoundException('Group not found!');
        }
        return new ApiResponse($group->getUsers(), Response::HTTP_OK);
    }
}