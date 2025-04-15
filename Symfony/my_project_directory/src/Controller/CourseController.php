<?php

namespace App\Controller;

use App\Entity\Course;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
    public function index(Request $request): JsonResponse
    {
        $filters = [];
        
        // Apply filters based on query parameters
        if ($request->query->has('id')) {
            $filters['id'] = $request->query->get('id');
        }
        
        if ($request->query->has('name')) {
            $filters['name'] = $request->query->get('name');
        }
        
        if ($request->query->has('code')) {
            $filters['code'] = $request->query->get('code');
        }
        
        if ($request->query->has('description')) {
            $filters['description'] = $request->query->get('description');
        }
        
        if ($request->query->has('credits')) {
            $filters['credits'] = $request->query->get('credits');
        }
        
        if ($request->query->has('startDate')) {
            $filters['startDate'] = new \DateTime($request->query->get('startDate'));
        }
        
        if ($request->query->has('endDate')) {
            $filters['endDate'] = new \DateTime($request->query->get('endDate'));
        }
        
        // Pagination parameters
        $page = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = max(1, $request->query->getInt('itemsPerPage', 10));
        
        // Get paginated and filtered results
        $result = $this->getPaginatedAndFilteredResults(Course::class, $filters, $page, $itemsPerPage);
        
        // Calculate total pages
        $totalItems = count($result);
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        return $this->json([
            'data' => $result,
            'pagination' => [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages
            ]
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

    /**
     * Get paginated and filtered results
     */
    private function getPaginatedAndFilteredResults(string $entityClass, array $filters = [], int $page = 1, int $itemsPerPage = 10): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('e')
            ->from($entityClass, 'e');
        
        // Apply filters dynamically
        $parameterIndex = 0;
        foreach ($filters as $field => $value) {
            $paramName = 'param_' . $parameterIndex++;
            
            // Handle special cases for date filters
            if ($value instanceof \DateTime) {
                $queryBuilder->andWhere("DATE(e.{$field}) = DATE(:{$paramName})");
            } else {
                $queryBuilder->andWhere("e.{$field} = :{$paramName}");
            }
            
            $queryBuilder->setParameter($paramName, $value);
        }
        
        // Apply pagination
        $queryBuilder->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);
        
        // Convert to paginator to get correct count
        $paginator = new Paginator($queryBuilder);
        
        // Convert to array
        $results = [];
        foreach ($paginator as $entity) {
            $results[] = $entity;
        }
        
        return $results;
    }
}