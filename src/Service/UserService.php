<?php

namespace App\Service;

use App\Entity\Group;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $hasher;
    private GroupRepository $groupRepository;

    /**
     * UserService constructor.
     *
     * @param EntityManagerInterface $em
     * @param UserPasswordHasherInterface $hasher
     * @param UserRepository $userRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepository,
        GroupRepository $groupRepository
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->hasher = $hasher;
        $this->groupRepository = $groupRepository;
    }

    public function addUser(array $data): User
    {
        $user = new User();
        $user->setEmail($data['email']);
        $user->setName($data['name']);
        $user->setPassword($this->hasher->hashPassword($user, $data['password']));
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function editUser(int $id, array $data): User
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new NotFoundException('User not found!');
        }
        $user->setName($data['name']);
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function killUser(int $id): void
    {
        $user = $this->userRepository->find($id);
        if ($user) {
            $this->em->remove($user);
            $this->em->flush();
        }
    }

    public function addToGroup(int $userId, $groupId): void
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new NotFoundException('User not found!');
        }
        /** @var Group|null $group */
        $group = $this->groupRepository->find($groupId);
        if (!$group) {
            throw new NotFoundException('Group not found!');
        }
        $user->addGroup($group);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function removeFromGroup(int $userId, $groupId): void
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new NotFoundException('User not found!');
        }
        /** @var Group|null $group */
        $group = $this->groupRepository->find($groupId);
        if (!$group) {
            throw new NotFoundException('Group not found!');
        }
        $user->removeGroup($group);
        $this->em->persist($user);
        $this->em->flush();
    }
}