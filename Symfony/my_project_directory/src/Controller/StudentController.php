<?php

namespace App\Controller;

use App\Entity\Student;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
        
        if ($request->query->has('birthDate')) {
            $filters['birthDate'] = new \DateTime($request->query->get('birthDate'));
        }
        
        if ($request->query->has('enrollmentDate')) {
            $filters['enrollmentDate'] = new \DateTime($request->query->get('enrollmentDate'));
        }
        
        if ($request->query->has('address')) {
            $filters['address'] = $request->query->get('address');
        }
        
        if ($request->query->has('phone')) {
            $filters['phone'] = $request->query->get('phone');
        }
        
        // Pagination parameters
        $page = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = max(1, $request->query->getInt('itemsPerPage', 10));
        
        // Get paginated and filtered results
        $result = $this->getPaginatedAndFilteredResults(Student::class, $filters, $page, $itemsPerPage);
        
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