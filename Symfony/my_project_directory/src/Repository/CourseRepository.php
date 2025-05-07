<?php

namespace App\Repository;

use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * Get paginated and filtered courses
     */
    public function getFilteredAndPaginatedCourses(Request $request): array
    {
        $filters = [];
        
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
        
        $page = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = max(1, $request->query->getInt('itemsPerPage', 10));
        
        $result = $this->getPaginatedAndFilteredResults($filters, $page, $itemsPerPage);
        
        $totalItems = count($result['paginator']);
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        return [
            'data' => $result['data'],
            'pagination' => [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages
            ]
        ];
    }

    /**
     * Update a course with data from request
     */
    public function updateCourseFromData(Course $course, array $data): Course
    {
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
        
        $this->getEntityManager()->flush();
        
        return $course;
    }

    /**
     * Get paginated and filtered results
     */
    private function getPaginatedAndFilteredResults(array $filters = [], int $page = 1, int $itemsPerPage = 10): array
    {
        $queryBuilder = $this->createQueryBuilder('e');
        
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
        
        $queryBuilder->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);
        
        $paginator = new Paginator($queryBuilder);
        
        $results = [];
        foreach ($paginator as $entity) {
            $results[] = $entity;
        }
        
        return [
            'data' => $results,
            'paginator' => $paginator
        ];
    }
}