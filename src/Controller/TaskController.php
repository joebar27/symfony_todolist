<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Task;
use App\Entity\Categories;

class TaskController extends AbstractController
{
    #[Route('/task', name: 'task')]
    public function index(): Response
    {
        return $this->render('task/index.html.twig', [
            'controller_name' => 'TaskController',
        ]);
    }

    #[Route('/task/create', name: 'task_create')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        // mise en place du gestionnaire de BDD :
        $entityManager = $doctrine->getManager();
        // creates l'objet Task et initialise les datas
        $task = new Task();
        $task->setNameTask('Write a blog post');
        $task->setDueDateTask(new \DateTime('now'));

        $form = $this->createFormBuilder($task)
            ->add('nameTask', TextType::class, ['label' => 'Nom de la tâche :', 'attr' => ['class' => 'form-control mb-4']])
            ->add('descriptionTask', TextareaType::class, ['label' => 'Description de la tâche :','attr' => ['class' => 'form-control mb-4']])
            ->add('dueDateTask', DateType::class, ["widget"=>"single_text",'label' => 'Date création de la tâche :','attr' => ['class' => 'form-control mb-4']])
            ->add('priorityTask', ChoiceType::class, ['label' => 'Priorité de la tâche :','choices' => ['Haute' => 'Haute','Normal' => 'Normal','Basse' => 'Basse',], 'attr' => ['class' => 'form-select mb-4'],])
            ->add('category', EntityType::class, ['label' => 'Catégorie de la tâche :', 'class' => Categories::class,'choice_label' => 'libelleCategory', 'attr' => ['class' => 'form-select mb-4'],])
            ->add('save', SubmitType::class, ['label' => 'Créé la tâche','attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            $task->setCreatedDateTask(new \DateTime('today'));

            // ... perform some action, such as saving the task to the database
            $entityManager->persist($task);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
            $this->addFlash('success', 'Tâche ajoutée avec succes');

            return $this->redirectToRoute('task_listing');
        }

        return $this->renderForm('task/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/task/listing', name: 'task_listing')]
    public function listing(ManagerRegistry $doctrine): Response
    {
        $tasks = $doctrine->getRepository(Task::class)->findAll();

        return $this->render('task/listing.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/editing/{id}', name: 'task_editing')]
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();

        $task = $entityManager->getRepository(task::class)->find($id);

        $form = $this->createFormBuilder($task)
            ->add('nameTask', TextType::class, ['label' => 'Nom de la tâche :', 'attr' => ['class' => 'form-control mb-4']])
            ->add('descriptionTask', TextareaType::class, ['label' => 'Description de la tâche :','attr' => ['class' => 'form-control mb-4']])
            ->add('dueDateTask', DateType::class, ["widget"=>"single_text",'label' => 'Date création de la tâche :','attr' => ['class' => 'form-control mb-4']])
            ->add('priorityTask', ChoiceType::class, ['label' => 'Priorité de la tâche :','choices' => ['Haute' => 'Haute','Normal' => 'Normal','Basse' => 'Basse',], 'attr' => ['class' => 'form-select mb-4'],])
            ->add('category', EntityType::class, ['label' => 'Catégorie de la tâche :', 'class' => Categories::class,'choice_label' => 'libelleCategory', 'attr' => ['class' => 'form-select mb-4'],])
            ->add('save', SubmitType::class, ['label' => 'Modifier la tâche','attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            $task->setCreatedDateTask(new \DateTime('today'));

            // ... perform some action, such as saving the task to the database
            $entityManager->persist($task);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();
            $this->addFlash('success', 'Tâche Modifiée avec succes');

            return $this->redirectToRoute('task_listing');
        }

        return $this->renderForm('task/editing.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/task/delete/{id}', name: 'task_delete')]
    public function remove(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();

        $task = $entityManager->getRepository(task::class)->find($id);
        
        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('danger', 'Tâche Supprimée avec succes');

        return $this->redirectToRoute('task_listing');
    }
}
