<?php

namespace App\Controller;

use App\Entity\Student;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/students')]
class StudentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private StudentRepository $studentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        StudentRepository $studentRepository
    ) {
        $this->entityManager = $entityManager;
        $this->studentRepository = $studentRepository;
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $students = $this->studentRepository->findAll();
        
        return $this->json([
            'data' => $students,
        ]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            return $this->json(['message' => 'Student not found'], 404);
        }
        
        return $this->json([
            'data' => $student,
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $student = new Student();
        $student->setFirstName($data['firstName']);
        $student->setLastName($data['lastName']);
        $student->setEmail($data['email']);
        
        if (isset($data['birthDate'])) {
            $student->setBirthDate(new \DateTime($data['birthDate']));
        }
        
        $student->setEnrollmentDate(new \DateTime($data['enrollmentDate'] ?? 'now'));
        
        if (isset($data['address'])) {
            $student->setAddress($data['address']);
        }
        
        if (isset($data['phone'])) {
            $student->setPhone($data['phone']);
        }
        
        $this->entityManager->persist($student);
        $this->entityManager->flush();
        
        return $this->json([
            'data' => $student,
        ], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            return $this->json(['message' => 'Student not found'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['firstName'])) {
            $student->setFirstName($data['firstName']);
        }
        
        if (isset($data['lastName'])) {
            $student->setLastName($data['lastName']);
        }
        
        if (isset($data['email'])) {
            $student->setEmail($data['email']);
        }
        
        if (isset($data['birthDate'])) {
            $student->setBirthDate(new \DateTime($data['birthDate']));
        }
        
        if (isset($data['enrollmentDate'])) {
            $student->setEnrollmentDate(new \DateTime($data['enrollmentDate']));
        }
        
        if (isset($data['address'])) {
            $student->setAddress($data['address']);
        }
        
        if (isset($data['phone'])) {
            $student->setPhone($data['phone']);
        }
        
        $this->entityManager->flush();
        
        return $this->json([
            'data' => $student,
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            return $this->json(['message' => 'Student not found'], 404);
        }
        
        $this->entityManager->remove($student);
        $this->entityManager->flush();
        
        return $this->json(null, 204);
    }
}