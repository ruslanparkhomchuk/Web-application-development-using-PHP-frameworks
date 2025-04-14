<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/courses')]
class CourseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CourseRepository $courseRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CourseRepository $courseRepository
    ) {
        $this->entityManager = $entityManager;
        $this->courseRepository = $courseRepository;
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $courses = $this->courseRepository->findAll();
        
        return $this->json([
            'data' => $courses,
        ]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $course = $this->courseRepository->find($id);
        
        if (!$course) {
            return $this->json(['message' => 'Course not found'], 404);
        }
        
        return $this->json([
            'data' => $course,
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $course = new Course();
        $course->setName($data['name']);
        $course->setCode($data['code']);
        $course->setCredits($data['credits']);
        
        if (isset($data['description'])) {
            $course->setDescription($data['description']);
        }
        
        if (isset($data['startDate'])) {
            $course->setStartDate(new \DateTime($data['startDate']));
        }
        
        if (isset($data['endDate'])) {
            $course->setEndDate(new \DateTime($data['endDate']));
        }
        
        $this->entityManager->persist($course);
        $this->entityManager->flush();
        
        return $this->json([
            'data' => $course,
        ], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $course = $this->courseRepository->find($id);
        
        if (!$course) {
            return $this->json(['message' => 'Course not found'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['name'])) {
            $course->setName($data['name']);
        }
        
        if (isset($data['code'])) {
            $course->setCode($data['code']);
        }
        
        if (isset($data['description'])) {
            $course->setDescription($data['description']);
        }
        
        if (isset($data['credits'])) {
            $course->setCredits($data['credits']);
        }
        
        if (isset($data['startDate'])) {
            $course->setStartDate(new \DateTime($data['startDate']));
        }
        
        if (isset($data['endDate'])) {
            $course->setEndDate(new \DateTime($data['endDate']));
        }
        
        $this->entityManager->flush();
        
        return $this->json([
            'data' => $course,
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $course = $this->courseRepository->find($id);
        
        if (!$course) {
            return $this->json(['message' => 'Course not found'], 404);
        }
        
        $this->entityManager->remove($course);
        $this->entityManager->flush();
        
        return $this->json(null, 204);
    }
}