<?php

namespace App\Controller;

use App\Entity\Assignment;
use App\Repository\AssignmentRepository;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/assignments')]
class AssignmentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AssignmentRepository $assignmentRepository;
    private CourseRepository $courseRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        AssignmentRepository $assignmentRepository,
        CourseRepository $courseRepository
    ) {
        $this->entityManager = $entityManager;
        $this->assignmentRepository = $assignmentRepository;
        $this->courseRepository = $courseRepository;
    }

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('a')
            ->from(Assignment::class, 'a')
            ->join('a.course', 'c');
        
        // Apply filters based on query parameters
        if ($request->query->has('id')) {
            $queryBuilder->andWhere('a.id = :id')
                ->setParameter('id', $request->query->get('id'));
        }
        
        if ($request->query->has('courseId')) {
            $queryBuilder->andWhere('c.id = :courseId')
                ->setParameter('courseId', $request->query->get('courseId'));
        }
        
        if ($request->query->has('title')) {
            $queryBuilder->andWhere('a.title LIKE :title')
                ->setParameter('title', '%' . $request->query->get('title') . '%');
        }
        
        if ($request->query->has('description')) {
            $queryBuilder->andWhere('a.description LIKE :description')
                ->setParameter('description', '%' . $request->query->get('description') . '%');
        }
        
        if ($request->query->has('dueDate')) {
            $queryBuilder->andWhere('DATE(a.dueDate) = DATE(:dueDate)')
                ->setParameter('dueDate', new \DateTime($request->query->get('dueDate')));
        }
        
        if ($request->query->has('maxScore')) {
            $queryBuilder->andWhere('a.maxScore = :maxScore')
                ->setParameter('maxScore', $request->query->get('maxScore'));
        }
        
        // Pagination parameters
        $page = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = max(1, $request->query->getInt('itemsPerPage', 10));
        
        // Apply pagination
        $queryBuilder->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);
        
        // Execute query with pagination
        $paginator = new Paginator($queryBuilder);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        // Format the data
        $data = [];
        foreach ($paginator as $assignment) {
            $data[] = [
                'id' => $assignment->getId(),
                'course' => [
                    'id' => $assignment->getCourse()->getId(),
                    'name' => $assignment->getCourse()->getName(),
                    'code' => $assignment->getCourse()->getCode()
                ],
                'title' => $assignment->getTitle(),
                'description' => $assignment->getDescription(),
                'dueDate' => $assignment->getDueDate() ? $assignment->getDueDate()->format('Y-m-d') : null,
                'maxScore' => $assignment->getMaxScore()
            ];
        }
        
        return $this->json([
            'data' => $data,
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
        $assignment = $this->assignmentRepository->find($id);
        
        if (!$assignment) {
            return $this->json(['message' => 'Assignment not found'], 404);
        }
        
        $data = [
            'id' => $assignment->getId(),
            'course' => [
                'id' => $assignment->getCourse()->getId(),
                'name' => $assignment->getCourse()->getName(),
                'code' => $assignment->getCourse()->getCode()
            ],
            'title' => $assignment->getTitle(),
            'description' => $assignment->getDescription(),
            'dueDate' => $assignment->getDueDate() ? $assignment->getDueDate()->format('Y-m-d') : null,
            'maxScore' => $assignment->getMaxScore()
        ];
        
        return $this->json([
            'data' => $data,
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $course = $this->courseRepository->find($data['courseId']);
        if (!$course) {
            return $this->json(['message' => 'Course not found'], 404);
        }
        
        $assignment = new Assignment();
        $assignment->setCourse($course);
        $assignment->setTitle($data['title']);
        
        if (isset($data['description'])) {
            $assignment->setDescription($data['description']);
        }
        
        if (isset($data['dueDate'])) {
            $assignment->setDueDate(new \DateTime($data['dueDate']));
        }
        
        if (isset($data['maxScore'])) {
            $assignment->setMaxScore($data['maxScore']);
        }
        
        $this->entityManager->persist($assignment);
        $this->entityManager->flush();
        
        $responseData = [
            'id' => $assignment->getId(),
            'course' => [
                'id' => $assignment->getCourse()->getId(),
                'name' => $assignment->getCourse()->getName(),
                'code' => $assignment->getCourse()->getCode()
            ],
            'title' => $assignment->getTitle(),
            'description' => $assignment->getDescription(),
            'dueDate' => $assignment->getDueDate() ? $assignment->getDueDate()->format('Y-m-d') : null,
            'maxScore' => $assignment->getMaxScore()
        ];
        
        return $this->json([
            'data' => $responseData,
        ], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $assignment = $this->assignmentRepository->find($id);
        
        if (!$assignment) {
            return $this->json(['message' => 'Assignment not found'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['courseId'])) {
            $course = $this->courseRepository->find($data['courseId']);
            if (!$course) {
                return $this->json(['message' => 'Course not found'], 404);
            }
            $assignment->setCourse($course);
        }
        
        if (isset($data['title'])) {
            $assignment->setTitle($data['title']);
        }
        
        if (isset($data['description'])) {
            $assignment->setDescription($data['description']);
        }
        
        if (isset($data['dueDate'])) {
            $assignment->setDueDate(new \DateTime($data['dueDate']));
        } elseif (array_key_exists('dueDate', $data) && $data['dueDate'] === null) {
            $assignment->setDueDate(null);
        }
        
        if (isset($data['maxScore'])) {
            $assignment->setMaxScore($data['maxScore']);
        }
        
        $this->entityManager->flush();
        
        $responseData = [
            'id' => $assignment->getId(),
            'course' => [
                'id' => $assignment->getCourse()->getId(),
                'name' => $assignment->getCourse()->getName(),
                'code' => $assignment->getCourse()->getCode()
            ],
            'title' => $assignment->getTitle(),
            'description' => $assignment->getDescription(),
            'dueDate' => $assignment->getDueDate() ? $assignment->getDueDate()->format('Y-m-d') : null,
            'maxScore' => $assignment->getMaxScore()
        ];
        
        return $this->json([
            'data' => $responseData,
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $assignment = $this->assignmentRepository->find($id);
        
        if (!$assignment) {
            return $this->json(['message' => 'Assignment not found'], 404);
        }
        
        $this->entityManager->remove($assignment);
        $this->entityManager->flush();
        
        return $this->json(null, 204);
    }
}