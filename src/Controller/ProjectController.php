<?php
 
namespace App\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

use App\Entity\Project;

use App\Exception\ProjectNotFoundException;
 
 
#[Route('/api', name: 'api_')]
class ProjectController extends AbstractController
{
    #[Route('/projects', name: 'projects_index', methods:['get'] )]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        
        $products = $entityManager
            ->getRepository(Project::class)
            ->findAll();
    
        $data = [];
    
        foreach ($products as $product) {
           $data[] = [
               'id' => $product->getId(),
               'name' => $product->getName(),
               'description' => $product->getDescription(),
           ];
        }
    
        return $this->json($data);
    }
  
    #[Route('/projects', name: 'projects_create', methods:['post'] )]
    public function create(EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator): JsonResponse
{
    $name = $request->request->get('name');
    $description = $request->request->get('description');

    // Validate input
    $errors = $validator->validate([
        'name' => $name,
        'description' => $description,
    ], new Assert\Collection([
        'name' => new Assert\NotBlank(),
        'description' => new Assert\NotBlank(),
    ]));

    if (count($errors) > 0) {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        return $this->json(['errors' => $errorMessages], 400);
    }

    // Proceed with entity creation and persistence
    $project = new Project();
    $project->setName($name);
    $project->setDescription($description);

    $entityManager->persist($project);
    $entityManager->flush();

    $data = [
        'id' => $project->getId(),
        'name' => $project->getName(),
        'description' => $project->getDescription(),
    ];

    return $this->json($data);
}
  
  
    #[Route('/projects/{id}', name: 'project_show', methods:['get'] )]
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $project = $entityManager->getRepository(Project::class)->find($id);
        
        if (!$project) {
            throw new ProjectNotFoundException('Project not found');
        }
    
        $data =  [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
        ];
            
        return $this->json($data);
    }
  
    #[Route('/projects/{id}', name: 'projects_update', methods:['put', 'patch'] )]
    public function update(EntityManagerInterface $entityManager, Request $request, int $id): JsonResponse
    {
        $project = $entityManager->getRepository(Project::class)->find($id);
    
        if (!$project) {
            return $this->json('No project found for id ' . $id, 404);
        }
    
        $project->setName($request->request->get('name'));
        $project->setDescription($request->request->get('description'));
        $entityManager->flush();
    
        $data =  [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
        ];
            
        return $this->json($data);
    }
  
    #[Route('/projects/{id}', name: 'project_delete', methods:['delete'] )]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $project = $entityManager->getRepository(Project::class)->find($id);
    
        if (!$project) {
            return $this->json('No project found for id ' . $id, 404);
        }
    
        $entityManager->remove($project);
        $entityManager->flush();
    
        return $this->json('Deleted a project successfully with id ' . $id);
    }
}