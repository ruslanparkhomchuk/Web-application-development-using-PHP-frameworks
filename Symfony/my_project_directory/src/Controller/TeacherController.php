<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/teachers')]
class TeacherController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TeacherRepository $teacherRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TeacherRepository $teacherRepository
    ) {
        $this->entityManager = $entityManager;
        $this->teacherRepository = $teacherRepository;
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $teachers = $this->teacherRepository->findAll();
        
        return $this->json([
            'data' => $teachers,
        ]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $teacher = $this->teacherRepository->find($id);
        
        if (!$teacher) {
            return $this->json(['message' => 'Teacher not found'], 404);
        }
        
        return $this->json([
            'data' => $teacher,
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $teacher = new Teacher();
        $teacher->setFirstName($data['firstName']);
        $teacher->setLastName($data['lastName']);
        $teacher->setEmail($data['email']);
        
        if (isset($data['phone'])) {
            $teacher->setPhone($data['phone']);
        }
        
        if (isset($data['department'])) {
            $teacher->setDepartment($data['department']);
        }
        
        if (isset($data['hireDate'])) {
            $teacher->setHireDate(new \DateTime($data['hireDate']));
        }
        
        $this->entityManager->persist($teacher);
        $this->entityManager->flush();
        
        return $this->json([
            'data' => $teacher,
        ], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $teacher = $this->teacherRepository->find($id);
        
        if (!$teacher) {
            return $this->json(['message' => 'Teacher not found'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['firstName'])) {
            $teacher->setFirstName($data['firstName']);
        }
        
        if (isset($data['lastName'])) {
            $teacher->setLastName($data['lastName']);
        }
        
        if (isset($data['email'])) {
            $teacher->setEmail($data['email']);
        }
        
        if (isset($data['phone'])) {
            $teacher->setPhone($data['phone']);
        }
        
        if (isset($data['department'])) {
            $teacher->setDepartment($data['department']);
        }
        
        if (isset($data['hireDate'])) {
            $teacher->setHireDate(new \DateTime($data['hireDate']));
        }
        
        $this->entityManager->flush();
        
        return $this->json([
            'data' => $teacher,
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $teacher = $this->teacherRepository->find($id);
        
        if (!$teacher) {
            return $this->json(['message' => 'Teacher not found'], 404);
        }
        
        $this->entityManager->remove($teacher);
        $this->entityManager->flush();
        
        return $this->json(null, 204);
    }
}