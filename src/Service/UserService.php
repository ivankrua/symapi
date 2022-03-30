<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $em;
    private JWTTokenManagerInterface $JWTTokenManager;
    private UserPasswordHasherInterface $hasher;

    /**
     * UserService constructor.
     *
     * @param EntityManagerInterface $em
     * @param UserPasswordHasherInterface $hasher
     * @param JWTTokenManagerInterface $JWTTokenManager
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $JWTTokenManager
    ) {
        $this->em = $em;
        $this->JWTTokenManager = $JWTTokenManager;
        $this->hasher = $hasher;
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
}