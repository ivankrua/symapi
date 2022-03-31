<?php

namespace App\Service;

use App\Entity\Group;
use App\Exception\NotFoundException;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;

class GroupService
{

    private EntityManagerInterface $em;
    private GroupRepository $groupRepository;

    public function __construct(
        EntityManagerInterface $em,
        GroupRepository $groupRepository
    ) {
        $this->em = $em;
        $this->groupRepository = $groupRepository;
    }

    public function addGroup(array $data): Group
    {
        $group = new Group();
        $group->setName($data['name']);
        $this->em->persist($group);
        $this->em->flush();
        return $group;
    }

    public function editGroup(int $id, array $data): Group
    {
        $group = $this->groupRepository->find($id);
        if (!$group) {
            throw new NotFoundException('Group not found!');
        }
        $group->setName($data['name']);
        $this->em->persist($group);
        $this->em->flush();
        return $group;
    }

    public function deleteGroup(int $id): void
    {
        $group = $this->groupRepository->find($id);
        if (!$group) {
            throw new NotFoundException('Group not found!');
        }
        $this->em->remove($group);
        $this->em->flush();
    }
}