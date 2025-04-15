<?php

namespace App\Controller;

use App\Entity\Enrollment;
use App\Repository\CourseRepository;
use App\Repository\EnrollmentRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/enrollments')]
class EnrollmentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private EnrollmentRepository $enrollmentRepository;
    private StudentRepository $studentRepository;
    private CourseRepository $courseRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EnrollmentRepository $enrollmentRepository,
        StudentRepository $studentRepository,
        CourseRepository $courseRepository
    ) {
        $this->entityManager = $entityManager;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->studentRepository = $studentRepository;
        $this->courseRepository = $courseRepository;
    }

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('e')
            ->from(Enrollment::class, 'e')
            ->join('e.student', 's')
            ->join('e.course', 'c');
        
        // Apply filters based on query parameters
        if ($request->query->has('id')) {
            $queryBuilder->andWhere('e.id = :id')
                ->setParameter('id', $request->query->get('id'));
        }
        
        if ($request->query->has('studentId')) {
            $queryBuilder->andWhere('s.id = :studentId')
                ->setParameter('studentId', $request->query->get('studentId'));
        }
        
        if ($request->query->has('courseId')) {
            $queryBuilder->andWhere('c.id = :courseId')
                ->setParameter('courseId', $request->query->get('courseId'));
        }
        
        if ($request->query->has('enrollmentDate')) {
            $queryBuilder->andWhere('DATE(e.enrollmentDate) = DATE(:enrollmentDate)')
                ->setParameter('enrollmentDate', new \DateTime($request->query->get('enrollmentDate')));
        }
        
        if ($request->query->has('grade')) {
            $queryBuilder->andWhere('e.grade = :grade')
                ->setParameter('grade', $request->query->get('grade'));
        }
        
        if ($request->query->has('status')) {
            $queryBuilder->andWhere('e.status = :status')
                ->setParameter('status', $request->query->get('status'));
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
        foreach ($paginator as $enrollment) {
            $data[] = [
                'id' => $enrollment->getId(),
                'student' => [
                    'id' => $enrollment->getStudent()->getId(),
                    'firstName' => $enrollment->getStudent()->getFirstName(),
                    'lastName' => $enrollment->getStudent()->getLastName()
                ],
                'course' => [
                    'id' => $enrollment->getCourse()->getId(),
                    'name' => $enrollment->getCourse()->getName(),
                    'code' => $enrollment->getCourse()->getCode()
                ],
                'enrollmentDate' => $enrollment->getEnrollmentDate()->format('Y-m-d'),
                'grade' => $enrollment->getGrade(),
                'status' => $enrollment->getStatus()
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
        $enrollment = $this->enrollmentRepository->find($id);
        
        if (!$enrollment) {
            return $this->json(['message' => 'Enrollment not found'], 404);
        }
        
        $data = [
            'id' => $enrollment->getId(),
            'student' => [
                'id' => $enrollment->getStudent()->getId(),
                'firstName' => $enrollment->getStudent()->getFirstName(),
                'lastName' => $enrollment->getStudent()->getLastName()
            ],
            'course' => [
                'id' => $enrollment->getCourse()->getId(),
                'name' => $enrollment->getCourse()->getName(),
                'code' => $enrollment->getCourse()->getCode()
            ],
            'enrollmentDate' => $enrollment->getEnrollmentDate()->format('Y-m-d'),
            'grade' => $enrollment->getGrade(),
            'status' => $enrollment->getStatus()
        ];
        
        return $this->json([
            'data' => $data,
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $student = $this->studentRepository->find($data['studentId']);
        if (!$student) {
            return $this->json(['message' => 'Student not found'], 404);
        }
        
        $course = $this->courseRepository->find($data['courseId']);
        if (!$course) {
            return $this->json(['message' => 'Course not found'], 404);
        }
        
        $enrollment = new Enrollment();
        $enrollment->setStudent($student);
        $enrollment->setCourse($course);
        $enrollment->setEnrollmentDate(new \DateTime($data['enrollmentDate'] ?? 'now'));
        
        if (isset($data['grade'])) {
            $enrollment->setGrade($data['grade']);
        }
        
        if (isset($data['status'])) {
            $enrollment->setStatus($data['status']);
        }
        
        $this->entityManager->persist($enrollment);
        $this->entityManager->flush();
        
        $responseData = [
            'id' => $enrollment->getId(),
            'student' => [
                'id' => $enrollment->getStudent()->getId(),
                'firstName' => $enrollment->getStudent()->getFirstName(),
                'lastName' => $enrollment->getStudent()->getLastName()
            ],
            'course' => [
                'id' => $enrollment->getCourse()->getId(),
                'name' => $enrollment->getCourse()->getName(),
                'code' => $enrollment->getCourse()->getCode()
            ],
            'enrollmentDate' => $enrollment->getEnrollmentDate()->format('Y-m-d'),
            'grade' => $enrollment->getGrade(),
            'status' => $enrollment->getStatus()
        ];
        
        return $this->json([
            'data' => $responseData,
        ], 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $enrollment = $this->enrollmentRepository->find($id);
        
        if (!$enrollment) {
            return $this->json(['message' => 'Enrollment not found'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['studentId'])) {
            $student = $this->studentRepository->find($data['studentId']);
            if (!$student) {
                return $this->json(['message' => 'Student not found'], 404);
            }
            $enrollment->setStudent($student);
        }
        
        if (isset($data['courseId'])) {
            $course = $this->courseRepository->find($data['courseId']);
            if (!$course) {
                return $this->json(['message' => 'Course not found'], 404);
            }
            $enrollment->setCourse($course);
        }
        
        if (isset($data['enrollmentDate'])) {
            $enrollment->setEnrollmentDate(new \DateTime($data['enrollmentDate']));
        }
        
        if (isset($data['grade'])) {
            $enrollment->setGrade($data['grade']);
        }
        
        if (isset($data['status'])) {
            $enrollment->setStatus($data['status']);
        }
        
        $this->entityManager->flush();
        
        $responseData = [
            'id' => $enrollment->getId(),
            'student' => [
                'id' => $enrollment->getStudent()->getId(),
                'firstName' => $enrollment->getStudent()->getFirstName(),
                'lastName' => $enrollment->getStudent()->getLastName()
            ],
            'course' => [
                'id' => $enrollment->getCourse()->getId(),
                'name' => $enrollment->getCourse()->getName(),
                'code' => $enrollment->getCourse()->getCode()
            ],
            'enrollmentDate' => $enrollment->getEnrollmentDate()->format('Y-m-d'),
            'grade' => $enrollment->getGrade(),
            'status' => $enrollment->getStatus()
        ];
        
        return $this->json([
            'data' => $responseData,
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $enrollment = $this->enrollmentRepository->find($id);
        
        if (!$enrollment) {
            return $this->json(['message' => 'Enrollment not found'], 404);
        }
        
        $this->entityManager->remove($enrollment);
        $this->entityManager->flush();
        
        return $this->json(null, 204);
    }
}