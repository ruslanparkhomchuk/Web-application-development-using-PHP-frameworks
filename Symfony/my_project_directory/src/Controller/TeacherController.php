<?php

namespace App\Controller;

use App\Entity\Teacher;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
    public function index(Request $request): JsonResponse
    {
        $filters = [];
        
        // Apply filters based on query parameters
        if ($request->query->has('id')) {
            $filters['id'] = $request->query->get('id');
        }
        
        if ($request->query->has('firstName')) {
            $filters['firstName'] = $request->query->get('firstName');
        }
        
        if ($request->query->has('lastName')) {
            $filters['lastName'] = $request->query->get('lastName');
        }
        
        if ($request->query->has('email')) {
            $filters['email'] = $request->query->get('email');
        }
        
        if ($request->query->has('phone')) {
            $filters['phone'] = $request->query->get('phone');
        }
        
        if ($request->query->has('department')) {
            $filters['department'] = $request->query->get('department');
        }
        
        if ($request->query->has('hireDate')) {
            $filters['hireDate'] = new \DateTime($request->query->get('hireDate'));
        }
        
        // Pagination parameters
        $page = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = max(1, $request->query->getInt('itemsPerPage', 10));
        
        // Get paginated and filtered results
        $result = $this->getPaginatedAndFilteredResults(Teacher::class, $filters, $page, $itemsPerPage);
        
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