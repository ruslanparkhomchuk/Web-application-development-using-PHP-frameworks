<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/users', name: 'admin_get_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        
        return $this->json([
            'data' => $users,
        ]);
    }

    #[Route('/users/{id}', name: 'admin_get_user', methods: ['GET'])]
    public function getUser(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }
        
        return $this->json([
            'data' => $user,
        ]);
    }

    #[Route('/users', name: 'admin_create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password']) || empty($data['firstName']) || empty($data['lastName']) || empty($data['roles'])) {
            return $this->json(['message' => 'Missing required fields'], 400);
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['message' => 'User already exists'], 409);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setRoles($data['roles']);

        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    #[Route('/users/{id}', name: 'admin_update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['email'])) {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return $this->json(['message' => 'Email already in use'], 409);
            }
            $user->setEmail($data['email']);
        }
        
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }
        
        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        
        $this->entityManager->flush();
        
        return $this->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    #[Route('/users/{id}', name: 'admin_delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }
        
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        
        return $this->json(['message' => 'User deleted successfully'], 204);
    }
}