<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class NoteController extends AbstractController
{
    public function index(NoteRepository $noteRepository): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_TEACHER', $user->getRoles())) {

            return $this->render('notes/index.html.twig', [
                'notes' => $noteRepository->findAll(),
            ]);
        }

        if (in_array('ROLE_STUDENT', $user->getRoles())) {

            $repository = $this->getDoctrine()->getRepository(Note::class);
            $notes = $repository->findNotesByUser($user);

            return $this->render('pages/student_note.twig', [
                'notes' => $notes,
            ]);
        }

        return $this->redirectToRoute('index');
    }

    public function new(Request $request): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_TEACHER', $user->getRoles())) {
            $note = new Note();
            $form = $this->createForm(NoteType::class, $note);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($note);
                $entityManager->flush();

                return $this->redirectToRoute('notes_index');
            }

            return $this->render('notes/new.html.twig', [
                'note' => $note,
                'form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('index');
    }

    public function show(Note $note): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_TEACHER', $user->getRoles())) {
            return $this->render('notes/show.html.twig', [
                'note' => $note,
            ]);
        }

        return $this->redirectToRoute('index');
    }

    public function edit(Request $request, Note $note): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_TEACHER', $user->getRoles())) {
            $form = $this->createForm(NoteType::class, $note);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('notes_index');
            }

            return $this->render('notes/edit.html.twig', [
                'note' => $note,
                'form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('index');
    }

    public function delete(Request $request, Note $note): Response
    {
        $user = $this->getUser();

        if ($user && in_array('ROLE_TEACHER', $user->getRoles())) {
            if ($this->isCsrfTokenValid('delete'.$note->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($note);
                $entityManager->flush();
            }

            return $this->redirectToRoute('notes_index');
        }

        return $this->redirectToRoute('index');
    }
}
